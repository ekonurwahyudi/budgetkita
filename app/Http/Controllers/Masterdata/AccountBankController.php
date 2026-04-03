<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use Illuminate\Http\Request;

class AccountBankController extends Controller
{
    public function index()
    {
        $data = AccountBank::latest()->get();
        $banks = [
            'Cash',
            'Bank Central Asia (BCA)',
            'Bank Republik Indonesia (BRI)',
            'Bank Negara Indonesia (BNI)',
            'Bank Mandiri',
            'Bank Syariah Indonesia (BSI)',
            'Bank Aceh',
            'CIMB Niaga',
            'Bank Danamon',
            'Bank Permata',
            'OCBC NISP',
            'Bank Mega',
            'Bank BTN',
            'Bank Muamalat',
            'Bank BTPN',
            'Bank Jago',
            'Bank Panin',
        ];
        return view('masterdata.account-bank.index', compact('data', 'banks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_account' => 'required|string|max:50|unique:account_banks,kode_account',
            'nama_bank' => 'required|string|max:255',
            'nama_pemilik' => 'nullable|string|max:255',
            'nomor_rekening' => 'nullable|string|max:50',
            'saldo' => 'required|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif',
        ]);
        AccountBank::create($request->only('kode_account', 'nama_bank', 'nama_pemilik', 'nomor_rekening', 'saldo', 'status'));
        return redirect()->back()->with('success', 'Account Bank berhasil ditambahkan.');
    }

    public function edit(AccountBank $account_bank)
    {
        return response()->json($account_bank);
    }

    public function update(Request $request, AccountBank $account_bank)
    {
        $request->validate([
            'kode_account' => 'required|string|max:50|unique:account_banks,kode_account,' . $account_bank->id,
            'nama_bank' => 'required|string|max:255',
            'nama_pemilik' => 'nullable|string|max:255',
            'nomor_rekening' => 'nullable|string|max:50',
            'saldo' => 'required|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif',
        ]);
        $account_bank->update($request->only('kode_account', 'nama_bank', 'nama_pemilik', 'nomor_rekening', 'saldo', 'status'));
        return redirect()->back()->with('success', 'Account Bank berhasil diperbarui.');
    }

    public function destroy(AccountBank $account_bank)
    {
        $account_bank->delete();
        return redirect()->back()->with('success', 'Account Bank berhasil dihapus.');
    }
}
