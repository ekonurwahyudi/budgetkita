<?php

namespace App\Http\Controllers\Budidaya;

use App\Http\Controllers\Controller;
use App\Models\Tambak;
use App\Models\TambakAnggota;
use Illuminate\Http\Request;

class TambakController extends Controller
{
    public function index()
    {
        $data = Tambak::withCount('bloks')->latest()->get();
        return view('budidaya.tambak.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_tambak' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'total_lahan' => 'nullable|numeric|min:0',
            'didirikan_pada' => 'required|date',
            'catatan' => 'nullable|string',
        ]);
        $tambak = Tambak::create($request->only('nama_tambak', 'lokasi', 'alamat', 'total_lahan', 'didirikan_pada', 'catatan'));

        // Auto-add creator as owner
        TambakAnggota::create([
            'tambak_id' => $tambak->id,
            'user_id' => auth()->id(),
            'peran' => 'owner',
        ]);

        return redirect()->back()->with('success', 'Tambak berhasil ditambahkan.');
    }

    public function edit(Tambak $tambak)
    {
        return response()->json($tambak);
    }

    public function update(Request $request, Tambak $tambak)
    {
        $request->validate([
            'nama_tambak' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'total_lahan' => 'nullable|numeric|min:0',
            'didirikan_pada' => 'required|date',
            'catatan' => 'nullable|string',
        ]);
        $tambak->update($request->only('nama_tambak', 'lokasi', 'alamat', 'total_lahan', 'didirikan_pada', 'catatan'));
        return redirect()->back()->with('success', 'Tambak berhasil diperbarui.');
    }

    public function destroy(Tambak $tambak)
    {
        $tambak->delete();
        return redirect()->back()->with('success', 'Tambak berhasil dihapus.');
    }
}
