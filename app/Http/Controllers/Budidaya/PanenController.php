<?php

namespace App\Http\Controllers\Budidaya;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\HutangPiutang;
use App\Models\KategoriHutangPiutang;
use App\Models\Panen;
use App\Models\Siklus;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PanenController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $hasTambak = $user->tambaks()->exists();
        $query = Panen::with(['siklus.blok.tambak', 'kolam', 'accountBank']);
        if ($hasTambak) {
            $tambakIds = $user->tambaks()->pluck('tambaks.id');
            $query->whereHas('siklus.blok', fn ($q) => $q->whereIn('tambak_id', $tambakIds));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $data = $query->latest()->get();
        return view('budidaya.panen.index', compact('data'));
    }

    public function create()
    {
        $user = auth()->user();
        $hasTambak = $user->tambaks()->exists();
        $siklusQuery = Siklus::where('status', '!=', 'selesai')->with(['blok.tambak', 'kolams' => fn($q) => $q->where('status', '!=', 'selesai')]);
        if ($hasTambak) {
            $tambakIds = $user->tambaks()->pluck('tambaks.id');
            $siklusQuery->whereHas('blok', fn ($q) => $q->whereIn('tambak_id', $tambakIds));
        }
        $sikluses = $siklusQuery->orderBy('nama_siklus')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('budidaya.panen.form', [
            'panen' => null,
            'sikluses' => $sikluses,
            'accountBanks' => $accountBanks,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'siklus_id' => 'required|uuid|exists:sikluses,id',
            'kolam_id' => 'nullable|uuid|exists:kolams,id',
            'tgl_panen' => 'required|date',
            'umur' => 'required|integer|min:0',
            'ukuran' => 'required|numeric|min:0',
            'total_berat' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'total_penjualan' => 'required|numeric|min:0',
            'pembeli' => 'required|string|max:255',
            'tipe_panen' => 'required|in:full,parsial,gagal',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'pembayaran' => 'required|in:lunas,piutang,sebagian',
            'nominal_dibayar' => 'nullable|numeric|min:0',
            'konfirmasi_hutang' => 'nullable|accepted_if:pembayaran,sebagian',
            'sisa_bayar' => 'nullable|numeric|min:0',
        ]);

        $input = $request->only([
            'siklus_id', 'kolam_id', 'tgl_panen', 'umur', 'ukuran', 'total_berat',
            'harga_jual', 'total_penjualan', 'pembeli', 'tipe_panen',
            'jenis_pembayaran', 'account_bank_id', 'pembayaran',
        ]);

        $total = (float) $request->input('total_penjualan');
        $input['nominal_dibayar'] = $this->calculatePaidAmount($request, $total);
        $input['sisa_bayar'] = max(0, $total - $input['nominal_dibayar']);
        $input['status'] = 'awaiting_approval';

        DB::transaction(function () use ($input) {
            $panen = Panen::create($input);
            $this->syncPiutangForPanen($panen);
        });

        app(NotifikasiService::class)->kirimApprovalRequest(
            'Panen Baru Menunggu Approval',
            'Data panen siklus ' . $input['siklus_id'] . ' memerlukan persetujuan.',
            route('panen.index')
        );

        return redirect()->route('panen.index')->with('success', 'Data panen berhasil ditambahkan.');
    }

    public function show(Panen $panen)
    {
        $panen->load(['siklus.blok.tambak', 'kolam', 'accountBank']);
        return view('budidaya.panen.show', compact('panen'));
    }

    public function edit(Panen $panen)
    {
        $user = auth()->user();
        $hasTambak = $user->tambaks()->exists();
        $siklusQuery = Siklus::where('status', '!=', 'selesai')->with(['blok.tambak', 'kolams' => fn($q) => $q->where('status', '!=', 'selesai')]);
        if ($hasTambak) {
            $tambakIds = $user->tambaks()->pluck('tambaks.id');
            $siklusQuery->whereHas('blok', fn ($q) => $q->whereIn('tambak_id', $tambakIds));
        }
        $sikluses = $siklusQuery->orderBy('nama_siklus')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('budidaya.panen.form', [
            'panen' => $panen,
            'sikluses' => $sikluses,
            'accountBanks' => $accountBanks,
        ]);
    }

    public function update(Request $request, Panen $panen)
    {
        $request->validate([
            'siklus_id' => 'required|uuid|exists:sikluses,id',
            'kolam_id' => 'nullable|uuid|exists:kolams,id',
            'tgl_panen' => 'required|date',
            'umur' => 'required|integer|min:0',
            'ukuran' => 'required|numeric|min:0',
            'total_berat' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'total_penjualan' => 'required|numeric|min:0',
            'pembeli' => 'required|string|max:255',
            'tipe_panen' => 'required|in:full,parsial,gagal',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'pembayaran' => 'required|in:lunas,piutang,sebagian',
            'nominal_dibayar' => 'nullable|numeric|min:0',
            'konfirmasi_hutang' => 'nullable|accepted_if:pembayaran,sebagian',
            'sisa_bayar' => 'nullable|numeric|min:0',
        ]);

        $input = $request->only([
            'siklus_id', 'kolam_id', 'tgl_panen', 'umur', 'ukuran', 'total_berat',
            'harga_jual', 'total_penjualan', 'pembeli', 'tipe_panen',
            'jenis_pembayaran', 'account_bank_id', 'pembayaran',
        ]);

        $total = (float) $request->input('total_penjualan');
        $input['nominal_dibayar'] = $this->calculatePaidAmount($request, $total);
        $input['sisa_bayar'] = max(0, $total - $input['nominal_dibayar']);

        DB::transaction(function () use ($panen, $input) {
            // Reverse saldo lama jika sudah selesai via bank (panen selalu masuk)
            if ($panen->status === 'selesai' && $panen->jenis_pembayaran === 'bank' && $panen->account_bank_id) {
                $bankLama = AccountBank::find($panen->account_bank_id);
                if ($bankLama) {
                    $bankLama->decrement('saldo', $this->getPanenPaidAmount($panen));
                }
            }

            $panen->update($input);
            $this->syncPiutangForPanen($panen->refresh());

            // Apply saldo baru jika masih selesai via bank
            $panen->refresh();
            if ($panen->status === 'selesai' && $panen->jenis_pembayaran === 'bank' && $panen->account_bank_id) {
                $bankBaru = AccountBank::find($panen->account_bank_id);
                if ($bankBaru) {
                    $bankBaru->increment('saldo', $this->getPanenPaidAmount($panen));
                }
            }
        });

        return redirect()->route('panen.index')->with('success', 'Data panen berhasil diperbarui.');
    }

    public function destroy(Panen $panen)
    {
        $panen->load(['kolam.siklus']);

        DB::transaction(function () use ($panen) {
            // Reverse saldo jika sudah selesai via bank (panen selalu masuk)
            if ($panen->status === 'selesai' && $panen->jenis_pembayaran === 'bank' && $panen->account_bank_id) {
                $bank = AccountBank::find($panen->account_bank_id);
                if ($bank) {
                    $bank->decrement('saldo', $this->getPanenPaidAmount($panen));
                }
            }

            $this->removeLinkedHutang($panen);

            if ($panen->tipe_panen === 'full' && $panen->kolam_id) {
                $panen->kolam->update(['status' => 'aktif']);

                $siklus = $panen->kolam->siklus;
                $allKolamSelesai = $siklus->kolams()
                    ->where('status', '!=', 'selesai')
                    ->doesntExist();

                if (!$allKolamSelesai && $siklus->status === 'selesai') {
                    $siklus->update(['status' => 'aktif']);
                }
            }

            $panen->delete();
        });

        return redirect()->route('panen.index')->with('success', 'Data panen berhasil dihapus.');
    }

    public function approve(Panen $panen)
    {
        $panen->load(['kolam.siklus']);
        DB::transaction(function () use ($panen) {
            app(ApprovalService::class)->approve($panen);

            $panen->refresh();
            if ($panen->hutang_piutang_id) {
                HutangPiutang::whereKey($panen->hutang_piutang_id)->update([
                    'status' => 'selesai',
                    'reject_reason' => null,
                ]);
            }
        });

        app(NotifikasiService::class)->kirimKeRole('Owner',
            'Panen Disetujui',
            'Data panen telah disetujui.',
            'info',
            route('panen.index')
        );

        if ($panen->tipe_panen === 'full' && $panen->kolam_id) {
            $panen->kolam->update(['status' => 'selesai']);

            $allKolamSelesai = $panen->kolam->siklus->kolams()
                ->where('status', '!=', 'selesai')
                ->doesntExist();

            if ($allKolamSelesai) {
                $panen->kolam->siklus->update(['status' => 'selesai']);
            }
        }

        return redirect()->back()->with('success', 'Panen berhasil di-approve.');
    }

    public function reject(Panen $panen)
    {
        app(ApprovalService::class)->reject($panen);
        if ($panen->hutang_piutang_id) {
            HutangPiutang::whereKey($panen->hutang_piutang_id)->update([
                'status' => 'cancel',
                'reject_reason' => 'Panen ditolak',
            ]);
        }
        app(NotifikasiService::class)->kirimKeRole('Owner',
            'Panen Ditolak',
            'Data panen telah ditolak.',
            'warning',
            route('panen.index')
        );
        return redirect()->back()->with('success', 'Panen berhasil di-reject.');
    }

    private function calculatePaidAmount(Request $request, float $total): float
    {
        return match ($request->input('pembayaran')) {
            'piutang' => 0,
            'sebagian' => min((float) $request->input('nominal_dibayar', 0), $total),
            default => $total,
        };
    }

    private function getPanenPaidAmount(Panen $panen): float
    {
        return match ($panen->pembayaran) {
            'piutang' => 0,
            'sebagian' => (float) ($panen->nominal_dibayar ?? 0),
            default => (float) $panen->total_penjualan,
        };
    }

    private function syncPiutangForPanen(Panen $panen): void
    {
        if (!in_array($panen->pembayaran, ['piutang', 'sebagian'], true)) {
            $this->removeLinkedHutang($panen);
            return;
        }

        $total = (float) $panen->total_penjualan;
        $dibayar = (float) $panen->nominal_dibayar;
        $sisa = max(0, $total - $dibayar);

        if ($sisa <= 0) {
            $this->removeLinkedHutang($panen);
            return;
        }

        $kategori = KategoriHutangPiutang::firstOrCreate(
            ['kode_hutang_piutang' => 'PANEN'],
            ['deskripsi' => 'Piutang Panen']
        );

        $payload = [
            'jenis' => 'piutang',
            'nama_pemberi_hutang' => $panen->pembeli,
            'aktivitas' => 'Penjualan panen ' . $panen->pembeli . ' (' . $panen->tgl_panen->format('Y-m-d') . ')',
            'kategori_hutang_piutang_id' => $kategori->id,
            'nominal' => $sisa,
            'total_bayar' => $sisa,
            'jatuh_tempo' => Carbon::parse($panen->tgl_panen)->addDays(30)->toDateString(),
            'nominal_bayar' => 0,
            'sisa_pembayaran' => $sisa,
            'jenis_pembayaran' => 'cash',
            'account_bank_id' => null,
            'catatan' => 'Otomatis dari panen',
            'status' => $panen->status === 'selesai' ? 'selesai' : 'awaiting_approval',
            'created_by' => $panen->created_by ?? auth()->id(),
            'created_at' => Carbon::parse($panen->tgl_panen),
        ];

        if ($panen->hutang_piutang_id) {
            $hutang = HutangPiutang::find($panen->hutang_piutang_id);
            if ($hutang) {
                $hutang->update($payload);
                return;
            }
        }

        $payload['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVP');
        $piutang = HutangPiutang::create($payload);
        $panen->updateQuietly(['hutang_piutang_id' => $piutang->id]);
    }

    private function removeLinkedHutang(Panen $panen): void
    {
        if (!$panen->hutang_piutang_id) {
            return;
        }

        $hutang = HutangPiutang::with('payments')->find($panen->hutang_piutang_id);
        if ($hutang) {
            if ($hutang->payments->isEmpty()) {
                $hutang->delete();
            } else {
                $hutang->update(['status' => 'cancel']);
            }
        }

        $panen->updateQuietly(['hutang_piutang_id' => null]);
    }
}
