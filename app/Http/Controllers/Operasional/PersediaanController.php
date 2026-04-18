<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Models\Persediaan;
use App\Models\PenyesuaianPersediaan;
use App\Models\RiwayatPersediaan;
use Illuminate\Http\Request;

class PersediaanController extends Controller
{
    public function index()
    {
        $data = Persediaan::with('itemPersediaan.kategoriPersediaan')->latest()->get();
        return view('operasional.persediaan.index', compact('data'));
    }

    public function show(Persediaan $persediaan)
    {
        $persediaan->load(['itemPersediaan.kategoriPersediaan', 'riwayats.blok', 'riwayats.siklus']);
        $penyesuaians = PenyesuaianPersediaan::where('persediaan_id', $persediaan->id)->latest()->get();
        return view('operasional.persediaan.show', compact('persediaan', 'penyesuaians'));
    }

    public function adjust(Request $request, Persediaan $persediaan)
    {
        $request->validate([
            'qty_fisik' => 'required|numeric|min:0',
            'catatan'   => 'required|string',
        ]);

        $qtyFisik = (float) $request->qty_fisik;
        $qtySistem = (float) $persediaan->qty;

        PenyesuaianPersediaan::create([
            'persediaan_id'   => $persediaan->id,
            'tgl_penyesuaian' => now(),
            'qty_sistem'      => $qtySistem,
            'qty_fisik'       => $qtyFisik,
            'catatan'         => $request->catatan,
        ]);

        $selisih = $qtyFisik - $qtySistem;

        RiwayatPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis'         => $selisih >= 0 ? 'penambahan' : 'pengeluaran',
            'qty_masuk'     => $selisih >= 0 ? abs($selisih) : 0,
            'qty_keluar'    => $selisih < 0 ? abs($selisih) : 0,
            'catatan'       => 'Penyesuaian stok: ' . $request->catatan,
        ]);

        $persediaan->update(['qty' => $qtyFisik]);

        return redirect()->route('persediaan.show', $persediaan)->with('success', 'Penyesuaian stok berhasil.');
    }
}
