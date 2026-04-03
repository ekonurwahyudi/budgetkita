<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\KategoriTransaksi;
use Illuminate\Http\Request;

class KategoriTransaksiController extends Controller
{
    public function index()
    {
        $data = KategoriTransaksi::latest()->get();
        return view('masterdata.kategori-transaksi.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'required|string|max:50|unique:kategori_transaksis,kode_kategori',
            'deskripsi' => 'required|string',
        ]);
        KategoriTransaksi::create($request->only('kode_kategori', 'deskripsi'));
        return redirect()->back()->with('success', 'Kategori Transaksi berhasil ditambahkan.');
    }

    public function edit(KategoriTransaksi $kategori_transaksi)
    {
        return response()->json($kategori_transaksi);
    }

    public function update(Request $request, KategoriTransaksi $kategori_transaksi)
    {
        $request->validate([
            'kode_kategori' => 'required|string|max:50|unique:kategori_transaksis,kode_kategori,' . $kategori_transaksi->id,
            'deskripsi' => 'required|string',
        ]);
        $kategori_transaksi->update($request->only('kode_kategori', 'deskripsi'));
        return redirect()->back()->with('success', 'Kategori Transaksi berhasil diperbarui.');
    }

    public function destroy(KategoriTransaksi $kategori_transaksi)
    {
        $kategori_transaksi->delete();
        return redirect()->back()->with('success', 'Kategori Transaksi berhasil dihapus.');
    }
}
