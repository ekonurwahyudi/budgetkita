<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\Blok;
use App\Models\GajiKaryawan;
use App\Models\Investasi;
use App\Models\Kolam;
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

        // Filters
        $filterBlok = request('blok_id');
        $filterSiklus = request('siklus_id');
        $filterDateFrom = request('date_from');
        $filterDateTo = request('date_to');

        // Dropdowns
        $bloks = Blok::orderBy('nama_blok')->get();
        $sikluses = Siklus::when($filterBlok, fn($q) => $q->where('blok_id', $filterBlok))
            ->orderBy('nama_siklus')->get();

        // Filtered query scopes
        $transaksiScope = TransaksiKeuangan::query()->where('status', 'selesai');
        $panenScope = Panen::query()->where('status', 'selesai');
        $investasiScope = Investasi::query()->where('status', 'selesai');

        if ($filterSiklus) {
            $transaksiScope->where('siklus_id', $filterSiklus);
            $panenScope->whereHas('siklus', fn($q) => $q->where('id', $filterSiklus));
        } elseif ($filterBlok) {
            $transaksiScope->whereHas('siklus', fn($q) => $q->where('blok_id', $filterBlok));
            $panenScope->whereHas('siklus.blok', fn($q) => $q->where('id', $filterBlok));
        }

        if ($filterDateFrom) {
            $transaksiScope->where('tgl_kwitansi', '>=', $filterDateFrom);
            $panenScope->where('tgl_panen', '>=', $filterDateFrom);
            $investasiScope->where('created_at', '>=', $filterDateFrom);
        }
        if ($filterDateTo) {
            $transaksiScope->where('tgl_kwitansi', '<=', $filterDateTo);
            $panenScope->where('tgl_panen', '<=', $filterDateTo);
            $investasiScope->where('created_at', '<=', $filterDateTo);
        }

        // === Stat Cards ===
        $totalInvestasi = $investasiScope->sum('nominal');

        $pendapatanTransaksi = (clone $transaksiScope)->where('jenis_transaksi', 'uang_masuk')->sum('nominal');
        $pendapatanPanen = $panenScope->sum('total_penjualan');
        $totalPendapatan = $pendapatanTransaksi + $pendapatanPanen;

        $pengeluaranTransaksi = (clone $transaksiScope)->where('jenis_transaksi', 'uang_keluar')->sum('nominal');
        $pengeluaranGaji = GajiKaryawan::where('status', 'selesai')->sum('thp');
        $pengeluaranPersediaan = PembelianPersediaan::where('status', 'selesai')
            ->with('items')->get()->sum(fn($p) => $p->items->sum('harga_total'));
        $pengeluaranAset = PembelianAset::where('status', 'selesai')->sum('nominal_pembelian');
        $totalPengeluaran = $pengeluaranTransaksi + $pengeluaranGaji + $pengeluaranPersediaan + $pengeluaranAset;

        $labaRugi = $totalPendapatan - $totalPengeluaran;

        // === Charts (12 months, unfiltered for trend) ===
        $penjualanBulanan = Panen::where('status', 'selesai')
            ->where('tgl_panen', '>=', now()->subMonths(12))
            ->selectRaw("TO_CHAR(tgl_panen, 'YYYY-MM') as bulan, SUM(total_penjualan) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

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

        $allMonths = collect();
        for ($i = 11; $i >= 0; $i--) {
            $allMonths->push(now()->subMonths($i)->format('Y-m'));
        }
        $pendapatanChart = $allMonths->mapWithKeys(fn($m) => [$m => (float)($pendapatanBulanan[$m] ?? 0)]);
        $pengeluaranChart = $allMonths->mapWithKeys(fn($m) => [$m => (float)($pengeluaranBulanan[$m] ?? 0)]);
        $penjualanChart = $allMonths->mapWithKeys(fn($m) => [$m => (float)($penjualanBulanan[$m] ?? 0)]);

        // === Stok Persediaan ===
        $stokPersediaan = Persediaan::with('itemPersediaan.kategoriPersediaan')
            ->where('qty', '>', 0)
            ->orderByDesc('qty')
            ->limit(10)
            ->get();
        $stokPersediaanCount = Persediaan::where('qty', '>', 0)->count();

        // === Account Banks ===
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();

        // === Operasional ===
        $totalTambak = Tambak::count();
        $totalBlok = Blok::count();
        $kolamAktif = Kolam::where('status', 'aktif')->count();
        $siklusAktif = Siklus::where('status', 'aktif')->count();
        $nilaiAset = PembelianAset::where('status', 'selesai')->get()->sum(fn($a) => $a->nilai_buku_aset);

        return view('dashboard.index', compact(
            'hasTambak',
            'bloks', 'sikluses',
            'filterBlok', 'filterSiklus', 'filterDateFrom', 'filterDateTo',
            'totalInvestasi', 'totalPendapatan', 'totalPengeluaran', 'labaRugi',
            'penjualanChart', 'pendapatanChart', 'pengeluaranChart', 'allMonths',
            'stokPersediaan', 'stokPersediaanCount',
            'accountBanks',
            'totalTambak', 'totalBlok', 'kolamAktif', 'siklusAktif', 'nilaiAset'
        ));
    }
}
