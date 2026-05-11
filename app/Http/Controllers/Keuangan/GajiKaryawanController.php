<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\GajiKaryawan;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\FileUploadService;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GajiKaryawanController extends Controller
{
    public function index()
    {
        $hasTambak = auth()->user()->tambaks()->exists();
        $data = $hasTambak
            ? GajiKaryawan::with(['user', 'accountBank'])->latest()->get()
            : collect();
        return view('keuangan.gaji.index', compact('data'));
    }

    public function create()
    {
        $karyawans = User::orderBy('nama')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('keuangan.gaji.form', compact('karyawans', 'accountBanks') + ['gaji' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'created_at' => 'required|date',
            'user_id' => 'required|uuid|exists:users,id',
            'gaji_pokok' => 'required|numeric|min:0',
            'upah_lembur' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'pajak' => 'nullable|numeric|min:0',
            'bpjs' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'jenis_pembayaran' => 'required|in:cash,bank',
            'account_bank_id' => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'eviden.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,bmp,webp,pdf,xlsx,xls',
        ]);

        $input = $request->only(['user_id','jenis_pembayaran','account_bank_id']);
        $input['created_at'] = \Carbon\Carbon::parse($request->created_at);
        $input['gaji_pokok'] = (int) $request->input('gaji_pokok', 0);
        $input['upah_lembur'] = (int) $request->input('upah_lembur', 0);
        $input['bonus']      = (int) $request->input('bonus', 0);
        $input['pajak']      = (int) $request->input('pajak', 0);
        $input['bpjs']       = (int) $request->input('bpjs', 0);
        $input['potongan']   = (int) $request->input('potongan', 0);
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVG');
        $input['thp'] = $input['gaji_pokok'] + $input['upah_lembur'] + $input['bonus'] - $input['pajak'] - $input['bpjs'] - $input['potongan'];
        $input['created_by'] = auth()->id();

        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        $gaji = GajiKaryawan::create($input);

        $user = User::find($input['user_id']);
        app(NotifikasiService::class)->kirimApprovalRequest(
            'Gaji Karyawan Baru Menunggu Approval',
            'Gaji ' . ($user?->name ?? 'Karyawan') . ' sebesar Rp ' . number_format($input['thp'], 0, ',', '.') . ' memerlukan persetujuan.',
            route('gaji.show', $gaji->id)
        );

        return redirect()->route('gaji.index')->with('success', 'Gaji karyawan berhasil ditambahkan.');
    }

    public function show(GajiKaryawan $gaji)
    {
        $gaji->load(['user', 'accountBank']);
        return view('keuangan.gaji.show', compact('gaji'));
    }

    public function edit(GajiKaryawan $gaji)
    {
        $karyawans = User::orderBy('nama')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('keuangan.gaji.form', compact('gaji', 'karyawans', 'accountBanks'));
    }

    public function update(Request $request, GajiKaryawan $gaji)
    {
        $request->validate([
            'created_at' => 'required|date',
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

        $input = $request->only(['user_id','jenis_pembayaran','account_bank_id']);
        $input['created_at'] = \Carbon\Carbon::parse($request->created_at);
        $input['gaji_pokok'] = (int) $request->input('gaji_pokok', 0);
        $input['upah_lembur'] = (int) $request->input('upah_lembur', 0);
        $input['bonus']      = (int) $request->input('bonus', 0);
        $input['pajak']      = (int) $request->input('pajak', 0);
        $input['bpjs']       = (int) $request->input('bpjs', 0);
        $input['potongan']   = (int) $request->input('potongan', 0);
        $input['thp'] = $input['gaji_pokok'] + $input['upah_lembur'] + $input['bonus'] - $input['pajak'] - $input['bpjs'] - $input['potongan'];

        if ($request->hasFile('eviden')) {
            $existing = $gaji->eviden ?? [];
            foreach ($request->file('eviden') as $file) {
                $existing[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $existing;
        }
        if ($request->filled('hapus_eviden')) {
            $existing = $gaji->eviden ?? [];
            $input['eviden'] = array_values(array_filter($existing, fn($p) => !in_array($p, $request->input('hapus_eviden', []))));
        }

        DB::transaction(function () use ($gaji, $input) {
            // Reverse saldo lama jika sudah selesai via bank (gaji selalu keluar)
            if ($gaji->status === 'selesai' && $gaji->jenis_pembayaran === 'bank' && $gaji->account_bank_id) {
                $bankLama = AccountBank::find($gaji->account_bank_id);
                if ($bankLama) {
                    $bankLama->increment('saldo', $gaji->thp);
                }
            }

            $gaji->update($input);

            // Apply saldo baru jika masih selesai via bank
            $gaji->refresh();
            if ($gaji->status === 'selesai' && $gaji->jenis_pembayaran === 'bank' && $gaji->account_bank_id) {
                $bankBaru = AccountBank::find($gaji->account_bank_id);
                if ($bankBaru) {
                    $bankBaru->decrement('saldo', $gaji->thp);
                }
            }
        });

        return redirect()->route('gaji.index')->with('success', 'Gaji karyawan berhasil diperbarui.');
    }

    public function destroy(GajiKaryawan $gaji)
    {
        DB::transaction(function () use ($gaji) {
            // Reverse saldo jika sudah selesai via bank (gaji selalu keluar)
            if ($gaji->status === 'selesai' && $gaji->jenis_pembayaran === 'bank' && $gaji->account_bank_id) {
                $bank = AccountBank::find($gaji->account_bank_id);
                if ($bank) {
                    $bank->increment('saldo', $gaji->thp);
                }
            }

            $gaji->delete();
        });

        return redirect()->back()->with('success', 'Gaji karyawan berhasil dihapus.');
    }

    public function approve(GajiKaryawan $gaji)
    {
        $gaji->update(['reject_reason' => null]);
        app(ApprovalService::class)->approve($gaji);
        app(NotifikasiService::class)->kirim(
            $gaji->user_id,
            'Gaji Disetujui',
            'Gaji Anda sebesar Rp ' . number_format($gaji->thp, 0, ',', '.') . ' telah disetujui.',
            'info',
            route('gaji.show', $gaji->id)
        );
        return redirect()->back()->with('success', 'Gaji berhasil di-approve.');
    }

    public function reject(Request $request, GajiKaryawan $gaji)
    {
        $validated = $request->validate([
            'alasan_reject' => 'required|string|min:5|max:1000',
        ], [
            'alasan_reject.required' => 'Alasan reject wajib diisi.',
            'alasan_reject.min' => 'Alasan reject minimal 5 karakter.',
        ]);

        $gaji->update(['reject_reason' => $validated['alasan_reject']]);
        app(ApprovalService::class)->reject($gaji);
        app(NotifikasiService::class)->kirim(
            $gaji->created_by ?: $gaji->user_id,
            'Gaji Ditolak',
            'Gaji sebesar Rp ' . number_format($gaji->thp, 0, ',', '.') . ' ditolak. Alasan: ' . $validated['alasan_reject'],
            'warning',
            route('gaji.show', $gaji->id)
        );
        return redirect()->back()->with('success', 'Gaji berhasil di-reject.');
    }
}
