<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\Blok;
use App\Models\PemberianPakan;
use App\Models\SharingRevenue;
use App\Models\Siklus;
use App\Models\Tambak;
use App\Models\TransaksiKeuangan;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\FileUploadService;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SharingRevenueController extends Controller
{
    private function toBaseUnit(float $qty, ?string $unit): float
    {
        return match (strtolower(trim($unit ?? 'kg'))) {
            'gram', 'ml' => $qty / 1000,
            default => $qty,
        };
    }

    private function calculateProfit(Siklus $siklus): float
    {
        $siklus->loadMissing('panens');

        $transaksis = TransaksiKeuangan::where('siklus_id', $siklus->id)->get();

        $uangMasukTransaksi = $transaksis->where('jenis_transaksi', 'uang_masuk')->sum('nominal');
        $uangKeluarTransaksi = $transaksis->where('jenis_transaksi', 'uang_keluar')->sum('nominal');
        $totalPanen = $siklus->panens->sum('total_penjualan');

        $semuaPemberian = PemberianPakan::with('itemPersediaan.kategoriPersediaan', 'itemPersediaan.persediaan')
            ->where('siklus_id', $siklus->id)
            ->get();

        $biayaPakanKimia = $semuaPemberian->sum(function ($p) {
            $persediaan = $p->itemPersediaan?->persediaan;
            $jumlahBase = $this->toBaseUnit((float) ($p->jumlah_pakan ?? 0), $p->unit ?? 'kg');
            return $jumlahBase * ($persediaan?->harga_per_unit ?? 0);
        });

        return (float) ($uangMasukTransaksi + $totalPanen - $uangKeluarTransaksi - $biayaPakanKimia);
    }

    private function buildSiklusCards($tambakIds)
    {
        return Siklus::with(['blok.tambak', 'panens', 'kolams'])
            ->whereHas('blok', fn ($q) => $q->whereIn('tambak_id', $tambakIds))
            ->orderByRaw("CASE WHEN status = 'aktif' THEN 0 WHEN status = 'selesai' THEN 1 ELSE 2 END")
            ->orderByDesc('tgl_siklus')
            ->limit(8)
            ->get()
            ->map(function ($siklus) {
                $transaksis = TransaksiKeuangan::where('siklus_id', $siklus->id)->get();

                $uangMasukTransaksi = $transaksis->where('jenis_transaksi', 'uang_masuk')->sum('nominal');
                $uangKeluarTransaksi = $transaksis->where('jenis_transaksi', 'uang_keluar')->sum('nominal');
                $uangMasuk = $uangMasukTransaksi + $siklus->panens->sum('total_penjualan');
                $uangKeluar = $uangKeluarTransaksi + $this->getBiayaPakanKimia($siklus);
                $keuntunganKerugian = $uangMasuk - $uangKeluar;
                $sharingTerpakai = $this->usedPercentageForSiklus($siklus->id);

                return [
                    'id' => $siklus->id,
                    'blok_id' => $siklus->blok_id,
                    'nama_siklus' => $siklus->nama_siklus,
                    'blok_nama' => $siklus->blok?->nama_blok ?? '-',
                    'total_kolam' => $siklus->kolams->count(),
                    'tgl_siklus' => $siklus->tgl_siklus,
                    'status' => $siklus->status,
                    'uang_masuk' => $uangMasuk,
                    'uang_keluar' => $uangKeluar,
                    'keuntungan_kerugian' => $keuntunganKerugian,
                    'sharing_terpakai' => min(100, $sharingTerpakai),
                    'sharing_sisa' => max(0, 100 - $sharingTerpakai),
                ];
            });
    }

    private function getBiayaPakanKimia(Siklus $siklus): float
    {
        return (float) PemberianPakan::with('itemPersediaan.persediaan')
            ->where('siklus_id', $siklus->id)
            ->get()
            ->sum(function ($p) {
                $persediaan = $p->itemPersediaan?->persediaan;
                $jumlahBase = $this->toBaseUnit((float) ($p->jumlah_pakan ?? 0), $p->unit ?? 'kg');
                return $jumlahBase * ($persediaan?->harga_per_unit ?? 0);
            });
    }

    private function usedPercentageForSiklus(string $siklusId, ?string $excludeId = null): float
    {
        return (float) SharingRevenue::where('siklus_id', $siklusId)
            ->where('status', '!=', 'cancel')
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->sum('persentase');
    }

    private function getRemainingPercentage(string $siklusId, ?string $excludeId = null): float
    {
        return max(0, 100 - $this->usedPercentageForSiklus($siklusId, $excludeId));
    }

    private function validateSiklusQuota(Request $request, ?SharingRevenue $sharingRevenue = null): Siklus
    {
        $siklus = Siklus::where('id', $request->siklus_id)
            ->where('blok_id', $request->blok_id)
            ->where('status', 'selesai')
            ->firstOrFail();

        $remaining = $this->getRemainingPercentage($siklus->id, $sharingRevenue?->id);
        if ((float) $request->persentase > $remaining) {
            throw ValidationException::withMessages([
                'persentase' => 'Persentase melebihi sisa sharing revenue. Sisa tersedia: ' . number_format($remaining, 2, ',', '.') . '%.',
            ]);
        }

        return $siklus;
    }

    private function getAccessibleBloks(?SharingRevenue $sharingRevenue = null)
    {
        $user = auth()->user();
        $hasTambak = $user->tambaks()->exists();
        $tambakIds = $hasTambak ? $user->tambaks()->pluck('tambaks.id') : Tambak::pluck('id');
        $currentSiklusId = $sharingRevenue?->siklus_id;
        $currentSharingId = $sharingRevenue?->id;

        $bloks = Blok::with(['tambak', 'sikluses' => function ($q) use ($currentSiklusId) {
                $q->where(function ($q2) use ($currentSiklusId) {
                      $q2->where('status', 'selesai')
                         ->when($currentSiklusId, fn ($q3) => $q3->orWhere('id', $currentSiklusId));
                  })
                  ->orderBy('nama_siklus');
            }])
            ->whereIn('tambak_id', $tambakIds)
            ->orderBy('nama_blok')
            ->get();

        $bloks->each(function ($blok) use ($currentSiklusId, $currentSharingId) {
            $blok->setRelation('sikluses', $blok->sikluses->filter(function ($siklus) use ($currentSiklusId, $currentSharingId) {
                return $siklus->id === $currentSiklusId || $this->getRemainingPercentage($siklus->id, $currentSharingId) > 0;
            })->values());
        });

        return $bloks;
    }

    public function index()
    {
        $hasTambak = auth()->user()->tambaks()->exists();
        $data = $hasTambak
            ? SharingRevenue::with(['blok', 'siklus', 'accountBank'])->latest('created_at')->get()
            : collect();
        $tambakIds = auth()->user()->tambaks()->pluck('tambaks.id');
        $siklusCards = $hasTambak ? $this->buildSiklusCards($tambakIds) : collect();

        return view('keuangan.sharing-revenue.index', compact('data', 'siklusCards'));
    }

    public function create()
    {
        $bloks = $this->getAccessibleBloks();
        $siklusProfits = $bloks->flatMap(fn ($blok) => $blok->sikluses)->mapWithKeys(function ($siklus) {
            return [$siklus->id => $this->calculateProfit($siklus)];
        });
        $siklusRemaining = $bloks->flatMap(fn ($blok) => $blok->sikluses)->mapWithKeys(function ($siklus) {
            return [$siklus->id => $this->getRemainingPercentage($siklus->id)];
        });
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();

        return view('keuangan.sharing-revenue.form', compact('bloks', 'siklusProfits', 'siklusRemaining', 'accountBanks') + ['sharingRevenue' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_penerima' => 'required|string|max:255',
            'blok_id' => 'required|uuid|exists:bloks,id',
            'siklus_id' => 'required|uuid|exists:sikluses,id',
            'persentase' => 'required|numeric|min:0.01|max:100',
            'account_bank_id' => 'required|uuid|exists:account_banks,id',
            'eviden.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'catatan' => 'nullable|string',
        ]);

        $siklus = $this->validateSiklusQuota($request);
        $totalKeuntungan = $this->calculateProfit($siklus);
        $nominal = max(0, $totalKeuntungan) * ((float) $request->persentase / 100);

        $input = $request->only(['nama_penerima', 'blok_id', 'siklus_id', 'persentase', 'account_bank_id', 'catatan']);
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVS');
        $input['total_keuntungan'] = $totalKeuntungan;
        $input['nominal'] = $nominal;
        $input['jenis_pembayaran'] = 'bank';
        $input['created_by'] = auth()->id();

        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        $sharingRevenue = SharingRevenue::create($input);

        app(NotifikasiService::class)->kirimApprovalRequest(
            'Sharing Revenue Menunggu Approval',
            'Sharing revenue untuk "' . $input['nama_penerima'] . '" sebesar Rp ' . number_format($nominal, 0, ',', '.') . ' memerlukan persetujuan.',
            route('sharing-revenue.show', $sharingRevenue->id)
        );

        return redirect()->route('sharing-revenue.index')->with('success', 'Sharing revenue berhasil ditambahkan.');
    }

    public function show(SharingRevenue $sharingRevenue)
    {
        $sharingRevenue->load(['blok', 'siklus', 'accountBank', 'pembuat']);
        return view('keuangan.sharing-revenue.show', compact('sharingRevenue'));
    }

    public function edit(SharingRevenue $sharingRevenue)
    {
        $bloks = $this->getAccessibleBloks($sharingRevenue);
        $siklusProfits = $bloks->flatMap(fn ($blok) => $blok->sikluses)->mapWithKeys(function ($siklus) {
            return [$siklus->id => $this->calculateProfit($siklus)];
        });
        $siklusRemaining = $bloks->flatMap(fn ($blok) => $blok->sikluses)->mapWithKeys(function ($siklus) use ($sharingRevenue) {
            return [$siklus->id => $this->getRemainingPercentage($siklus->id, $sharingRevenue->id)];
        });
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();

        return view('keuangan.sharing-revenue.form', compact('sharingRevenue', 'bloks', 'siklusProfits', 'siklusRemaining', 'accountBanks'));
    }

    public function update(Request $request, SharingRevenue $sharingRevenue)
    {
        $request->validate([
            'nama_penerima' => 'required|string|max:255',
            'blok_id' => 'required|uuid|exists:bloks,id',
            'siklus_id' => 'required|uuid|exists:sikluses,id',
            'persentase' => 'required|numeric|min:0.01|max:100',
            'account_bank_id' => 'required|uuid|exists:account_banks,id',
            'eviden.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'catatan' => 'nullable|string',
        ]);

        $siklus = $this->validateSiklusQuota($request, $sharingRevenue);
        $totalKeuntungan = $this->calculateProfit($siklus);
        $nominal = max(0, $totalKeuntungan) * ((float) $request->persentase / 100);

        $input = $request->only(['nama_penerima', 'blok_id', 'siklus_id', 'persentase', 'account_bank_id', 'catatan']);
        $input['total_keuntungan'] = $totalKeuntungan;
        $input['nominal'] = $nominal;
        $input['jenis_pembayaran'] = 'bank';

        if ($request->hasFile('eviden')) {
            $existing = $sharingRevenue->eviden ?? [];
            foreach ($request->file('eviden') as $file) {
                $existing[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $existing;
        }

        if ($request->filled('hapus_eviden')) {
            $existing = $sharingRevenue->eviden ?? [];
            $input['eviden'] = array_values(array_filter($existing, fn ($p) => !in_array($p, $request->input('hapus_eviden', []))));
        }

        DB::transaction(function () use ($sharingRevenue, $input) {
            if ($sharingRevenue->status === 'selesai' && $sharingRevenue->jenis_pembayaran === 'bank' && $sharingRevenue->account_bank_id) {
                $bankLama = AccountBank::find($sharingRevenue->account_bank_id);
                if ($bankLama) {
                    $bankLama->increment('saldo', $sharingRevenue->nominal);
                }
            }

            $sharingRevenue->update($input);

            $sharingRevenue->refresh();
            if ($sharingRevenue->status === 'selesai' && $sharingRevenue->jenis_pembayaran === 'bank' && $sharingRevenue->account_bank_id) {
                $bankBaru = AccountBank::find($sharingRevenue->account_bank_id);
                if ($bankBaru) {
                    $bankBaru->decrement('saldo', $sharingRevenue->nominal);
                }
            }
        });

        return redirect()->route('sharing-revenue.index')->with('success', 'Sharing revenue berhasil diperbarui.');
    }

    public function destroy(SharingRevenue $sharingRevenue)
    {
        DB::transaction(function () use ($sharingRevenue) {
            if ($sharingRevenue->status === 'selesai' && $sharingRevenue->jenis_pembayaran === 'bank' && $sharingRevenue->account_bank_id) {
                $bank = AccountBank::find($sharingRevenue->account_bank_id);
                if ($bank) {
                    $bank->increment('saldo', $sharingRevenue->nominal);
                }
            }

            $sharingRevenue->delete();
        });

        return redirect()->back()->with('success', 'Sharing revenue berhasil dihapus.');
    }

    public function approve(SharingRevenue $sharingRevenue)
    {
        $sharingRevenue->update(['reject_reason' => null]);
        app(ApprovalService::class)->approve($sharingRevenue);
        app(NotifikasiService::class)->kirimKeRole('Owner',
            'Sharing Revenue Disetujui',
            'Sharing revenue untuk "' . $sharingRevenue->nama_penerima . '" telah disetujui.',
            'info',
            route('sharing-revenue.show', $sharingRevenue->id)
        );

        if ($sharingRevenue->created_by) {
            app(NotifikasiService::class)->kirim(
                $sharingRevenue->created_by,
                'Sharing Revenue Disetujui',
                'Sharing revenue untuk "' . $sharingRevenue->nama_penerima . '" telah disetujui.',
                'info',
                route('sharing-revenue.show', $sharingRevenue->id)
            );
        }

        return redirect()->back()->with('success', 'Sharing revenue berhasil di-approve.');
    }

    public function reject(Request $request, SharingRevenue $sharingRevenue)
    {
        $validated = $request->validate([
            'alasan_reject' => 'required|string|min:5|max:1000',
        ], [
            'alasan_reject.required' => 'Alasan reject wajib diisi.',
            'alasan_reject.min' => 'Alasan reject minimal 5 karakter.',
        ]);

        $sharingRevenue->update(['reject_reason' => $validated['alasan_reject']]);
        app(ApprovalService::class)->reject($sharingRevenue);

        if ($sharingRevenue->created_by) {
            app(NotifikasiService::class)->kirim(
                $sharingRevenue->created_by,
                'Sharing Revenue Ditolak',
                'Sharing revenue untuk "' . $sharingRevenue->nama_penerima . '" ditolak. Alasan: ' . $validated['alasan_reject'],
                'warning',
                route('sharing-revenue.show', $sharingRevenue->id)
            );
        }

        return redirect()->back()->with('success', 'Sharing revenue berhasil di-reject.');
    }
}
