<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\GajiKaryawan;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

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
        $input['gaji_pokok'] = (int) $request->input('gaji_pokok', 0);
        $input['upah_lembur'] = (int) $request->input('upah_lembur', 0);
        $input['bonus']      = (int) $request->input('bonus', 0);
        $input['pajak']      = (int) $request->input('pajak', 0);
        $input['bpjs']       = (int) $request->input('bpjs', 0);
        $input['potongan']   = (int) $request->input('potongan', 0);
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVG');
        $input['thp'] = $input['gaji_pokok'] + $input['upah_lembur'] + $input['bonus'] - $input['pajak'] - $input['bpjs'] - $input['potongan'];

        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        GajiKaryawan::create($input);
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

        $gaji->update($input);
        return redirect()->route('gaji.index')->with('success', 'Gaji karyawan berhasil diperbarui.');
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
