<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\KategoriPersediaan;
use Illuminate\Http\Request;

class KategoriPersediaanController extends Controller
{
    public function index()
    {
        $data = KategoriPersediaan::latest()->get();
        return view('masterdata.kategori-persediaan.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_persediaan' => 'required|string|max:50|unique:kategori_persediaans,kode_persediaan',
            'deskripsi' => 'required|string',
        ]);
        KategoriPersediaan::create($request->only('kode_persediaan', 'deskripsi'));
        return redirect()->back()->with('success', 'Kategori Persediaan berhasil ditambahkan.');
    }

    public function edit(KategoriPersediaan $kategori_persediaan)
    {
        return response()->json($kategori_persediaan);
    }

    public function update(Request $request, KategoriPersediaan $kategori_persediaan)
    {
        $request->validate([
            'kode_persediaan' => 'required|string|max:50|unique:kategori_persediaans,kode_persediaan,' . $kategori_persediaan->id,
            'deskripsi' => 'required|string',
        ]);
        $kategori_persediaan->update($request->only('kode_persediaan', 'deskripsi'));
        return redirect()->back()->with('success', 'Kategori Persediaan berhasil diperbarui.');
    }

    public function destroy(KategoriPersediaan $kategori_persediaan)
    {
        $kategori_persediaan->delete();
        return redirect()->back()->with('success', 'Kategori Persediaan berhasil dihapus.');
    }
}
