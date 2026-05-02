<?php

namespace App\Http\Controllers\Budidaya;

use App\Http\Controllers\Controller;
use App\Models\Blok;
use App\Models\Tambak;
use Illuminate\Http\Request;

class BlokController extends Controller
{
    public function index(Request $request)
    {
        $tambakIds = auth()->user()->tambaks()->pluck('tambaks.id');
        $query = Blok::with('tambak')->withCount('sikluses')
            ->whereIn('tambak_id', $tambakIds);
        if ($request->filled('tambak_id')) {
            $query->where('tambak_id', $request->tambak_id);
        }
        $data = $query->latest()->get();
        $tambaks = Tambak::whereIn('id', $tambakIds)->orderBy('nama_tambak')->get();
        return view('budidaya.blok.index', compact('data', 'tambaks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tambak_id' => 'required|uuid|exists:tambaks,id',
            'nama_blok' => 'required|string|max:255',
            'didirikan_pada' => 'required|date',
            'jumlah_anco' => 'required|integer|min:0',
            'panjang' => 'required|numeric|min:0',
            'lebar' => 'required|numeric|min:0',
            'kedalaman' => 'required|numeric|min:0',
            'status_blok' => 'required|in:aktif,nonaktif,maintenance',
        ]);
        Blok::create($request->only('tambak_id', 'nama_blok', 'didirikan_pada', 'jumlah_anco', 'panjang', 'lebar', 'kedalaman', 'status_blok'));
        return redirect()->back()->with('success', 'Blok berhasil ditambahkan.');
    }

    public function edit(Blok $blok)
    {
        return response()->json($blok);
    }

    public function update(Request $request, Blok $blok)
    {
        $request->validate([
            'tambak_id' => 'required|uuid|exists:tambaks,id',
            'nama_blok' => 'required|string|max:255',
            'didirikan_pada' => 'required|date',
            'jumlah_anco' => 'required|integer|min:0',
            'panjang' => 'required|numeric|min:0',
            'lebar' => 'required|numeric|min:0',
            'kedalaman' => 'required|numeric|min:0',
            'status_blok' => 'required|in:aktif,nonaktif,maintenance',
        ]);
        $blok->update($request->only('tambak_id', 'nama_blok', 'didirikan_pada', 'jumlah_anco', 'panjang', 'lebar', 'kedalaman', 'status_blok'));
        return redirect()->back()->with('success', 'Blok berhasil diperbarui.');
    }

    public function destroy(Blok $blok)
    {
        $blok->delete();
        return redirect()->back()->with('success', 'Blok berhasil dihapus.');
    }

    public function byTambak(Tambak $tambak)
    {
        return response()->json($tambak->bloks()->where('status_blok', 'aktif')->orderBy('nama_blok')->get());
    }
}
