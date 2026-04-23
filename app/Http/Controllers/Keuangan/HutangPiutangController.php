<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\HutangPiutang;
use App\Models\KategoriHutangPiutang;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

class HutangPiutangController extends Controller
{
    public function index()
    {
        $hasTambak = auth()->user()->tambaks()->exists();
        $data = $hasTambak
            ? HutangPiutang::with(['kategoriHutangPiutang', 'accountBank'])->latest()->get()
            : collect();
        return view('keuangan.hutang-piutang.index', compact('data'));
    }

    public function create()
    {
        $kategoriHutangPiutangs = KategoriHutangPiutang::orderBy('deskripsi')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('keuangan.hutang-piutang.form', compact('kategoriHutangPiutangs', 'accountBanks') + ['hutangPiutang' => null]);
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
            'eviden.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'catatan' => 'nullable|string',
        ]);

        $input = $request->only(['jenis','aktivitas','kategori_hutang_piutang_id','nominal','jatuh_tempo','nominal_bayar','jenis_pembayaran','account_bank_id','catatan']);
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate($request->jenis === 'hutang' ? 'INVH' : 'INVP');
        $input['sisa_pembayaran'] = ($input['nominal'] ?? 0) - ($input['nominal_bayar'] ?? 0);

        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        HutangPiutang::create($input);
        return redirect()->route('hutang-piutang.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function show(HutangPiutang $hutangPiutang)
    {
        $hutangPiutang->load(['kategoriHutangPiutang', 'accountBank']);
        return view('keuangan.hutang-piutang.show', compact('hutangPiutang'));
    }

    public function edit(HutangPiutang $hutangPiutang)
    {
        $kategoriHutangPiutangs = KategoriHutangPiutang::orderBy('deskripsi')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('keuangan.hutang-piutang.form', compact('hutangPiutang', 'kategoriHutangPiutangs', 'accountBanks'));
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

        if ($request->hasFile('eviden')) {
            $existing = $hutangPiutang->eviden ?? [];
            foreach ($request->file('eviden') as $file) {
                $existing[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $existing;
        }
        if ($request->filled('hapus_eviden')) {
            $existing = $hutangPiutang->eviden ?? [];
            $input['eviden'] = array_values(array_filter($existing, fn($p) => !in_array($p, $request->input('hapus_eviden', []))));
        }

        $hutangPiutang->update($input);
        return redirect()->route('hutang-piutang.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(HutangPiutang $hutangPiutang) { $hutangPiutang->delete(); return redirect()->back()->with('success', 'Data berhasil dihapus.'); }

    public function bayar(Request $request, HutangPiutang $hutangPiutang)
    {
        $request->validate([
            'jumlah_bayar'   => 'required|numeric|min:1',
            'account_bank_id'=> 'nullable|uuid|exists:account_banks,id',
            'catatan_bayar'  => 'nullable|string',
        ]);

        $jumlah = (float) $request->jumlah_bayar;
        $sisaBaru = max(0, ($hutangPiutang->sisa_pembayaran ?? $hutangPiutang->nominal) - $jumlah);
        $nominalBayarBaru = ($hutangPiutang->nominal_bayar ?? 0) + $jumlah;

        $hutangPiutang->update([
            'nominal_bayar'   => $nominalBayarBaru,
            'sisa_pembayaran' => $sisaBaru,
        ]);

        // Kurangi saldo bank jika bayar via bank
        if ($request->filled('account_bank_id')) {
            \App\Models\AccountBank::where('id', $request->account_bank_id)->decrement('saldo', $jumlah);
        }

        $msg = 'Pembayaran Rp ' . number_format($jumlah, 0, ',', '.') . ' berhasil dicatat.';
        if ($sisaBaru <= 0) $msg .= ' Hutang/Piutang sudah LUNAS.';

        return redirect()->back()->with('success', $msg);
    }

    public function approve(HutangPiutang $hutangPiutang) { app(ApprovalService::class)->approve($hutangPiutang); return redirect()->back()->with('success', 'Data berhasil di-approve.'); }
    public function reject(HutangPiutang $hutangPiutang) { app(ApprovalService::class)->reject($hutangPiutang); return redirect()->back()->with('success', 'Data berhasil di-reject.'); }
}
