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
        $hasTambak = auth()->user()->tambaks()->exists();
        $data = $hasTambak
            ? Investasi::with(['kategoriInvestasi', 'accountBank'])->latest()->get()
            : collect();
        return view('keuangan.investasi.index', compact('data'));
    }

    public function create()
    {
        $kategoriInvestasis = KategoriInvestasi::orderBy('deskripsi')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('keuangan.investasi.form', compact('kategoriInvestasis', 'accountBanks') + ['investasi' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'deskripsi' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'kategori_investasi_id' => 'required|uuid|exists:kategori_investasis,id',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'eviden.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'catatan' => 'nullable|string',
        ]);

        $input = $request->only(['deskripsi','nominal','kategori_investasi_id','jenis_pembayaran','account_bank_id','catatan']);
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVI');
        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        Investasi::create($input);
        return redirect()->route('investasi.index')->with('success', 'Investasi berhasil ditambahkan.');
    }

    public function show(Investasi $investasi)
    {
        $investasi->load(['kategoriInvestasi', 'accountBank']);
        return view('keuangan.investasi.show', compact('investasi'));
    }

    public function edit(Investasi $investasi)
    {
        $kategoriInvestasis = KategoriInvestasi::orderBy('deskripsi')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('keuangan.investasi.form', compact('investasi', 'kategoriInvestasis', 'accountBanks'));
    }

    public function update(Request $request, Investasi $investasi)
    {
        $request->validate([
            'deskripsi' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'kategori_investasi_id' => 'required|uuid|exists:kategori_investasis,id',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'eviden.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'catatan' => 'nullable|string',
        ]);

        $input = $request->only(['deskripsi','nominal','kategori_investasi_id','jenis_pembayaran','account_bank_id','catatan']);
        if ($request->hasFile('eviden')) {
            $existing = $investasi->eviden ?? [];
            foreach ($request->file('eviden') as $file) {
                $existing[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $existing;
        }
        if ($request->filled('hapus_eviden')) {
            $existing = $investasi->eviden ?? [];
            $input['eviden'] = array_values(array_filter($existing, fn($p) => !in_array($p, $request->input('hapus_eviden', []))));
        }

        $investasi->update($input);
        return redirect()->route('investasi.index')->with('success', 'Investasi berhasil diperbarui.');
    }

    public function destroy(Investasi $investasi) { $investasi->delete(); return redirect()->back()->with('success', 'Investasi berhasil dihapus.'); }
    public function approve(Investasi $investasi) { app(ApprovalService::class)->approve($investasi); return redirect()->back()->with('success', 'Investasi berhasil di-approve.'); }
    public function reject(Investasi $investasi) { app(ApprovalService::class)->reject($investasi); return redirect()->back()->with('success', 'Investasi berhasil di-reject.'); }
}
