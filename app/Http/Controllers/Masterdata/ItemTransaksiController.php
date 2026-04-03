<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\ItemTransaksi;
use App\Models\KategoriTransaksi;
use Illuminate\Http\Request;

class ItemTransaksiController extends Controller
{
    public function index()
    {
        $data = ItemTransaksi::with('kategoriTransaksi')->latest()->get();
        $kategoriTransaksis = KategoriTransaksi::orderBy('deskripsi')->get();
        return view('masterdata.item-transaksi.index', compact('data', 'kategoriTransaksis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_transaksi_id' => 'required|uuid|exists:kategori_transaksis,id',
            'kode_item' => 'required|string|max:50|unique:item_transaksis,kode_item',
            'deskripsi' => 'nullable|string',
        ]);
        ItemTransaksi::create($request->only('kategori_transaksi_id', 'kode_item', 'deskripsi'));
        return redirect()->back()->with('success', 'Item Transaksi berhasil ditambahkan.');
    }

    public function edit(ItemTransaksi $item_transaksi)
    {
        return response()->json($item_transaksi);
    }

    public function update(Request $request, ItemTransaksi $item_transaksi)
    {
        $request->validate([
            'kategori_transaksi_id' => 'required|uuid|exists:kategori_transaksis,id',
            'kode_item' => 'required|string|max:50|unique:item_transaksis,kode_item,' . $item_transaksi->id,
            'deskripsi' => 'nullable|string',
        ]);
        $item_transaksi->update($request->only('kategori_transaksi_id', 'kode_item', 'deskripsi'));
        return redirect()->back()->with('success', 'Item Transaksi berhasil diperbarui.');
    }

    public function destroy(ItemTransaksi $item_transaksi)
    {
        $item_transaksi->delete();
        return redirect()->back()->with('success', 'Item Transaksi berhasil dihapus.');
    }
}
