<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\GajiKaryawan;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use Illuminate\Http\Request;

class GajiKaryawanController extends Controller
{
    public function index()
    {
        $data = GajiKaryawan::with(['user', 'accountBank'])->latest()->get();
        $karyawans = User::orderBy('name')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('keuangan.gaji.index', compact('data', 'karyawans', 'accountBanks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'gaji_pokok' => 'required|numeric|min:0',
            'upah_lembur' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'pajak' => 'nullable|numeric|min:0',
            'bpjs' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
        ]);

        $input = $request->only(['user_id','gaji_pokok','upah_lembur','bonus','pajak','bpjs','potongan','jenis_pembayaran','account_bank_id']);
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVG');
        $input['thp'] = ($input['gaji_pokok'] ?? 0) + ($input['upah_lembur'] ?? 0) + ($input['bonus'] ?? 0) - ($input['pajak'] ?? 0) - ($input['bpjs'] ?? 0) - ($input['potongan'] ?? 0);

        GajiKaryawan::create($input);
        return redirect()->back()->with('success', 'Gaji karyawan berhasil ditambahkan.');
    }

    public function edit(GajiKaryawan $gaji)
    {
        return response()->json($gaji);
    }

    public function update(Request $request, GajiKaryawan $gaji)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'gaji_pokok' => 'required|numeric|min:0',
            'upah_lembur' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'pajak' => 'nullable|numeric|min:0',
            'bpjs' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
        ]);

        $input = $request->only(['user_id','gaji_pokok','upah_lembur','bonus','pajak','bpjs','potongan','jenis_pembayaran','account_bank_id']);
        $input['thp'] = ($input['gaji_pokok'] ?? 0) + ($input['upah_lembur'] ?? 0) + ($input['bonus'] ?? 0) - ($input['pajak'] ?? 0) - ($input['bpjs'] ?? 0) - ($input['potongan'] ?? 0);

        $gaji->update($input);
        return redirect()->back()->with('success', 'Gaji karyawan berhasil diperbarui.');
    }

    public function destroy(GajiKaryawan $gaji)
    {
        $gaji->delete();
        return redirect()->back()->with('success', 'Gaji karyawan berhasil dihapus.');
    }

    public function approve(GajiKaryawan $gaji)
    {
        app(ApprovalService::class)->approve($gaji);
        return redirect()->back()->with('success', 'Gaji berhasil di-approve.');
    }

    public function reject(GajiKaryawan $gaji)
    {
        app(ApprovalService::class)->reject($gaji);
        return redirect()->back()->with('success', 'Gaji berhasil di-reject.');
    }
}
