<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Notifikasi::where('user_id', auth()->id());

        if ($request->filled('status')) {
            if ($request->status === 'baru') {
                $query->belumDibaca();
            } elseif ($request->status === 'dibaca') {
                $query->whereNotNull('dibaca_pada');
            }
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('judul', 'ilike', "%{$request->search}%")
                  ->orWhere('pesan', 'ilike', "%{$request->search}%");
            });
        }

        $data = $query->latest()->paginate(10);
        $totalCount = Notifikasi::where('user_id', auth()->id())->count();

        return view('notifikasi.index', compact('data', 'totalCount'));
    }

    public function destroyAll()
    {
        Notifikasi::where('user_id', auth()->id())->delete();
        return redirect()->back()->with('success', 'Semua notifikasi berhasil dihapus.');
    }
}
