<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\Investasi;
use App\Models\KategoriInvestasi;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\FileUploadService;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'created_at' => 'required|date',
            'deskripsi' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'kategori_investasi_id' => 'required|uuid|exists:kategori_investasis,id',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'eviden.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'catatan' => 'nullable|string',
        ]);

        $input = $request->only(['deskripsi','nominal','kategori_investasi_id','jenis_pembayaran','account_bank_id','catatan']);
        $input['created_at'] = \Carbon\Carbon::parse($request->created_at);
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVI');
        $input['created_by'] = auth()->id();
        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        $investasi = Investasi::create($input);

        app(NotifikasiService::class)->kirimApprovalRequest(
            'Investasi Baru Menunggu Approval',
            'Investasi "' . $input['deskripsi'] . '" sebesar Rp ' . number_format($input['nominal'], 0, ',', '.') . ' memerlukan persetujuan.',
            route('investasi.show', $investasi->id)
        );

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
            'created_at' => 'required|date',
            'deskripsi' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'kategori_investasi_id' => 'required|uuid|exists:kategori_investasis,id',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'eviden.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
            'catatan' => 'nullable|string',
        ]);

        $input = $request->only(['deskripsi','nominal','kategori_investasi_id','jenis_pembayaran','account_bank_id','catatan']);
        $input['created_at'] = \Carbon\Carbon::parse($request->created_at);
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

        DB::transaction(function () use ($investasi, $input) {
            // Reverse saldo lama jika sudah selesai via bank (investasi selalu masuk)
            if ($investasi->status === 'selesai' && $investasi->jenis_pembayaran === 'bank' && $investasi->account_bank_id) {
                $bankLama = AccountBank::find($investasi->account_bank_id);
                if ($bankLama) {
                    $bankLama->decrement('saldo', $investasi->nominal);
                }
            }

            $investasi->update($input);

            // Apply saldo baru jika masih selesai via bank
            $investasi->refresh();
            if ($investasi->status === 'selesai' && $investasi->jenis_pembayaran === 'bank' && $investasi->account_bank_id) {
                $bankBaru = AccountBank::find($investasi->account_bank_id);
                if ($bankBaru) {
                    $bankBaru->increment('saldo', $investasi->nominal);
                }
            }
        });

        return redirect()->route('investasi.index')->with('success', 'Investasi berhasil diperbarui.');
    }

    public function destroy(Investasi $investasi)
    {
        DB::transaction(function () use ($investasi) {
            // Reverse saldo jika sudah selesai via bank (investasi selalu masuk)
            if ($investasi->status === 'selesai' && $investasi->jenis_pembayaran === 'bank' && $investasi->account_bank_id) {
                $bank = AccountBank::find($investasi->account_bank_id);
                if ($bank) {
                    $bank->decrement('saldo', $investasi->nominal);
                }
            }

            $investasi->delete();
        });

        return redirect()->back()->with('success', 'Investasi berhasil dihapus.');
    }
    public function approve(Investasi $investasi)
    {
        $investasi->update(['reject_reason' => null]);
        app(ApprovalService::class)->approve($investasi);
        app(NotifikasiService::class)->kirimKeRole('Owner', 'Investasi Disetujui', 'Investasi "' . $investasi->deskripsi . '" telah disetujui.', 'info', route('investasi.show', $investasi->id));
        return redirect()->back()->with('success', 'Investasi berhasil di-approve.');
    }

    public function reject(Request $request, Investasi $investasi)
    {
        $validated = $request->validate([
            'alasan_reject' => 'required|string|min:5|max:1000',
        ], [
            'alasan_reject.required' => 'Alasan reject wajib diisi.',
            'alasan_reject.min' => 'Alasan reject minimal 5 karakter.',
        ]);

        $investasi->update(['reject_reason' => $validated['alasan_reject']]);
        app(ApprovalService::class)->reject($investasi);

        if ($investasi->created_by) {
            app(NotifikasiService::class)->kirim(
                $investasi->created_by,
                'Investasi Ditolak',
                'Investasi "' . $investasi->deskripsi . '" ditolak. Alasan: ' . $validated['alasan_reject'],
                'warning',
                route('investasi.show', $investasi->id)
            );
        }

        return redirect()->back()->with('success', 'Investasi berhasil di-reject.');
    }
}
