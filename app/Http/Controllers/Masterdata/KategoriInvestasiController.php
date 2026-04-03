<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\KategoriInvestasi;
use Illuminate\Http\Request;

class KategoriInvestasiController extends Controller
{
    public function index()
    {
        $data = KategoriInvestasi::latest()->get();
        return view('masterdata.kategori-investasi.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_investasi' => 'required|string|max:50|unique:kategori_investasis,kode_investasi',
            'deskripsi' => 'required|string',
        ]);
        KategoriInvestasi::create($request->only('kode_investasi', 'deskripsi'));
        return redirect()->back()->with('success', 'Kategori Investasi berhasil ditambahkan.');
    }

    public function edit(KategoriInvestasi $kategori_investasi)
    {
        return response()->json($kategori_investasi);
    }

    public function update(Request $request, KategoriInvestasi $kategori_investasi)
    {
        $request->validate([
            'kode_investasi' => 'required|string|max:50|unique:kategori_investasis,kode_investasi,' . $kategori_investasi->id,
            'deskripsi' => 'required|string',
        ]);
        $kategori_investasi->update($request->only('kode_investasi', 'deskripsi'));
        return redirect()->back()->with('success', 'Kategori Investasi berhasil diperbarui.');
    }

    public function destroy(KategoriInvestasi $kategori_investasi)
    {
        $kategori_investasi->delete();
        return redirect()->back()->with('success', 'Kategori Investasi berhasil dihapus.');
    }
}
