<?php

namespace App\Http\Controllers\Keuangan;

use App\Exports\TransaksiKeuanganExport;
use App\Http\Controllers\Controller;
use App\Imports\TransaksiKeuanganImport;
use App\Models\AccountBank;
use App\Models\Blok;
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

        $data = $query->latest()->get();

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

        $data = $query->latest()->get();
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
            'jenis_pembayaran'      => 'required|in:cash,bank',
            'account_bank_id'       => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'eviden.*'              => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'catatan'               => 'nullable|string',
        ]);

        $input = $request->only([
            'jenis_transaksi', 'tgl_kwitansi', 'aktivitas', 'nominal',
            'item_transaksi_id', 'kategori_transaksi_id', 'tambak_id',
            'blok_id', 'siklus_id', 'sumber_dana_id', 'jenis_pembayaran',
            'account_bank_id', 'catatan',
        ]);

        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVT');
        $input['created_by'] = auth()->id();

        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        $transaksi = TransaksiKeuangan::create($input);

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
            'jenis_pembayaran'      => 'required|in:cash,bank',
            'account_bank_id'       => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'eviden.*'              => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'catatan'               => 'nullable|string',
        ]);

        $input = $request->only([
            'jenis_transaksi', 'tgl_kwitansi', 'aktivitas', 'nominal',
            'item_transaksi_id', 'kategori_transaksi_id', 'tambak_id',
            'blok_id', 'siklus_id', 'sumber_dana_id', 'jenis_pembayaran',
            'account_bank_id', 'catatan',
        ]);

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
                        $bankLama->decrement('saldo', $transaksi->nominal);
                    } elseif ($transaksi->jenis_transaksi === 'uang_keluar') {
                        $bankLama->increment('saldo', $transaksi->nominal);
                    }
                }
            }

            $transaksi->update($input);

            // Apply saldo baru jika transaksi masih selesai via bank
            $transaksi->refresh();
            if ($transaksi->status === 'selesai' && $transaksi->jenis_pembayaran === 'bank' && $transaksi->account_bank_id) {
                $bankBaru = AccountBank::find($transaksi->account_bank_id);
                if ($bankBaru) {
                    if ($transaksi->jenis_transaksi === 'uang_masuk') {
                        $bankBaru->increment('saldo', $transaksi->nominal);
                    } elseif ($transaksi->jenis_transaksi === 'uang_keluar') {
                        $bankBaru->decrement('saldo', $transaksi->nominal);
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
                        $bank->decrement('saldo', $transaksi->nominal);
                    } elseif ($transaksi->jenis_transaksi === 'uang_keluar') {
                        $bank->increment('saldo', $transaksi->nominal);
                    }
                }
            }

            $transaksi->delete();
        });

        return redirect()->back()->with('success', 'Transaksi berhasil dihapus.');
    }

    public function approve(TransaksiKeuangan $transaksi)
    {
        $transaksi->update(['reject_reason' => null]);
        app(ApprovalService::class)->approve($transaksi);
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
}
