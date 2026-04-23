<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\KategoriAset;
use App\Models\PembelianAset;
use App\Services\ApprovalService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

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
            'umur_manfaat'       => 'required|integer|min:1',
            'nilai_residu'       => 'required|numeric|min:0',
            'jenis_pembayaran'   => 'required|in:cash,bank',
            'account_bank_id'    => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'catatan'            => 'nullable|string',
        ]);

        $input = $request->only([
            'nama_aset', 'kategori_aset_id', 'tgl_pembelian',
            'nominal_pembelian', 'umur_manfaat', 'nilai_residu',
            'jenis_pembayaran', 'account_bank_id', 'catatan',
        ]);

        PembelianAset::create($input);
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
            'umur_manfaat'       => 'required|integer|min:1',
            'nilai_residu'       => 'required|numeric|min:0',
            'jenis_pembayaran'   => 'required|in:cash,bank',
            'account_bank_id'    => 'nullable|required_if:jenis_pembayaran,bank|uuid|exists:account_banks,id',
            'catatan'            => 'nullable|string',
        ]);

        $input = $request->only([
            'nama_aset', 'kategori_aset_id', 'tgl_pembelian',
            'nominal_pembelian', 'umur_manfaat', 'nilai_residu',
            'jenis_pembayaran', 'account_bank_id', 'catatan',
        ]);

        $pembelianAset->update($input);
        return redirect()->route('pembelian-aset.index')->with('success', 'Pembelian aset berhasil diperbarui.');
    }

    public function destroy(PembelianAset $pembelianAset)
    {
        $pembelianAset->delete();
        return redirect()->back()->with('success', 'Pembelian aset berhasil dihapus.');
    }

    public function approve(PembelianAset $pembelianAset)
    {
        app(ApprovalService::class)->approve($pembelianAset);
        return redirect()->back()->with('success', 'Pembelian aset berhasil di-approve.');
    }

    public function reject(PembelianAset $pembelianAset)
    {
        app(ApprovalService::class)->reject($pembelianAset);
        return redirect()->back()->with('success', 'Pembelian aset berhasil di-reject.');
    }
}
