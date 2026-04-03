<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\KategoriAset;
use Illuminate\Http\Request;

class KategoriAsetController extends Controller
{
    public function index()
    {
        $data = KategoriAset::latest()->get();
        return view('masterdata.kategori-aset.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_aset' => 'required|string|max:50|unique:kategori_asets,kode_aset',
            'deskripsi' => 'required|string',
        ]);
        KategoriAset::create($request->only('kode_aset', 'deskripsi'));
        return redirect()->back()->with('success', 'Kategori Aset berhasil ditambahkan.');
    }

    public function edit(KategoriAset $kategori_aset)
    {
        return response()->json($kategori_aset);
    }

    public function update(Request $request, KategoriAset $kategori_aset)
    {
        $request->validate([
            'kode_aset' => 'required|string|max:50|unique:kategori_asets,kode_aset,' . $kategori_aset->id,
            'deskripsi' => 'required|string',
        ]);
        $kategori_aset->update($request->only('kode_aset', 'deskripsi'));
        return redirect()->back()->with('success', 'Kategori Aset berhasil diperbarui.');
    }

    public function destroy(KategoriAset $kategori_aset)
    {
        $kategori_aset->delete();
        return redirect()->back()->with('success', 'Kategori Aset berhasil dihapus.');
    }
}
