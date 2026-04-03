<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\KategoriHutangPiutang;
use Illuminate\Http\Request;

class KategoriHutangPiutangController extends Controller
{
    public function index()
    {
        $data = KategoriHutangPiutang::latest()->get();
        return view('masterdata.kategori-hutang-piutang.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_hutang_piutang' => 'required|string|max:50|unique:kategori_hutang_piutangs,kode_hutang_piutang',
            'deskripsi' => 'required|string',
        ]);
        KategoriHutangPiutang::create($request->only('kode_hutang_piutang', 'deskripsi'));
        return redirect()->back()->with('success', 'Kategori Hutang/Piutang berhasil ditambahkan.');
    }

    public function edit(KategoriHutangPiutang $kategori_hutang_piutang)
    {
        return response()->json($kategori_hutang_piutang);
    }

    public function update(Request $request, KategoriHutangPiutang $kategori_hutang_piutang)
    {
        $request->validate([
            'kode_hutang_piutang' => 'required|string|max:50|unique:kategori_hutang_piutangs,kode_hutang_piutang,' . $kategori_hutang_piutang->id,
            'deskripsi' => 'required|string',
        ]);
        $kategori_hutang_piutang->update($request->only('kode_hutang_piutang', 'deskripsi'));
        return redirect()->back()->with('success', 'Kategori Hutang/Piutang berhasil diperbarui.');
    }

    public function destroy(KategoriHutangPiutang $kategori_hutang_piutang)
    {
        $kategori_hutang_piutang->delete();
        return redirect()->back()->with('success', 'Kategori Hutang/Piutang berhasil dihapus.');
    }
}
