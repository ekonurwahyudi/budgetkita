<?php

namespace App\Http\Controllers\Budidaya;

use App\Http\Controllers\Controller;
use App\Models\Panen;
use App\Models\Siklus;
use Illuminate\Http\Request;

class PanenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'siklus_id' => 'required|uuid|exists:sikluses,id',
            'tgl_panen' => 'required|date',
            'umur' => 'required|integer|min:0',
            'ukuran' => 'required|numeric|min:0',
            'total_berat' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'total_penjualan' => 'required|numeric|min:0',
            'pembeli' => 'required|string|max:255',
            'tipe_panen' => 'required|in:full,parsial,gagal',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|uuid|exists:account_banks,id',
            'pembayaran' => 'required|in:lunas,piutang',
            'sisa_bayar' => 'nullable|numeric|min:0',
        ]);

        $input = $request->only([
            'siklus_id', 'tgl_panen', 'umur', 'ukuran', 'total_berat',
            'harga_jual', 'total_penjualan', 'pembeli', 'tipe_panen',
            'jenis_pembayaran', 'account_bank_id', 'pembayaran', 'sisa_bayar',
        ]);

        if ($request->pembayaran === 'lunas') {
            $input['sisa_bayar'] = 0;
        }

        Panen::create($input);

        return redirect()->back()->with('success', 'Data panen berhasil ditambahkan.');
    }

    public function destroy(Panen $panen)
    {
        $panen->delete();
        return redirect()->back()->with('success', 'Data panen berhasil dihapus.');
    }
}
