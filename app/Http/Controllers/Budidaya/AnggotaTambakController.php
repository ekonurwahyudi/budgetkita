<?php

namespace App\Http\Controllers\Budidaya;

use App\Http\Controllers\Controller;
use App\Models\Tambak;
use App\Models\TambakAnggota;
use App\Models\User;
use Illuminate\Http\Request;

class AnggotaTambakController extends Controller
{
    public function index(Tambak $tambak)
    {
        $tambak->load('anggotas.user');
        $users = User::where('status', 'aktif')
            ->whereNotIn('id', $tambak->anggotas->pluck('user_id'))
            ->orderBy('nama')
            ->get();
        return view('budidaya.tambak.anggota', compact('tambak', 'users'));
    }

    public function store(Request $request, Tambak $tambak)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'peran' => 'required|in:owner,anggota',
        ]);

        TambakAnggota::firstOrCreate(
            ['tambak_id' => $tambak->id, 'user_id' => $request->user_id],
            ['peran' => $request->peran]
        );

        return redirect()->back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function destroy(Tambak $tambak, TambakAnggota $anggota)
    {
        if ($anggota->peran === 'owner') {
            return redirect()->back()->with('error', 'Owner tambak tidak bisa dihapus.');
        }
        $anggota->delete();
        return redirect()->back()->with('success', 'Anggota berhasil dihapus.');
    }
}
