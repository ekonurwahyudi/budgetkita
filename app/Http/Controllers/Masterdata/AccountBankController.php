<?php

namespace App\Http\Controllers\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\GajiKaryawan;
use App\Models\HutangPiutang;
use App\Models\HutangPiutangPayment;
use App\Models\Investasi;
use App\Models\Panen;
use App\Models\PembelianAset;
use App\Models\PembelianPersediaan;
use App\Models\TransaksiKeuangan;
use App\Models\SaldoAdjustment;
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

    private function getBankHistories(string $id): \Illuminate\Support\Collection
    {
        $histories = collect();

        TransaksiKeuangan::where('account_bank_id', $id)
            ->where('jenis_pembayaran', 'bank')
            ->where('status', 'selesai')
            ->get()
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

        GajiKaryawan::with('user')
            ->where('account_bank_id', $id)
            ->where('jenis_pembayaran', 'bank')
            ->where('status', 'selesai')
            ->get()
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

        Investasi::where('account_bank_id', $id)
            ->where('jenis_pembayaran', 'bank')
            ->where('status', 'selesai')
            ->get()
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

        HutangPiutang::where('account_bank_id', $id)
            ->where('jenis_pembayaran', 'bank')
            ->where('status', 'selesai')
            ->get()
            ->each(fn($t) => $histories->push([
                'tanggal'   => $t->created_at,
                'modul'     => 'Hutang/Piutang',
                'nomor'     => $t->nomor_transaksi,
                'keterangan'=> ($t->jenis === 'hutang' ? '[Hutang Masuk] ' : '[Piutang] ') . $t->aktivitas,
                'jenis'     => $t->jenis === 'hutang' ? 'masuk' : 'keluar',
                'nominal'   => $t->nominal,
                'status'    => $t->status,
                'view_url'  => route('hutang-piutang.show', $t->id),
            ]));

        HutangPiutangPayment::with('hutangPiutang')
            ->where('account_bank_id', $id)
            ->get()
            ->each(fn($p) => $histories->push([
                'tanggal'   => $p->created_at,
                'modul'     => 'Hutang/Piutang',
                'nomor'     => $p->hutangPiutang?->nomor_transaksi,
                'keterangan'=> ($p->hutangPiutang?->jenis === 'hutang' ? '[Bayar Hutang] ' : '[Terima Piutang] ') . ($p->hutangPiutang?->aktivitas ?? '-') . ($p->catatan ? ' - ' . $p->catatan : ''),
                'jenis'     => $p->hutangPiutang?->jenis === 'hutang' ? 'keluar' : 'masuk',
                'nominal'   => $p->jumlah,
                'status'    => 'selesai',
                'view_url'  => $p->hutangPiutang ? route('hutang-piutang.show', $p->hutangPiutang->id) : null,
            ]));

        PembelianPersediaan::with('items')
            ->where('account_bank_id', $id)
            ->where('jenis_pembayaran', 'bank')
            ->where('status', 'selesai')
            ->get()
            ->each(fn($t) => $histories->push([
                'tanggal'   => $t->tgl_pembelian,
                'modul'     => 'Pembelian Persediaan',
                'nomor'     => $t->nomor_transaksi,
                'keterangan'=> 'Pembelian persediaan',
                'jenis'     => 'keluar',
                'nominal'   => $t->items->sum('harga_total'),
                'status'    => $t->status,
                'view_url'  => route('pembelian-persediaan.show', $t->id),
            ]));

        PembelianAset::where('account_bank_id', $id)
            ->where('jenis_pembayaran', 'bank')
            ->where('status', 'selesai')
            ->get()
            ->each(fn($t) => $histories->push([
                'tanggal'   => $t->tgl_pembelian,
                'modul'     => 'Pembelian Aset',
                'nomor'     => $t->id,
                'keterangan'=> $t->nama_aset,
                'jenis'     => 'keluar',
                'nominal'   => $t->nominal_pembelian,
                'status'    => $t->status,
                'view_url'  => route('pembelian-aset.show', $t->id),
            ]));

        Panen::with('siklus')
            ->where('account_bank_id', $id)
            ->where('jenis_pembayaran', 'bank')
            ->where('status', 'selesai')
            ->get()
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

        return $histories;
    }

    private function getBankAdjustments(string $id): \Illuminate\Support\Collection
    {
        return SaldoAdjustment::where('account_bank_id', $id)->get()
            ->map(fn($a) => [
                'tanggal'   => $a->created_at,
                'modul'     => 'Penyesuaian Saldo',
                'nomor'     => 'ADJ-' . strtoupper(substr($a->id, 0, 8)),
                'keterangan'=> ($a->jenis === 'tambah' ? '[Penambahan] ' : '[Pengurangan] ') . ($a->deskripsi ?: 'Penyesuaian saldo manual'),
                'jenis'     => $a->jenis === 'tambah' ? 'masuk' : 'keluar',
                'nominal'   => $a->selisih,
                'status'    => 'selesai',
                'view_url'  => null,
            ]);
    }

    public function show(AccountBank $account_bank)
    {
        $id = $account_bank->id;

        // Transaksi bisnis saja — yang dihitung dalam mutasi
        $histories = $this->getBankHistories($id);

        // Log penyesuaian saldo — untuk audit, TIDAK ikut hitung mutasi
        $adjustments = $this->getBankAdjustments($id);

        // Hitung running balance dari transaksi bisnis (terlama ke terbaru)
        $historiesSortedAsc = $histories->sortBy('tanggal')->values();

        $totalNetChange = $histories->sum(fn($h) => $h['jenis'] === 'masuk' ? $h['nominal'] : -$h['nominal']);
        $saldoAwalTerhitung = $account_bank->saldo - $totalNetChange;

        $runningBalance = $saldoAwalTerhitung;
        $historiesWithBalance = collect();

        foreach ($historiesSortedAsc as $h) {
            if ($h['jenis'] === 'masuk') {
                $runningBalance += $h['nominal'];
            } else {
                $runningBalance -= $h['nominal'];
            }
            $h['running_balance'] = $runningBalance;
            $historiesWithBalance->push($h);
        }

        // Sort by tanggal desc untuk tampilan
        $histories = $historiesWithBalance->sortByDesc('tanggal')->values();

        // Data rekonsiliasi (hanya dari transaksi bisnis)
        $saldoAwalAsli = $account_bank->saldo_awal;
        $saldoSeharusnya = $saldoAwalAsli !== null ? (float) $saldoAwalAsli + $totalNetChange : null;
        $selisih = $saldoSeharusnya !== null ? (float) $account_bank->saldo - $saldoSeharusnya : null;

        return view('masterdata.account-bank.show', compact(
            'account_bank', 'histories', 'adjustments', 'totalNetChange', 'saldoAwalTerhitung', 'saldoSeharusnya', 'selisih'
        ));
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
        $input = $request->only('kode_account', 'nama_bank', 'nama_pemilik', 'nomor_rekening', 'saldo', 'status');
        $input['saldo_awal'] = $request->saldo;
        AccountBank::create($input);
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
            'saldo_awal' => 'nullable|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif',
            'deskripsi_saldo' => 'nullable|string|max:500',
        ]);

        $saldoLama = (float) $account_bank->saldo;
        $saldoBaru = (float) $request->saldo;
        $selisih   = abs($saldoBaru - $saldoLama);

        DB::transaction(function () use ($request, $account_bank, $saldoLama, $saldoBaru, $selisih) {
            $account_bank->update($request->only('kode_account', 'nama_bank', 'nama_pemilik', 'nomor_rekening', 'saldo', 'saldo_awal', 'status'));

            // Catat adjustment saldo jika ada perubahan nilai saldo (bukan saldo_awal)
            if ($selisih > 0) {
                SaldoAdjustment::create([
                    'account_bank_id'  => $account_bank->id,
                    'saldo_sebelumnya' => $saldoLama,
                    'saldo_baru'       => $saldoBaru,
                    'selisih'          => $selisih,
                    'jenis'            => $saldoBaru > $saldoLama ? 'tambah' : 'kurang',
                    'deskripsi'        => $request->deskripsi_saldo ?: 'Penyesuaian saldo manual',
                ]);
            }
        });

        return redirect()->back()->with('success', 'Account Bank berhasil diperbarui.');
    }

    public function syncSaldo(AccountBank $account_bank)
    {
        if ($account_bank->saldo_awal === null) {
            return redirect()->back()->with('error', 'Saldo Awal belum ditentukan untuk bank ini. Silakan edit bank dan isi field Saldo Awal terlebih dahulu.');
        }

        $histories = $this->getBankHistories($account_bank->id);
        $totalNetChange = $histories->sum(fn($h) => $h['jenis'] === 'masuk' ? $h['nominal'] : -$h['nominal']);
        $saldoSeharusnya = (float) $account_bank->saldo_awal + $totalNetChange;
        $selisih = (float) $account_bank->saldo - $saldoSeharusnya;

        if (abs($selisih) < 0.01) {
            return redirect()->back()->with('info', 'Saldo sudah sinkron. Tidak ada perubahan.');
        }

        DB::transaction(function () use ($account_bank, $saldoSeharusnya, $selisih) {
            $saldoLama = (float) $account_bank->saldo;

            $account_bank->update(['saldo' => $saldoSeharusnya]);

            SaldoAdjustment::create([
                'account_bank_id'  => $account_bank->id,
                'saldo_sebelumnya' => $saldoLama,
                'saldo_baru'       => $saldoSeharusnya,
                'selisih'          => abs($selisih),
                'jenis'            => $selisih > 0 ? 'kurang' : 'tambah',
                'deskripsi'        => 'Sinkronisasi otomatis saldo berdasarkan history transaksi (Selisih: Rp ' . number_format(abs($selisih), 0, ',', '.') . ')',
            ]);
        });

        $tanda = $selisih > 0 ? 'dikurangi' : 'ditambah';
        return redirect()->back()->with('success', 'Saldo berhasil disinkronkan. Saldo ' . $tanda . ' Rp ' . number_format(abs($selisih), 0, ',', '.') . '.');
    }

    public function destroy(AccountBank $account_bank)
    {
        $account_bank->delete();
        return redirect()->back()->with('success', 'Account Bank berhasil dihapus.');
    }
}
