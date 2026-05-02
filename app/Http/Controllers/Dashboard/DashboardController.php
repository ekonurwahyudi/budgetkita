<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Blok;
use App\Models\GajiKaryawan;
use App\Models\HutangPiutang;
use App\Models\Investasi;
use App\Models\Panen;
use App\Models\PembelianAset;
use App\Models\PembelianPersediaan;
use App\Models\Persediaan;
use App\Models\Siklus;
use App\Models\Tambak;
use App\Models\TambakAnggota;
use App\Models\TransaksiKeuangan;

class DashboardController extends Controller
{
    public function index()
    {
        $hasTambak = TambakAnggota::where('user_id', auth()->id())->exists();

        if (!$hasTambak) {
            return view('dashboard.index', compact('hasTambak'));
        }

        // Widget Row 1: Keuangan
        $pendapatanTransaksi = TransaksiKeuangan::where('jenis_transaksi', 'uang_masuk')
            ->where('status', 'selesai')->sum('nominal');
        $pendapatanPanen = Panen::where('status', 'selesai')->sum('total_penjualan');
        $pendapatanInvestasi = Investasi::where('status', 'selesai')->sum('nominal');
        $pendapatan = $pendapatanTransaksi + $pendapatanPanen + $pendapatanInvestasi;

        $pengeluaranTransaksi = TransaksiKeuangan::where('jenis_transaksi', 'uang_keluar')
            ->where('status', 'selesai')->sum('nominal');
        $pengeluaranGaji = GajiKaryawan::where('status', 'selesai')->sum('thp');
        $pengeluaranPersediaan = PembelianPersediaan::where('status', 'selesai')
            ->with('items')->get()->sum(fn($p) => $p->items->sum('harga_total'));
        $pengeluaranAset = PembelianAset::where('status', 'selesai')->sum('nominal_pembelian');
        $pengeluaran = $pengeluaranTransaksi + $pengeluaranGaji + $pengeluaranPersediaan + $pengeluaranAset;

        $labaRugi = $pendapatan - $pengeluaran;

        $totalHutang = HutangPiutang::where('jenis', 'hutang')
            ->where('sisa_pembayaran', '>', 0)->sum('sisa_pembayaran');
        $totalPiutang = HutangPiutang::where('jenis', 'piutang')
            ->where('sisa_pembayaran', '>', 0)->sum('sisa_pembayaran');
        $nilaiAset = PembelianAset::where('status', 'selesai')
            ->get()->sum(fn($a) => $a->nilai_buku_aset);

        // Widget Row 2: Operasional
        $totalTambak = Tambak::count();
        $totalBlok = Blok::count();
        $siklusAktif = Siklus::where('status', 'aktif')->count();
        $stokPersediaan = Persediaan::where('qty', '>', 0)->count();

        // Chart: Penjualan bulanan 12 bulan
        $penjualanBulanan = Panen::where('status', 'selesai')
            ->where('tgl_panen', '>=', now()->subMonths(12))
            ->selectRaw("TO_CHAR(tgl_panen, 'YYYY-MM') as bulan, SUM(total_penjualan) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        // Chart: Pendapatan vs Pengeluaran bulanan
        $pendapatanBulanan = TransaksiKeuangan::where('jenis_transaksi', 'uang_masuk')
            ->where('status', 'selesai')
            ->where('tgl_kwitansi', '>=', now()->subMonths(12))
            ->selectRaw("TO_CHAR(tgl_kwitansi, 'YYYY-MM') as bulan, SUM(nominal) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $pengeluaranBulanan = TransaksiKeuangan::where('jenis_transaksi', 'uang_keluar')
            ->where('status', 'selesai')
            ->where('tgl_kwitansi', '>=', now()->subMonths(12))
            ->selectRaw("TO_CHAR(tgl_kwitansi, 'YYYY-MM') as bulan, SUM(nominal) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        // Merge months for consistent chart
        $allMonths = collect();
        for ($i = 11; $i >= 0; $i--) {
            $allMonths->push(now()->subMonths($i)->format('Y-m'));
        }
        $pendapatanChart = $allMonths->mapWithKeys(fn($m) => [$m => (float)($pendapatanBulanan[$m] ?? 0)]);
        $pengeluaranChart = $allMonths->mapWithKeys(fn($m) => [$m => (float)($pengeluaranBulanan[$m] ?? 0)]);
        $penjualanChart = $allMonths->mapWithKeys(fn($m) => [$m => (float)($penjualanBulanan[$m] ?? 0)]);

        // Top 10 stok
        $topStok = Persediaan::with('itemPersediaan')
            ->where('qty', '>', 0)
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'hasTambak', 'pendapatan', 'pengeluaran', 'labaRugi',
            'totalHutang', 'totalPiutang', 'nilaiAset',
            'totalTambak', 'totalBlok', 'siklusAktif', 'stokPersediaan',
            'penjualanChart', 'pendapatanChart', 'pengeluaranChart',
            'allMonths', 'topStok'
        ));
    }
}
