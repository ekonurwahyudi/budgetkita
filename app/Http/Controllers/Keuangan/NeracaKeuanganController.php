<?php

namespace App\Http\Controllers\Keuangan;

use App\Exports\NeracaKeuanganExport;
use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\TransaksiKeuangan;
use App\Models\GajiKaryawan;
use App\Models\Investasi;
use App\Models\PembelianPersediaan;
use App\Models\PembelianAset;
use App\Models\HutangPiutang;
use App\Models\Panen;
use App\Models\Persediaan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NeracaKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);
        $data = $this->getNeracaData((int) $tahun);

        return view('keuangan.neraca-keuangan.index', array_merge($data, [
            'tahun' => $tahun,
        ]));
    }

    public function export(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);
        $data = $this->getNeracaData((int) $tahun);
        $filename = 'neraca-keuangan-' . $tahun . '.xlsx';
        return Excel::download(new NeracaKeuanganExport($data, (int) $tahun), $filename);
    }

    private function getNeracaData(int $tahun): array
    {
        // === ASET ===

        // Kas & Bank (saldo semua rekening aktif)
        $kasBank = AccountBank::where('status', 'aktif')->sum('saldo');

        // Piutang (sisa piutang yang belum dibayar)
        $piutang = HutangPiutang::where('jenis', 'piutang')
            ->where('status', 'selesai')
            ->whereYear('jatuh_tempo', '<=', $tahun)
            ->sum('sisa_pembayaran');

        // Persediaan (stok yang masih ada)
        $persediaan = Persediaan::where('qty', '>', 0)->sum('total_harga');

        // Investasi (yang sudah selesai sampai tahun ini)
        $investasi = Investasi::where('status', 'selesai')
            ->whereYear('created_at', '<=', $tahun)
            ->sum('nominal');

        // Aset Tetap
        $asetTetap = PembelianAset::where('status', 'selesai')
            ->whereYear('tgl_pembelian', '<=', $tahun)
            ->get();
        $asetTetapBruto = $asetTetap->sum('nominal_pembelian');
        $akumulasiDepresiasi = $asetTetap->sum(function ($a) use ($tahun) {
            $umur = min($tahun - $a->tgl_pembelian->year, $a->umur_manfaat);
            $umur = max(0, $umur);
            if ($a->metode_depresiasi === 'persen') {
                $depPerTahun = ($a->nominal_pembelian - ($a->nilai_residu ?? 0)) * ((float) $a->persen_depresiasi / 100);
            } elseif ($a->metode_depresiasi === 'tanpa') {
                $depPerTahun = 0;
            } else {
                $depPerTahun = $a->umur_manfaat > 0 ? ($a->nominal_pembelian - ($a->nilai_residu ?? 0)) / $a->umur_manfaat : 0;
            }
            return min($depPerTahun * $umur, max(0, (float) $a->nominal_pembelian - (float) ($a->nilai_residu ?? 0)));
        });
        $asetTetapNetto = max(0, $asetTetapBruto - $akumulasiDepresiasi);

        $totalAset = $kasBank + $piutang + $persediaan + $investasi + $asetTetapNetto;

        // === KEWAJIBAN ===

        // Hutang (sisa hutang yang belum dibayar)
        $hutang = HutangPiutang::where('jenis', 'hutang')
            ->where('status', 'selesai')
            ->whereYear('jatuh_tempo', '<=', $tahun)
            ->sum('sisa_pembayaran');

        $totalKewajiban = $hutang;

        // === EKUITAS ===

        // Pendapatan
        $pendapatanTransaksi = TransaksiKeuangan::where('jenis_transaksi', 'uang_masuk')
            ->where('status', 'selesai')
            ->whereYear('tgl_kwitansi', $tahun)
            ->sum('nominal');
        $pendapatanPanen = Panen::where('status', 'selesai')
            ->whereYear('tgl_panen', $tahun)
            ->sum('total_penjualan');
        $pendapatanInvestasi = Investasi::where('status', 'selesai')
            ->whereYear('created_at', $tahun)
            ->sum('nominal');
        $totalPendapatan = $pendapatanTransaksi + $pendapatanPanen + $pendapatanInvestasi;

        // Pengeluaran
        $pengeluaranTransaksi = TransaksiKeuangan::where('jenis_transaksi', 'uang_keluar')
            ->where('status', 'selesai')
            ->whereYear('tgl_kwitansi', $tahun)
            ->sum('nominal');
        $pengeluaranGaji = GajiKaryawan::where('status', 'selesai')
            ->whereYear('created_at', $tahun)
            ->sum('thp');
        $pengeluaranPersediaan = PembelianPersediaan::where('status', 'selesai')
            ->whereYear('tgl_pembelian', $tahun)
            ->withSum('items as total_nominal', 'harga_total')
            ->get()
            ->sum('total_nominal');
        $pengeluaranAset = PembelianAset::where('status', 'selesai')
            ->whereYear('tgl_pembelian', $tahun)
            ->sum('nominal_pembelian');
        $totalPengeluaran = $pengeluaranTransaksi + $pengeluaranGaji + $pengeluaranPersediaan + $pengeluaranAset;

        $labaRugi = $totalPendapatan - $totalPengeluaran;
        $ekuitas = $totalAset - $totalKewajiban;

        return compact(
            'tahun', 'kasBank', 'piutang', 'persediaan', 'investasi',
            'asetTetapBruto', 'akumulasiDepresiasi', 'asetTetapNetto', 'totalAset',
            'hutang', 'totalKewajiban',
            'pendapatanTransaksi', 'pendapatanPanen', 'pendapatanInvestasi', 'totalPendapatan',
            'pengeluaranTransaksi', 'pengeluaranGaji', 'pengeluaranPersediaan', 'pengeluaranAset', 'totalPengeluaran',
            'labaRugi', 'ekuitas'
        );
    }
}