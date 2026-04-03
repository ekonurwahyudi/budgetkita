<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\ItemTransaksi;
use App\Models\KategoriTransaksi;
use App\Models\SumberDana;
use App\Models\Tambak;
use App\Models\TransaksiKeuangan;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

class TransaksiKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $query = TransaksiKeuangan::with(['itemTransaksi', 'kategoriTransaksi', 'tambak', 'sumberDana', 'accountBank']);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('jenis_transaksi')) $query->where('jenis_transaksi', $request->jenis_transaksi);
        $data = $query->latest()->get();

        $kategoriTransaksis = KategoriTransaksi::orderBy('deskripsi')->get();
        $itemTransaksis = ItemTransaksi::orderBy('kode_item')->get();
        $tambaks = Tambak::orderBy('nama_tambak')->get();
        $sumberDanas = SumberDana::orderBy('deskripsi')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();

        return view('keuangan.transaksi.index', compact(
            'data', 'kategoriTransaksis', 'itemTransaksis', 'tambaks', 'sumberDanas', 'accountBanks'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_transaksi' => 'required|in:uang_masuk,uang_keluar,cash_card',
            'tgl_kwitansi' => 'required|date',
            'aktivitas' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'item_transaksi_id' => 'required|uuid|exists:item_transaksis,id',
            'kategori_transaksi_id' => 'required|uuid|exists:kategori_transaksis,id',
            'tambak_id' => 'required|uuid|exists:tambaks,id',
            'blok_id' => 'nullable|uuid|exists:bloks,id',
            'siklus_id' => 'nullable|uuid|exists:sikluses,id',
            'sumber_dana_id' => 'required|uuid|exists:sumber_danas,id',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'eviden' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'catatan' => 'nullable|string',
        ]);

        $input = $request->only([
            'jenis_transaksi', 'tgl_kwitansi', 'aktivitas', 'nominal',
            'item_transaksi_id', 'kategori_transaksi_id', 'tambak_id',
            'blok_id', 'siklus_id', 'sumber_dana_id', 'jenis_pembayaran',
            'account_bank_id', 'catatan',
        ]);

        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVT');

        if ($request->hasFile('eviden')) {
            $input['eviden'] = app(FileUploadService::class)->upload($request->file('eviden'));
        }

        TransaksiKeuangan::create($input);
        return redirect()->back()->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function edit(TransaksiKeuangan $transaksi)
    {
        $data = $transaksi->toArray();
        $data['tgl_kwitansi'] = $transaksi->tgl_kwitansi?->format('Y-m-d');
        return response()->json($data);
    }

    public function update(Request $request, TransaksiKeuangan $transaksi)
    {
        $request->validate([
            'jenis_transaksi' => 'required|in:uang_masuk,uang_keluar,cash_card',
            'tgl_kwitansi' => 'required|date',
            'aktivitas' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'item_transaksi_id' => 'required|uuid|exists:item_transaksis,id',
            'kategori_transaksi_id' => 'required|uuid|exists:kategori_transaksis,id',
            'tambak_id' => 'required|uuid|exists:tambaks,id',
            'blok_id' => 'nullable|uuid|exists:bloks,id',
            'siklus_id' => 'nullable|uuid|exists:sikluses,id',
            'sumber_dana_id' => 'required|uuid|exists:sumber_danas,id',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'eviden' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'catatan' => 'nullable|string',
        ]);

        $input = $request->only([
            'jenis_transaksi', 'tgl_kwitansi', 'aktivitas', 'nominal',
            'item_transaksi_id', 'kategori_transaksi_id', 'tambak_id',
            'blok_id', 'siklus_id', 'sumber_dana_id', 'jenis_pembayaran',
            'account_bank_id', 'catatan',
        ]);

        if ($request->hasFile('eviden')) {
            $input['eviden'] = app(FileUploadService::class)->upload($request->file('eviden'));
        }

        $transaksi->update($input);
        return redirect()->back()->with('success', 'Transaksi berhasil diperbarui.');
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

    // API: item transaksi by kategori
    public function itemsByKategori(KategoriTransaksi $kategori)
    {
        return response()->json($kategori->itemTransaksis()->orderBy('kode_item')->get());
    }
}
