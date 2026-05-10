<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\KategoriAset;
use App\Models\PembelianAset;
use App\Services\ApprovalService;
use App\Services\AutoNumberService;
use App\Services\FileUploadService;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PembelianAsetController extends Controller
{
    public function index()
    {
        $hasTambak = auth()->user()->tambaks()->exists();
        $data = $hasTambak
            ? PembelianAset::with(['kategoriAset', 'accountBank'])->latest()->get()
            : collect();
        return view('operasional.pembelian-aset.index', compact('data'));
    }

    public function create()
    {
        $kategoriAsets = KategoriAset::orderBy('deskripsi')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('operasional.pembelian-aset.form', compact('kategoriAsets', 'accountBanks') + ['pembelianAset' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_aset'          => 'required|string|max:255',
            'kategori_aset_id'   => 'required|uuid|exists:kategori_asets,id',
            'tgl_pembelian'      => 'required|date',
            'nominal_pembelian'  => 'required|numeric|min:0',
            'umur_manfaat'       => 'nullable|integer|min:0',
            'nilai_residu'       => 'nullable|numeric|min:0',
            'metode_depresiasi'  => 'required|in:garis_lurus,persen,tanpa',
            'persen_depresiasi'  => 'nullable|numeric|min:0|max:100',
            'jenis_pembayaran'   => 'required|in:cash,bank',
            'account_bank_id'    => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'catatan'            => 'nullable|string',
            'eviden.*'           => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'foto_aset.*'        => 'nullable|file|max:5120|mimes:jpg,jpeg,png',
        ]);

        $input = $request->only([
            'nama_aset', 'kategori_aset_id', 'tgl_pembelian',
            'nominal_pembelian', 'metode_depresiasi',
            'jenis_pembayaran', 'account_bank_id', 'catatan',
        ]);

        $input['umur_manfaat'] = $request->input('umur_manfaat') ?: 0;
        $input['nilai_residu'] = $request->input('nilai_residu') ?: 0;
        $input['nomor_transaksi'] = app(AutoNumberService::class)->generate('INVA');

        if ($input['metode_depresiasi'] === 'persen') {
            $input['persen_depresiasi'] = $request->input('persen_depresiasi') ?: 0;
        } elseif ($input['metode_depresiasi'] === 'tanpa') {
            $input['persen_depresiasi'] = null;
            $input['umur_manfaat'] = 0;
            $input['nilai_residu'] = 0;
        } else {
            $input['persen_depresiasi'] = null;
        }

        if ($request->hasFile('eviden')) {
            $paths = [];
            foreach ($request->file('eviden') as $file) {
                $paths[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $paths;
        }

        if ($request->hasFile('foto_aset')) {
            $fotos = [];
            foreach ($request->file('foto_aset') as $file) {
                $fotos[] = app(FileUploadService::class)->upload($file);
            }
            $input['foto_aset'] = $fotos;
        }

        $aset = PembelianAset::create($input);

        app(NotifikasiService::class)->kirimApprovalRequest(
            'Pembelian Aset Menunggu Approval',
            'Pembelian aset "' . $input['nama_aset'] . '" sebesar Rp ' . number_format($input['nominal_pembelian'], 0, ',', '.') . ' memerlukan persetujuan.',
            route('pembelian-aset.show', $aset->id)
        );

        return redirect()->route('pembelian-aset.index')->with('success', 'Pembelian aset berhasil ditambahkan.');
    }

    public function show(PembelianAset $pembelianAset)
    {
        $pembelianAset->load(['kategoriAset', 'accountBank']);
        return view('operasional.pembelian-aset.show', compact('pembelianAset'));
    }

    public function edit(PembelianAset $pembelianAset)
    {
        $kategoriAsets = KategoriAset::orderBy('deskripsi')->get();
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        return view('operasional.pembelian-aset.form', compact('pembelianAset', 'kategoriAsets', 'accountBanks'));
    }

    public function update(Request $request, PembelianAset $pembelianAset)
    {
        $request->validate([
            'nama_aset'          => 'required|string|max:255',
            'kategori_aset_id'   => 'required|uuid|exists:kategori_asets,id',
            'tgl_pembelian'      => 'required|date',
            'nominal_pembelian'  => 'required|numeric|min:0',
            'umur_manfaat'       => 'nullable|integer|min:0',
            'nilai_residu'       => 'nullable|numeric|min:0',
            'metode_depresiasi'  => 'required|in:garis_lurus,persen,tanpa',
            'persen_depresiasi'  => 'nullable|numeric|min:0|max:100',
            'jenis_pembayaran'   => 'required|in:cash,bank',
            'account_bank_id'    => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'catatan'            => 'nullable|string',
            'eviden.*'           => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'foto_aset.*'        => 'nullable|file|max:5120|mimes:jpg,jpeg,png',
        ]);

        $input = $request->only([
            'nama_aset', 'kategori_aset_id', 'tgl_pembelian',
            'nominal_pembelian', 'metode_depresiasi',
            'jenis_pembayaran', 'account_bank_id', 'catatan',
        ]);

        $input['umur_manfaat'] = $request->input('umur_manfaat') ?: 0;
        $input['nilai_residu'] = $request->input('nilai_residu') ?: 0;

        if ($input['metode_depresiasi'] === 'persen') {
            $input['persen_depresiasi'] = $request->input('persen_depresiasi') ?: 0;
        } elseif ($input['metode_depresiasi'] === 'tanpa') {
            $input['persen_depresiasi'] = null;
            $input['umur_manfaat'] = 0;
            $input['nilai_residu'] = 0;
        } else {
            $input['persen_depresiasi'] = null;
        }

        if ($request->hasFile('eviden')) {
            $existing = $pembelianAset->eviden ?? [];
            foreach ($request->file('eviden') as $file) {
                $existing[] = app(FileUploadService::class)->upload($file);
            }
            $input['eviden'] = $existing;
        }
        if ($request->filled('hapus_eviden')) {
            $existing = $pembelianAset->eviden ?? [];
            $input['eviden'] = array_values(array_filter($existing, fn($p) => !in_array($p, $request->input('hapus_eviden', []))));
        }

        if ($request->hasFile('foto_aset')) {
            $existing = $pembelianAset->foto_aset ?? [];
            foreach ($request->file('foto_aset') as $file) {
                $existing[] = app(FileUploadService::class)->upload($file);
            }
            $input['foto_aset'] = $existing;
        }
        if ($request->filled('hapus_foto')) {
            $existing = $pembelianAset->foto_aset ?? [];
            $input['foto_aset'] = array_values(array_filter($existing, fn($p) => !in_array($p, $request->input('hapus_foto', []))));
        }

        DB::transaction(function () use ($pembelianAset, $input) {
            // Reverse saldo lama jika sudah selesai via bank (pembelian selalu keluar)
            if ($pembelianAset->status === 'selesai' && $pembelianAset->jenis_pembayaran === 'bank' && $pembelianAset->account_bank_id) {
                $bankLama = AccountBank::find($pembelianAset->account_bank_id);
                if ($bankLama) {
                    $bankLama->increment('saldo', $pembelianAset->nominal_pembelian);
                }
            }

            $pembelianAset->update($input);

            // Apply saldo baru jika masih selesai via bank
            $pembelianAset->refresh();
            if ($pembelianAset->status === 'selesai' && $pembelianAset->jenis_pembayaran === 'bank' && $pembelianAset->account_bank_id) {
                $bankBaru = AccountBank::find($pembelianAset->account_bank_id);
                if ($bankBaru) {
                    $bankBaru->decrement('saldo', $pembelianAset->nominal_pembelian);
                }
            }
        });

        return redirect()->route('pembelian-aset.index')->with('success', 'Pembelian aset berhasil diperbarui.');
    }

    public function destroy(PembelianAset $pembelianAset)
    {
        DB::transaction(function () use ($pembelianAset) {
            // Reverse saldo jika sudah selesai via bank (pembelian selalu keluar)
            if ($pembelianAset->status === 'selesai' && $pembelianAset->jenis_pembayaran === 'bank' && $pembelianAset->account_bank_id) {
                $bank = AccountBank::find($pembelianAset->account_bank_id);
                if ($bank) {
                    $bank->increment('saldo', $pembelianAset->nominal_pembelian);
                }
            }

            $pembelianAset->delete();
        });

        return redirect()->back()->with('success', 'Pembelian aset berhasil dihapus.');
    }

    public function approve(PembelianAset $pembelianAset)
    {
        app(ApprovalService::class)->approve($pembelianAset);
        app(NotifikasiService::class)->kirimKeRole('Owner',
            'Pembelian Aset Disetujui',
            'Pembelian aset "' . $pembelianAset->nama_aset . '" telah disetujui.',
            'info',
            route('pembelian-aset.show', $pembelianAset->id)
        );
        return redirect()->back()->with('success', 'Pembelian aset berhasil di-approve.');
    }

    public function reject(PembelianAset $pembelianAset)
    {
        app(ApprovalService::class)->reject($pembelianAset);
        app(NotifikasiService::class)->kirimKeRole('Owner',
            'Pembelian Aset Ditolak',
            'Pembelian aset "' . $pembelianAset->nama_aset . '" telah ditolak.',
            'warning',
            route('pembelian-aset.show', $pembelianAset->id)
        );
        return redirect()->back()->with('success', 'Pembelian aset berhasil di-reject.');
    }
}
