<?php

namespace App\Http\Controllers\Budidaya;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\Panen;
use App\Models\Siklus;
use App\Services\ApprovalService;
use Illuminate\Http\Request;

class PanenController extends Controller
{
    public function index(Request $request)
    {
        $query = Panen::with(['siklus.blok.tambak', 'accountBank']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $data = $query->latest()->get();
        return view('budidaya.panen.index', compact('data'));
    }

    public function create()
    {
        $sikluses = Siklus::where('status', 'aktif')->with('blok.tambak')->orderBy('nama_siklus')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('budidaya.panen.form', [
            'panen' => null,
            'sikluses' => $sikluses,
            'accountBanks' => $accountBanks,
        ]);
    }

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
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'pembayaran' => 'required|in:lunas,piutang',
            'sisa_bayar' => 'nullable|numeric|min:0',
        ]);

        $input = $request->only([
            'siklus_id', 'tgl_panen', 'umur', 'ukuran', 'total_berat',
            'harga_jual', 'total_penjualan', 'pembeli', 'tipe_panen',
            'jenis_pembayaran', 'account_bank_id', 'pembayaran', 'sisa_bayar',
        ]);

        $input['status'] = 'awaiting_approval';
        if ($request->pembayaran === 'lunas') {
            $input['sisa_bayar'] = 0;
        }

        Panen::create($input);
        return redirect()->route('panen.index')->with('success', 'Data panen berhasil ditambahkan.');
    }

    public function show(Panen $panen)
    {
        $panen->load(['siklus.blok.tambak', 'accountBank']);
        return view('budidaya.panen.show', compact('panen'));
    }

    public function edit(Panen $panen)
    {
        $sikluses = Siklus::where('status', 'aktif')->with('blok.tambak')->orderBy('nama_siklus')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('budidaya.panen.form', [
            'panen' => $panen,
            'sikluses' => $sikluses,
            'accountBanks' => $accountBanks,
        ]);
    }

    public function update(Request $request, Panen $panen)
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
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
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

        $panen->update($input);
        return redirect()->route('panen.index')->with('success', 'Data panen berhasil diperbarui.');
    }

    public function destroy(Panen $panen)
    {
        $panen->delete();
        return redirect()->route('panen.index')->with('success', 'Data panen berhasil dihapus.');
    }

    public function approve(Panen $panen)
    {
        app(ApprovalService::class)->approve($panen);
        return redirect()->back()->with('success', 'Panen berhasil di-approve.');
    }

    public function reject(Panen $panen)
    {
        app(ApprovalService::class)->reject($panen);
        return redirect()->back()->with('success', 'Panen berhasil di-reject.');
    }
}
