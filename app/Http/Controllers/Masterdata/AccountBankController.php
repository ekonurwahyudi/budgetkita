<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\GajiKaryawan;
use App\Models\HutangPiutang;
use App\Models\Investasi;
use App\Models\Panen;
use App\Models\PembelianAset;
use App\Models\PembelianPersediaan;
use App\Models\TransaksiKeuangan;
use App\Models\TransferSaldo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountBankController extends Controller
{
    public function index()
    {
        $data = AccountBank::latest()->get();
        $banks = [
            'Cash',
            'Bank Central Asia (BCA)',
            'Bank Republik Indonesia (BRI)',
            'Bank Negara Indonesia (BNI)',
            'Bank Mandiri',
            'Bank Syariah Indonesia (BSI)',
            'Bank Aceh',
            'CIMB Niaga',
            'Bank Danamon',
            'Bank Permata',
            'OCBC NISP',
            'Bank Mega',
            'Bank BTN',
            'Bank Muamalat',
            'Bank BTPN',
            'Bank Jago',
            'Bank Panin',
        ];
        return view('masterdata.account-bank.index', compact('data', 'banks'));
    }

    public function show(AccountBank $account_bank)
    {
        $id = $account_bank->id;

        // Kumpulkan semua transaksi dari berbagai modul
        $histories = collect();

        // Transaksi Keuangan
        TransaksiKeuangan::where('account_bank_id', $id)->where('jenis_pembayaran', 'bank')->get()
            ->each(fn($t) => $histories->push([
                'tanggal'   => $t->tgl_kwitansi,
                'modul'     => 'Transaksi Keuangan',
                'nomor'     => $t->nomor_transaksi,
                'keterangan'=> $t->aktivitas,
                'jenis'     => $t->jenis_transaksi === 'uang_masuk' ? 'masuk' : 'keluar',
                'nominal'   => $t->nominal,
                'status'    => $t->status,
                'view_url'  => route('transaksi.show', $t->id),
            ]));

        // Gaji Karyawan (pengeluaran)
        GajiKaryawan::with('user')->where('account_bank_id', $id)->where('jenis_pembayaran', 'bank')->get()
            ->each(fn($t) => $histories->push([
                'tanggal'   => $t->created_at,
                'modul'     => 'Gaji Karyawan',
                'nomor'     => $t->nomor_transaksi,
                'keterangan'=> 'Gaji ' . ($t->user?->nama ?? '-'),
                'jenis'     => 'keluar',
                'nominal'   => $t->thp,
                'status'    => $t->status,
                'view_url'  => route('gaji.show', $t->id),
            ]));

        // Investasi (pemasukan)

        // Investasi (pemasukan)
        Investasi::where('account_bank_id', $id)->where('jenis_pembayaran', 'bank')->get()
            ->each(fn($t) => $histories->push([
                'tanggal'   => $t->created_at,
                'modul'     => 'Investasi',
                'nomor'     => $t->nomor_transaksi,
                'keterangan'=> $t->deskripsi,
                'jenis'     => 'masuk',
                'nominal'   => $t->nominal,
                'status'    => $t->status,
                'view_url'  => route('investasi.show', $t->id),
            ]));

        // Hutang Piutang
        HutangPiutang::where('account_bank_id', $id)->where('jenis_pembayaran', 'bank')->get()
            ->each(fn($t) => $histories->push([
                'tanggal'   => $t->created_at,
                'modul'     => 'Hutang/Piutang',
                'nomor'     => $t->nomor_transaksi,
                'keterangan'=> $t->aktivitas,
                'jenis'     => $t->jenis === 'hutang' ? 'masuk' : 'keluar',
                'nominal'   => $t->nominal,
                'status'    => $t->status,
                'view_url'  => route('hutang-piutang.show', $t->id),
            ]));

        // Pembelian Persediaan (pengeluaran)
        PembelianPersediaan::with('items')->where('account_bank_id', $id)->where('jenis_pembayaran', 'bank')->get()
            ->each(fn($t) => $histories->push([
                'tanggal'   => $t->tgl_pembelian,
                'modul'     => 'Pembelian Persediaan',
                'nomor'     => $t->nomor_transaksi,
                'keterangan'=> 'Pembelian persediaan',
                'jenis'     => 'keluar',
                'nominal'   => $t->items->sum('harga_total'),
                'status'    => $t->status,
                'view_url'  => null,
            ]));

        // Pembelian Aset (pengeluaran)
        PembelianAset::where('account_bank_id', $id)->where('jenis_pembayaran', 'bank')->get()
            ->each(fn($t) => $histories->push([
                'tanggal'   => $t->tgl_pembelian,
                'modul'     => 'Pembelian Aset',
                'nomor'     => $t->id,
                'keterangan'=> $t->nama_aset,
                'jenis'     => 'keluar',
                'nominal'   => $t->nominal_pembelian,
                'status'    => $t->status,
                'view_url'  => null,
            ]));

        // Panen (pemasukan)
        Panen::with('siklus')->where('account_bank_id', $id)->where('jenis_pembayaran', 'bank')->get()
            ->each(fn($t) => $histories->push([
                'tanggal'   => $t->tgl_panen,
                'modul'     => 'Panen',
                'nomor'     => $t->id,
                'keterangan'=> 'Panen ' . ($t->siklus?->nama_siklus ?? '-') . ' - ' . ($t->pembeli ?? '-'),
                'jenis'     => 'masuk',
                'nominal'   => $t->total_penjualan,
                'status'    => $t->status,
                'view_url'  => null,
            ]));

        // Transfer Keluar
        TransferSaldo::with('keBank')->where('dari_account_bank_id', $id)->get()
            ->each(fn($t) => $histories->push([
                'tanggal'   => $t->created_at,
                'modul'     => 'Transfer Saldo',
                'nomor'     => 'TRF-' . strtoupper(substr($t->id, 0, 8)),
                'keterangan'=> 'Transfer ke ' . ($t->keBank?->nama_bank ?? '-'),
                'jenis'     => 'keluar',
                'nominal'   => $t->nominal,
                'status'    => 'selesai',
                'view_url'  => null,
            ]));

        // Transfer Masuk
        TransferSaldo::with('dariBank')->where('ke_account_bank_id', $id)->get()
            ->each(fn($t) => $histories->push([
                'tanggal'   => $t->created_at,
                'modul'     => 'Transfer Saldo',
                'nomor'     => 'TRF-' . strtoupper(substr($t->id, 0, 8)),
                'keterangan'=> 'Transfer dari ' . ($t->dariBank?->nama_bank ?? '-'),
                'jenis'     => 'masuk',
                'nominal'   => $t->nominal,
                'status'    => 'selesai',
                'view_url'  => null,
            ]));

        // Sort by tanggal desc
        $histories = $histories->sortByDesc('tanggal')->values();

        return view('masterdata.account-bank.show', compact('account_bank', 'histories'));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'dari_account_bank_id' => 'required|uuid|exists:account_banks,id',
            'ke_account_bank_id'   => 'required|uuid|exists:account_banks,id|different:dari_account_bank_id',
            'nominal'              => 'required|numeric|min:1',
            'catatan'              => 'nullable|string',
        ]);

        $dari = AccountBank::findOrFail($request->dari_account_bank_id);
        $ke   = AccountBank::findOrFail($request->ke_account_bank_id);

        if ($dari->saldo < $request->nominal) {
            return redirect()->back()->with('error', 'Saldo ' . $dari->nama_bank . ' tidak mencukupi.');
        }

        DB::transaction(function () use ($dari, $ke, $request) {
            $dari->decrement('saldo', $request->nominal);
            $ke->increment('saldo', $request->nominal);
            TransferSaldo::create([
                'dari_account_bank_id' => $dari->id,
                'ke_account_bank_id'   => $ke->id,
                'nominal'              => $request->nominal,
                'catatan'              => $request->catatan,
            ]);
        });

        return redirect()->back()->with('success', 'Transfer Rp ' . number_format($request->nominal, 0, ',', '.') . ' berhasil dari ' . $dari->nama_bank . ' ke ' . $ke->nama_bank . '.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_account' => 'required|string|max:50|unique:account_banks,kode_account',
            'nama_bank' => 'required|string|max:255',
            'nama_pemilik' => 'nullable|string|max:255',
            'nomor_rekening' => 'nullable|string|max:50',
            'saldo' => 'required|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif',
        ]);
        AccountBank::create($request->only('kode_account', 'nama_bank', 'nama_pemilik', 'nomor_rekening', 'saldo', 'status'));
        return redirect()->back()->with('success', 'Account Bank berhasil ditambahkan.');
    }

    public function edit(AccountBank $account_bank)
    {
        return response()->json($account_bank);
    }

    public function update(Request $request, AccountBank $account_bank)
    {
        $request->validate([
            'kode_account' => 'required|string|max:50|unique:account_banks,kode_account,' . $account_bank->id,
            'nama_bank' => 'required|string|max:255',
            'nama_pemilik' => 'nullable|string|max:255',
            'nomor_rekening' => 'nullable|string|max:50',
            'saldo' => 'required|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif',
        ]);
        $account_bank->update($request->only('kode_account', 'nama_bank', 'nama_pemilik', 'nomor_rekening', 'saldo', 'status'));
        return redirect()->back()->with('success', 'Account Bank berhasil diperbarui.');
    }

    public function destroy(AccountBank $account_bank)
    {
        $account_bank->delete();
        return redirect()->back()->with('success', 'Account Bank berhasil dihapus.');
    }
}
