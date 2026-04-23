<?php

namespace App\Http\Controllers\Keuangan;

use App\Exports\TransaksiKeuanganExport;
use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\Blok;
use App\Models\ItemTransaksi;
use App\Models\KategoriTransaksi;
use App\Models\Siklus;
use App\Models\SumberDana;
use App\Models\Tambak;
use App\Models\TransaksiKeuangan;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransaksiKeuanganController extends Controller
{
    private function formData(): array
    {
        $tambakIds = auth()->user()->tambaks()->pluck('tambaks.id');
        return [
            'kategoriTransaksis' => KategoriTransaksi::orderBy('deskripsi')->get(),
            'itemTransaksis'     => ItemTransaksi::orderBy('kode_item')->get(),
            'tambaks'            => Tambak::whereIn('id', $tambakIds)->orderBy('nama_tambak')->get(),
            'sumberDanas'        => SumberDana::orderBy('deskripsi')->get(),
            'accountBanks'       => AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get(),
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

        $data = $query->latest('tgl_kwitansi')->get();

        $tambakIds2 = auth()->user()->tambaks()->pluck('tambaks.id');
        $kategoriTransaksis = KategoriTransaksi::orderBy('deskripsi')->get();
        $bloks = Blok::whereIn('tambak_id', $tambakIds2)->orderBy('nama_blok')->get();
        $sikluses = Siklus::whereHas('blok', fn ($q) => $q->whereIn('tambak_id', $tambakIds2))->orderBy('nama_siklus')->get();

        // Counts per tab - hitung dari total tanpa filter jenis_transaksi
        $baseQuery = TransaksiKeuangan::whereIn('tambak_id', $tambakIds);
        if ($request->filled('kategori_transaksi_id')) $baseQuery->where('kategori_transaksi_id', $request->kategori_transaksi_id);
        if ($request->filled('blok_id')) $baseQuery->where('blok_id', $request->blok_id);
        if ($request->filled('siklus_id')) $baseQuery->where('siklus_id', $request->siklus_id);
        if ($request->filled('tgl_dari')) $baseQuery->whereDate('tgl_kwitansi', '>=', $request->tgl_dari);
        if ($request->filled('tgl_sampai')) $baseQuery->whereDate('tgl_kwitansi', '<=', $request->tgl_sampai);
        if ($request->filled('status')) $baseQuery->where('status', $request->status);

        $allCount = (clone $baseQuery)->count();
        $counts = [
            'all'         => $allCount,
            'uang_masuk'  => (clone $baseQuery)->where('jenis_transaksi', 'uang_masuk')->count(),
            'uang_keluar' => (clone $baseQuery)->where('jenis_transaksi', 'uang_keluar')->count(),
        ];

        return view('keuangan.transaksi.index', compact('data', 'kategoriTransaksis', 'bloks', 'sikluses', 'counts'));
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

        $data = $query->latest('tgl_kwitansi')->get();
        $filename = 'transaksi-keuangan-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new TransaksiKeuanganExport($data), $filename);
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
            'eviden.*'              => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'catatan'               => 'nullable|string',
        ]);

        $input = $request->only([
            'jenis_transaksi', 'tgl_kwitansi', 'aktivitas', 'nominal',
            'item_transaksi_id', 'kategori_transaksi_id', 'tambak_id',
            'blok_id', 'siklus_id', 'sumber_dana_id', 'jenis_pembayaran',
            'account_bank_id', 'catatan',
        ]);

        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVT');

        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        TransaksiKeuangan::create($input);
        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function show(TransaksiKeuangan $transaksi)
    {
        $transaksi->load(['itemTransaksi', 'kategoriTransaksi', 'tambak', 'blok', 'siklus', 'sumberDana', 'accountBank']);
        return view('keuangan.transaksi.show', compact('transaksi'));
    }

    public function edit(TransaksiKeuangan $transaksi)
    {        $bloks = $transaksi->tambak_id ? Blok::where('tambak_id', $transaksi->tambak_id)->orderBy('nama_blok')->get() : collect();
        $sikluses = $transaksi->blok_id ? Siklus::where('blok_id', $transaksi->blok_id)->orderBy('nama_siklus')->get() : collect();
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
            'eviden.*'              => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
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

        $transaksi->update($input);
        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(TransaksiKeuangan $transaksi)
    {
        $transaksi->delete();
        return redirect()->back()->with('success', 'Transaksi berhasil dihapus.');
    }

    public function approve(TransaksiKeuangan $transaksi)
    {
        app(ApprovalService::class)->approve($transaksi);
        return redirect()->back()->with('success', 'Transaksi berhasil di-approve.');
    }

    public function reject(TransaksiKeuangan $transaksi)
    {
        app(ApprovalService::class)->reject($transaksi);
        return redirect()->back()->with('success', 'Transaksi berhasil di-reject.');
    }

    public function itemsByKategori(KategoriTransaksi $kategori)
    {
        return response()->json($kategori->itemTransaksis()->orderBy('kode_item')->get());
    }
}
