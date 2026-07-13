<?php

namespace App\Http\Controllers\Keuangan;

use App\Exports\TransaksiKeuanganExport;
use App\Http\Controllers\Controller;
use App\Imports\TransaksiKeuanganImport;
use App\Models\AccountBank;
use App\Models\Blok;
use App\Models\HutangPiutang;
use App\Models\ItemTransaksi;
use App\Models\ItemPersediaan;
use App\Models\KategoriAset;
use App\Models\KategoriHutangPiutang;
use App\Models\KategoriInvestasi;
use App\Models\KategoriPersediaan;
use App\Models\KategoriTransaksi;
use App\Models\Siklus;
use App\Models\SumberDana;
use App\Models\Tambak;
use App\Models\TransaksiKeuangan;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\FileUploadService;
use App\Services\NotifikasiService;
use App\Support\ActiveTambak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TransaksiKeuanganController extends Controller
{
    private function formData(): array
    {
        $tambakIds = auth()->user()->tambaks()->pluck('tambaks.id');
        return [
            'kategoriTransaksis' => KategoriTransaksi::orderBy('deskripsi')->get(),
            'kategoriAsets'      => KategoriAset::orderBy('deskripsi')->get(),
            'kategoriInvestasis' => KategoriInvestasi::orderBy('deskripsi')->get(),
            'kategoriHutangPiutangs' => KategoriHutangPiutang::orderBy('deskripsi')->get(),
            'kategoriPersediaans' => KategoriPersediaan::orderBy('deskripsi')->get(),
            'itemPersediaans'    => ItemPersediaan::with('kategoriPersediaan')->orderBy('deskripsi')->get(),
            'itemTransaksis'     => ItemTransaksi::orderBy('kode_item')->get(),
            'tambaks'            => Tambak::whereIn('id', $tambakIds)->orderBy('nama_tambak')->get(),
            'selectedTambakId'   => old('tambak_id', ActiveTambak::id() ?? $tambakIds->first()),
            'karyawans'          => User::orderBy('nama')->get(),
            'sumberDanas'        => SumberDana::orderBy('deskripsi')->get(),
            'accountBanks'       => AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get(),
            'sikluses'           => Siklus::where('status', '!=', 'selesai')->whereHas('blok', fn ($q) => $q->whereIn('tambak_id', $tambakIds))->orderBy('nama_siklus')->get(),
        ];
    }

    public function index(Request $request)
    {
        $tambakIds = auth()->user()->tambaks()->pluck('tambaks.id');
        $query = TransaksiKeuangan::with(['itemTransaksi', 'kategoriTransaksi', 'tambak', 'blok', 'siklus', 'sumberDana', 'accountBank'])
            ->whereIn('tambak_id', $tambakIds);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('jenis_transaksi')) $query->where('jenis_transaksi', $request->jenis_transaksi);
        if ($request->filled('kategori_transaksi_id')) $query->where('kategori_transaksi_id', $request->kategori_transaksi_id);
        if ($request->filled('blok_id')) $query->where('blok_id', $request->blok_id);
        if ($request->filled('siklus_id')) $query->where('siklus_id', $request->siklus_id);
        if ($request->filled('tgl_dari')) $query->whereDate('tgl_kwitansi', '>=', $request->tgl_dari);
        if ($request->filled('tgl_sampai')) $query->whereDate('tgl_kwitansi', '<=', $request->tgl_sampai);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhere('aktivitas', 'like', "%{$search}%")
                    ->orWhereHas('kategoriTransaksi', fn ($qq) => $qq->where('deskripsi', 'like', "%{$search}%"));
            });
        }

        $data = $query->latest('created_at')->get();

        $tambakIds2 = auth()->user()->tambaks()->pluck('tambaks.id');
        $kategoriTransaksis = KategoriTransaksi::orderBy('deskripsi')->get();
        $itemTransaksis = ItemTransaksi::with('kategoriTransaksi')->orderBy('kode_item')->get();
        $tambaks = Tambak::whereIn('id', $tambakIds2)->orderBy('nama_tambak')->get();
        $bloks = Blok::with('tambak')->whereIn('tambak_id', $tambakIds2)->orderBy('nama_blok')->get();
        $sikluses = Siklus::with('blok')->whereHas('blok', fn ($q) => $q->whereIn('tambak_id', $tambakIds2))->orderBy('nama_siklus')->get();
        $sumberDanas = SumberDana::orderBy('deskripsi')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();

        // Counts per tab - hitung dari total tanpa filter jenis_transaksi
        $baseQuery = TransaksiKeuangan::whereIn('tambak_id', $tambakIds);
        if ($request->filled('kategori_transaksi_id')) $baseQuery->where('kategori_transaksi_id', $request->kategori_transaksi_id);
        if ($request->filled('blok_id')) $baseQuery->where('blok_id', $request->blok_id);
        if ($request->filled('siklus_id')) $baseQuery->where('siklus_id', $request->siklus_id);
        if ($request->filled('tgl_dari')) $baseQuery->whereDate('tgl_kwitansi', '>=', $request->tgl_dari);
        if ($request->filled('tgl_sampai')) $baseQuery->whereDate('tgl_kwitansi', '<=', $request->tgl_sampai);
        if ($request->filled('status')) $baseQuery->where('status', $request->status);
        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhere('aktivitas', 'like', "%{$search}%")
                    ->orWhereHas('kategoriTransaksi', fn ($qq) => $qq->where('deskripsi', 'like', "%{$search}%"));
            });
        }

        $allCount = (clone $baseQuery)->count();
        $counts = [
            'all'         => $allCount,
            'uang_masuk'  => (clone $baseQuery)->where('jenis_transaksi', 'uang_masuk')->count(),
            'uang_keluar' => (clone $baseQuery)->where('jenis_transaksi', 'uang_keluar')->count(),
        ];

        return view('keuangan.transaksi.index', compact('data', 'kategoriTransaksis', 'itemTransaksis', 'tambaks', 'bloks', 'sikluses', 'sumberDanas', 'accountBanks', 'counts'));
    }

    public function export(Request $request)
    {
        $tambakIds = auth()->user()->tambaks()->pluck('tambaks.id');
        $query = TransaksiKeuangan::with(['itemTransaksi', 'kategoriTransaksi', 'tambak', 'blok', 'siklus', 'sumberDana', 'accountBank'])
            ->whereIn('tambak_id', $tambakIds);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('jenis_transaksi')) $query->where('jenis_transaksi', $request->jenis_transaksi);
        if ($request->filled('kategori_transaksi_id')) $query->where('kategori_transaksi_id', $request->kategori_transaksi_id);
        if ($request->filled('blok_id')) $query->where('blok_id', $request->blok_id);
        if ($request->filled('siklus_id')) $query->where('siklus_id', $request->siklus_id);
        if ($request->filled('tgl_dari')) $query->whereDate('tgl_kwitansi', '>=', $request->tgl_dari);
        if ($request->filled('tgl_sampai')) $query->whereDate('tgl_kwitansi', '<=', $request->tgl_sampai);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhere('aktivitas', 'like', "%{$search}%")
                    ->orWhereHas('kategoriTransaksi', fn ($qq) => $qq->where('deskripsi', 'like', "%{$search}%"));
            });
        }

        $data = $query->latest('created_at')->get();
        $filename = 'transaksi-keuangan-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new TransaksiKeuanganExport($data), $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new TransaksiKeuanganImport();
        Excel::import($import, $request->file('file'));

        if ($import->imported() > 0) {
            app(NotifikasiService::class)->kirimApprovalRequest(
                'Import Transaksi Menunggu Approval',
                $import->imported() . ' transaksi hasil import Excel memerlukan persetujuan.',
                route('transaksi.index', ['status' => 'awaiting_approval'])
            );
        }

        return redirect()->route('transaksi.index')->with('success', $import->imported() . ' transaksi berhasil diimport dengan status awaiting.');
    }

    public function create()
    {
        return view('keuangan.transaksi.form', array_merge($this->formData(), ['transaksi' => null]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_transaksi'       => 'required|in:uang_masuk,uang_keluar,cash_card',
            'tgl_kwitansi'          => 'required|date',
            'aktivitas'             => 'required|string',
            'nominal'               => 'required|numeric|min:0',
            'item_transaksi_id'     => 'required|uuid|exists:item_transaksis,id',
            'kategori_transaksi_id' => 'required|uuid|exists:kategori_transaksis,id',
            'tambak_id'             => 'required|uuid|exists:tambaks,id',
            'blok_id'               => 'nullable|uuid|exists:bloks,id',
            'siklus_id'             => 'nullable|uuid|exists:sikluses,id',
            'sumber_dana_id'        => 'required|uuid|exists:sumber_danas,id',
            'status_pembayaran'     => 'required|in:lunas,hutang,sebagian',
            'nominal_dibayar'       => 'nullable|numeric|min:0',
            'konfirmasi_hutang'     => 'nullable|accepted_if:status_pembayaran,sebagian',
            'jenis_pembayaran'      => 'required|in:cash,bank',
            'account_bank_id'       => 'nullable|required_if:status_pembayaran,lunas,sebagian|uuid|exists:account_banks,id',
            'eviden.*'              => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'catatan'               => 'nullable|string',
        ]);

        $input = $request->only([
            'jenis_transaksi', 'tgl_kwitansi', 'aktivitas', 'nominal',
            'item_transaksi_id', 'kategori_transaksi_id', 'tambak_id',
            'blok_id', 'siklus_id', 'sumber_dana_id', 'status_pembayaran',
            'jenis_pembayaran', 'account_bank_id', 'catatan',
        ]);

        $input['nominal'] = (float) $request->input('nominal');
        $input['nominal_dibayar'] = $this->calculatePaidAmount($request, $input['nominal']);
        if ($input['status_pembayaran'] === 'hutang') {
            $input['account_bank_id'] = null;
        }

        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVT');
        $input['created_by'] = auth()->id();

        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        $transaksi = DB::transaction(function () use ($input) {
            $transaksi = TransaksiKeuangan::create($input);
            $this->syncHutangForTransaksi($transaksi);
            return $transaksi;
        });

        app(NotifikasiService::class)->kirimApprovalRequest(
            'Transaksi Baru Menunggu Approval',
            'Transaksi "' . $input['aktivitas'] . '" sebesar Rp ' . number_format($input['nominal'], 0, ',', '.') . ' memerlukan persetujuan.',
            route('transaksi.show', $transaksi->id)
        );

        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function show(TransaksiKeuangan $transaksi)
    {
        $transaksi->load(['itemTransaksi', 'kategoriTransaksi', 'tambak', 'blok', 'siklus', 'sumberDana', 'accountBank']);
        return view('keuangan.transaksi.show', compact('transaksi'));
    }

    public function edit(TransaksiKeuangan $transaksi)
    {
        $bloks = $transaksi->tambak_id ? Blok::where('tambak_id', $transaksi->tambak_id)->orderBy('nama_blok')->get() : collect();
        $sikluses = $transaksi->blok_id ? Siklus::where('blok_id', $transaksi->blok_id)->where('status', '!=', 'selesai')->orderBy('nama_siklus')->get() : collect();
        return view('keuangan.transaksi.form', array_merge($this->formData(), [
            'transaksi' => $transaksi,
            'bloks'     => $bloks,
            'sikluses'  => $sikluses,
        ]));
    }

    public function update(Request $request, TransaksiKeuangan $transaksi)
    {
        $request->validate([
            'jenis_transaksi'       => 'required|in:uang_masuk,uang_keluar,cash_card',
            'tgl_kwitansi'          => 'required|date',
            'aktivitas'             => 'required|string',
            'nominal'               => 'required|numeric|min:0',
            'item_transaksi_id'     => 'required|uuid|exists:item_transaksis,id',
            'kategori_transaksi_id' => 'required|uuid|exists:kategori_transaksis,id',
            'tambak_id'             => 'required|uuid|exists:tambaks,id',
            'blok_id'               => 'nullable|uuid|exists:bloks,id',
            'siklus_id'             => 'nullable|uuid|exists:sikluses,id',
            'sumber_dana_id'        => 'required|uuid|exists:sumber_danas,id',
            'status_pembayaran'     => 'required|in:lunas,hutang,sebagian',
            'nominal_dibayar'       => 'nullable|numeric|min:0',
            'konfirmasi_hutang'     => 'nullable|accepted_if:status_pembayaran,sebagian',
            'jenis_pembayaran'      => 'required|in:cash,bank',
            'account_bank_id'       => 'nullable|required_if:status_pembayaran,lunas,sebagian|uuid|exists:account_banks,id',
            'eviden.*'              => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'catatan'               => 'nullable|string',
        ]);

        $input = $request->only([
            'jenis_transaksi', 'tgl_kwitansi', 'aktivitas', 'nominal',
            'item_transaksi_id', 'kategori_transaksi_id', 'tambak_id',
            'blok_id', 'siklus_id', 'sumber_dana_id', 'status_pembayaran',
            'jenis_pembayaran', 'account_bank_id', 'catatan',
        ]);

        $input['nominal'] = (float) $request->input('nominal');
        $input['nominal_dibayar'] = $this->calculatePaidAmount($request, $input['nominal']);
        if ($input['status_pembayaran'] === 'hutang') {
            $input['account_bank_id'] = null;
        }

        if ($request->hasFile('eviden')) {
            $existing = $transaksi->eviden ?? [];
            foreach ($request->file('eviden') as $file) {
                $existing[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $existing;
        }

        // Handle hapus eviden
        if ($request->filled('hapus_eviden')) {
            $existing = $transaksi->eviden ?? [];
            $hapus = $request->input('hapus_eviden', []);
            $input['eviden'] = array_values(array_filter($existing, fn($p) => !in_array($p, $hapus)));
        }

        DB::transaction(function () use ($transaksi, $input) {
            // Reverse saldo lama jika transaksi sudah selesai via bank
            if ($transaksi->status === 'selesai' && $transaksi->jenis_pembayaran === 'bank' && $transaksi->account_bank_id) {
                $bankLama = AccountBank::find($transaksi->account_bank_id);
                if ($bankLama) {
                    if ($transaksi->jenis_transaksi === 'uang_masuk') {
                        $bankLama->decrement('saldo', $this->getTransaksiPaidAmount($transaksi));
                    } elseif ($transaksi->jenis_transaksi === 'uang_keluar') {
                        $bankLama->increment('saldo', $this->getTransaksiPaidAmount($transaksi));
                    }
                }
            }

            $transaksi->update($input);
            $this->syncHutangForTransaksi($transaksi->refresh());

            // Apply saldo baru jika transaksi masih selesai via bank
            $transaksi->refresh();
            if ($transaksi->status === 'selesai' && $transaksi->jenis_pembayaran === 'bank' && $transaksi->account_bank_id) {
                $bankBaru = AccountBank::find($transaksi->account_bank_id);
                if ($bankBaru) {
                    if ($transaksi->jenis_transaksi === 'uang_masuk') {
                        $bankBaru->increment('saldo', $this->getTransaksiPaidAmount($transaksi));
                    } elseif ($transaksi->jenis_transaksi === 'uang_keluar') {
                        $bankBaru->decrement('saldo', $this->getTransaksiPaidAmount($transaksi));
                    }
                }
            }
        });

        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(TransaksiKeuangan $transaksi)
    {
        DB::transaction(function () use ($transaksi) {
            // Reverse saldo jika transaksi sudah selesai via bank
            if ($transaksi->status === 'selesai' && $transaksi->jenis_pembayaran === 'bank' && $transaksi->account_bank_id) {
                $bank = AccountBank::find($transaksi->account_bank_id);
                if ($bank) {
                    if ($transaksi->jenis_transaksi === 'uang_masuk') {
                        $bank->decrement('saldo', $this->getTransaksiPaidAmount($transaksi));
                    } elseif ($transaksi->jenis_transaksi === 'uang_keluar') {
                        $bank->increment('saldo', $this->getTransaksiPaidAmount($transaksi));
                    }
                }
            }

            $this->removeLinkedHutang($transaksi);
            $transaksi->delete();
        });

        return redirect()->back()->with('success', 'Transaksi berhasil dihapus.');
    }

    public function approve(TransaksiKeuangan $transaksi)
    {
        DB::transaction(function () use ($transaksi) {
            $transaksi->update(['reject_reason' => null]);
            app(ApprovalService::class)->approve($transaksi);

            $transaksi->refresh();
            if ($transaksi->hutang_piutang_id) {
                HutangPiutang::whereKey($transaksi->hutang_piutang_id)->update([
                    'status' => 'selesai',
                    'reject_reason' => null,
                ]);
            }
        });

        app(NotifikasiService::class)->kirimKeRole('Owner',
            'Transaksi Disetujui',
            'Transaksi "' . $transaksi->aktivitas . '" telah disetujui.',
            'info',
            route('transaksi.show', $transaksi->id)
        );
        return redirect()->back()->with('success', 'Transaksi berhasil di-approve.');
    }

    public function reject(Request $request, TransaksiKeuangan $transaksi)
    {
        $validated = $request->validate([
            'alasan_reject' => 'required|string|min:5|max:1000',
        ], [
            'alasan_reject.required' => 'Alasan reject wajib diisi.',
            'alasan_reject.min' => 'Alasan reject minimal 5 karakter.',
        ]);

        $transaksi->update(['reject_reason' => $validated['alasan_reject']]);
        app(ApprovalService::class)->reject($transaksi);
        if ($transaksi->hutang_piutang_id) {
            HutangPiutang::whereKey($transaksi->hutang_piutang_id)->update([
                'status' => 'cancel',
                'reject_reason' => 'Transaksi ditolak: ' . $validated['alasan_reject'],
            ]);
        }

        if ($transaksi->created_by) {
            app(NotifikasiService::class)->kirim(
                $transaksi->created_by,
                'Transaksi Ditolak',
                'Transaksi "' . $transaksi->aktivitas . '" ditolak. Alasan: ' . $validated['alasan_reject'],
                'warning',
                route('transaksi.show', $transaksi->id)
            );
        }

        return redirect()->back()->with('success', 'Transaksi berhasil di-reject.');
    }

    public function itemsByKategori(KategoriTransaksi $kategori)
    {
        return response()->json($kategori->itemTransaksis()->orderBy('kode_item')->get());
    }

    private function calculatePaidAmount(Request $request, float $total): float
    {
        return match ($request->input('status_pembayaran')) {
            'hutang' => 0,
            'sebagian' => min((float) $request->input('nominal_dibayar', 0), $total),
            default => $total,
        };
    }

    private function getTransaksiPaidAmount(TransaksiKeuangan $transaksi): float
    {
        return match ($transaksi->status_pembayaran) {
            'hutang' => 0,
            'sebagian' => (float) ($transaksi->nominal_dibayar ?? 0),
            default => (float) $transaksi->nominal,
        };
    }

    private function syncHutangForTransaksi(TransaksiKeuangan $transaksi): void
    {
        if (!in_array($transaksi->status_pembayaran, ['hutang', 'sebagian'], true)) {
            $this->removeLinkedHutang($transaksi);
            return;
        }

        $total = (float) $transaksi->nominal;
        $dibayar = (float) $transaksi->nominal_dibayar;
        $sisa = max(0, $total - $dibayar);

        if ($sisa <= 0) {
            $this->removeLinkedHutang($transaksi);
            return;
        }

        $isPiutang = $transaksi->jenis_transaksi === 'uang_masuk';
        $kategori = KategoriHutangPiutang::firstOrCreate(
            ['kode_hutang_piutang' => 'TRANS'],
            ['deskripsi' => 'Hutang/Piutang Transaksi']
        );

        $payload = [
            'jenis' => $isPiutang ? 'piutang' : 'hutang',
            'nama_pemberi_hutang' => null,
            'aktivitas' => $transaksi->aktivitas . ' (' . $transaksi->nomor_transaksi . ')',
            'kategori_hutang_piutang_id' => $kategori->id,
            'nominal' => $total,
            'total_bayar' => $total,
            'jatuh_tempo' => Carbon::parse($transaksi->tgl_kwitansi)->addDays(30)->toDateString(),
            'nominal_bayar' => $dibayar,
            'sisa_pembayaran' => $sisa,
            'jenis_pembayaran' => 'cash',
            'account_bank_id' => null,
            'catatan' => trim(($transaksi->catatan ?? '') . "\n\nOtomatis dari transaksi " . $transaksi->nomor_transaksi),
            'status' => $transaksi->status === 'selesai' ? 'selesai' : 'awaiting_approval',
            'created_by' => $transaksi->created_by,
            'created_at' => Carbon::parse($transaksi->tgl_kwitansi),
        ];

        if ($transaksi->hutang_piutang_id) {
            $hutang = HutangPiutang::find($transaksi->hutang_piutang_id);
            if ($hutang) {
                $hutang->update($payload);
                return;
            }
        }

        $payload['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVH');
        $hutang = HutangPiutang::create($payload);
        $transaksi->updateQuietly(['hutang_piutang_id' => $hutang->id]);
    }

    private function removeLinkedHutang(TransaksiKeuangan $transaksi): void
    {
        if (!$transaksi->hutang_piutang_id) {
            return;
        }

        $hutang = HutangPiutang::with('payments')->find($transaksi->hutang_piutang_id);
        if ($hutang) {
            if ($hutang->payments->isEmpty()) {
                $hutang->delete();
            } else {
                $hutang->update(['status' => 'cancel']);
            }
        }

        $transaksi->updateQuietly(['hutang_piutang_id' => null]);
    }
}
