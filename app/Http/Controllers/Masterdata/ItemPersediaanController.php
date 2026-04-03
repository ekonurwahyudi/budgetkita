<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\ItemPersediaan;
use App\Models\KategoriPersediaan;
use Illuminate\Http\Request;

class ItemPersediaanController extends Controller
{
    public function index()
    {
        $data = ItemPersediaan::with('kategoriPersediaan')->latest()->get();
        $kategoriPersediaans = KategoriPersediaan::orderBy('deskripsi')->get();
        return view('masterdata.item-persediaan.index', compact('data', 'kategoriPersediaans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_persediaan_id' => 'required|uuid|exists:kategori_persediaans,id',
            'kode_item_persediaan' => 'required|string|max:50|unique:item_persediaans,kode_item_persediaan',
            'deskripsi' => 'required|string',
        ]);
        ItemPersediaan::create($request->only('kategori_persediaan_id', 'kode_item_persediaan', 'deskripsi'));
        return redirect()->back()->with('success', 'Item Persediaan berhasil ditambahkan.');
    }

    public function edit(ItemPersediaan $item_persediaan)
    {
        return response()->json($item_persediaan);
    }

    public function update(Request $request, ItemPersediaan $item_persediaan)
    {
        $request->validate([
            'kategori_persediaan_id' => 'required|uuid|exists:kategori_persediaans,id',
            'kode_item_persediaan' => 'required|string|max:50|unique:item_persediaans,kode_item_persediaan,' . $item_persediaan->id,
            'deskripsi' => 'required|string',
        ]);
        $item_persediaan->update($request->only('kategori_persediaan_id', 'kode_item_persediaan', 'deskripsi'));
        return redirect()->back()->with('success', 'Item Persediaan berhasil diperbarui.');
    }

    public function destroy(ItemPersediaan $item_persediaan)
    {
        $item_persediaan->delete();
        return redirect()->back()->with('success', 'Item Persediaan berhasil dihapus.');
    }
}
