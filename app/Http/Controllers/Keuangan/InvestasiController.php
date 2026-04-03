<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\Investasi;
use App\Models\KategoriInvestasi;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

class InvestasiController extends Controller
{
    public function index()
    {
        $data = Investasi::with(['kategoriInvestasi', 'accountBank'])->latest()->get();
        $kategoriInvestasis = KategoriInvestasi::orderBy('nama')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('keuangan.investasi.index', compact('data', 'kategoriInvestasis', 'accountBanks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'deskripsi' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'kategori_investasi_id' => 'required|uuid|exists:kategori_investasis,id',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'eviden' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'catatan' => 'nullable|string',
        ]);

        $input = $request->only(['deskripsi','nominal','kategori_investasi_id','jenis_pembayaran','account_bank_id','catatan']);
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVI');
        if ($request->hasFile('eviden')) $input['eviden'] = app(FileUploadService::class)->upload($request->file('eviden'));

        Investasi::create($input);
        return redirect()->back()->with('success', 'Investasi berhasil ditambahkan.');
    }

    public function edit(Investasi $investasi) { return response()->json($investasi); }

    public function update(Request $request, Investasi $investasi)
    {
        $request->validate([
            'deskripsi' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'kategori_investasi_id' => 'required|uuid|exists:kategori_investasis,id',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'eviden' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'catatan' => 'nullable|string',
        ]);

        $input = $request->only(['deskripsi','nominal','kategori_investasi_id','jenis_pembayaran','account_bank_id','catatan']);
        if ($request->hasFile('eviden')) $input['eviden'] = app(FileUploadService::class)->upload($request->file('eviden'));

        $investasi->update($input);
        return redirect()->back()->with('success', 'Investasi berhasil diperbarui.');
    }

    public function destroy(Investasi $investasi) { $investasi->delete(); return redirect()->back()->with('success', 'Investasi berhasil dihapus.'); }
    public function approve(Investasi $investasi) { app(ApprovalService::class)->approve($investasi); return redirect()->back()->with('success', 'Investasi berhasil di-approve.'); }
    public function reject(Investasi $investasi) { app(ApprovalService::class)->reject($investasi); return redirect()->back()->with('success', 'Investasi berhasil di-reject.'); }
}
