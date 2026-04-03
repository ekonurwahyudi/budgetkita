<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\SumberDana;
use Illuminate\Http\Request;

class SumberDanaController extends Controller
{
    public function index()
    {
        $data = SumberDana::latest()->get();
        return view('masterdata.sumber-dana.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_sumber_dana' => 'required|string|max:50|unique:sumber_danas,kode_sumber_dana',
            'deskripsi' => 'required|string',
        ]);
        SumberDana::create($request->only('kode_sumber_dana', 'deskripsi'));
        return redirect()->back()->with('success', 'Sumber Dana berhasil ditambahkan.');
    }

    public function edit(SumberDana $sumber_dana)
    {
        return response()->json($sumber_dana);
    }

    public function update(Request $request, SumberDana $sumber_dana)
    {
        $request->validate([
            'kode_sumber_dana' => 'required|string|max:50|unique:sumber_danas,kode_sumber_dana,' . $sumber_dana->id,
            'deskripsi' => 'required|string',
        ]);
        $sumber_dana->update($request->only('kode_sumber_dana', 'deskripsi'));
        return redirect()->back()->with('success', 'Sumber Dana berhasil diperbarui.');
    }

    public function destroy(SumberDana $sumber_dana)
    {
        $sumber_dana->delete();
        return redirect()->back()->with('success', 'Sumber Dana berhasil dihapus.');
    }
}
