<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\HutangPiutang;
use App\Models\KategoriHutangPiutang;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use Illuminate\Http\Request;

class HutangPiutangController extends Controller
{
    public function index()
    {
        $data = HutangPiutang::with(['kategoriHutangPiutang', 'accountBank'])->latest()->get();
        $kategoriHutangPiutangs = KategoriHutangPiutang::orderBy('nama')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('keuangan.hutang-piutang.index', compact('data', 'kategoriHutangPiutangs', 'accountBanks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:hutang,piutang',
            'aktivitas' => 'required|string',
            'kategori_hutang_piutang_id' => 'required|uuid|exists:kategori_hutang_piutangs,id',
            'nominal' => 'required|numeric|min:0',
            'jatuh_tempo' => 'required|date',
            'nominal_bayar' => 'nullable|numeric|min:0',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'catatan' => 'nullable|string',
        ]);

        $input = $request->only(['jenis','aktivitas','kategori_hutang_piutang_id','nominal','jatuh_tempo','nominal_bayar','jenis_pembayaran','account_bank_id','catatan']);
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate($request->jenis === 'hutang' ? 'INVH' : 'INVP');
        $input['sisa_pembayaran'] = ($input['nominal'] ?? 0) - ($input['nominal_bayar'] ?? 0);

        HutangPiutang::create($input);
        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(HutangPiutang $hutangPiutang)
    {
        $data = $hutangPiutang->toArray();
        $data['jatuh_tempo'] = $hutangPiutang->jatuh_tempo?->format('Y-m-d');
        return response()->json($data);
    }

    public function update(Request $request, HutangPiutang $hutangPiutang)
    {
        $request->validate([
            'jenis' => 'required|in:hutang,piutang',
            'aktivitas' => 'required|string',
            'kategori_hutang_piutang_id' => 'required|uuid|exists:kategori_hutang_piutangs,id',
            'nominal' => 'required|numeric|min:0',
            'jatuh_tempo' => 'required|date',
            'nominal_bayar' => 'nullable|numeric|min:0',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'catatan' => 'nullable|string',
        ]);

        $input = $request->only(['jenis','aktivitas','kategori_hutang_piutang_id','nominal','jatuh_tempo','nominal_bayar','jenis_pembayaran','account_bank_id','catatan']);
        $input['sisa_pembayaran'] = ($input['nominal'] ?? 0) - ($input['nominal_bayar'] ?? 0);

        $hutangPiutang->update($input);
        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(HutangPiutang $hutangPiutang) { $hutangPiutang->delete(); return redirect()->back()->with('success', 'Data berhasil dihapus.'); }
    public function approve(HutangPiutang $hutangPiutang) { app(ApprovalService::class)->approve($hutangPiutang); return redirect()->back()->with('success', 'Data berhasil di-approve.'); }
    public function reject(HutangPiutang $hutangPiutang) { app(ApprovalService::class)->reject($hutangPiutang); return redirect()->back()->with('success', 'Data berhasil di-reject.'); }
}
