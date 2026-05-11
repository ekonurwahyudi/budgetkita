<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AccountBank;
use App\Models\Blok;
use App\Models\GajiKaryawan;
use App\Models\HutangPiutang;
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
        $sikluses = Siklus::where('status', 'aktif')
            ->when($filterBlok, fn($q) => $q->where('blok_id', $filterBlok))
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
            ->orderByRaw('CASE WHEN minimum_stok IS NOT NULL AND minimum_stok > 0 AND qty <= minimum_stok THEN 0 ELSE 1 END')
            ->orderBy('qty')
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

    public function transactions()
    {
        $filterBlok = request('blok_id');
        $filterSiklus = request('siklus_id');
        $filterDateFrom = request('date_from');
        $filterDateTo = request('date_to');
        $jenis = request('jenis'); // pendapatan, pengeluaran, investasi

        $allTransactions = collect();

        if ($jenis === 'pendapatan' || $jenis === 'pengeluaran') {
            // Transaksi Keuangan
            $query = TransaksiKeuangan::with(['kategoriTransaksi', 'itemTransaksi', 'accountBank', 'siklus.blok'])
                ->where('status', 'selesai');

            if ($jenis === 'pendapatan') {
                $query->where('jenis_transaksi', 'uang_masuk');
            } else {
                $query->where('jenis_transaksi', 'uang_keluar');
            }

            if ($filterSiklus) {
                $query->where('siklus_id', $filterSiklus);
            } elseif ($filterBlok) {
                $query->whereHas('siklus', fn($q) => $q->where('blok_id', $filterBlok));
            }

            if ($filterDateFrom) $query->where('tgl_kwitansi', '>=', $filterDateFrom);
            if ($filterDateTo) $query->where('tgl_kwitansi', '<=', $filterDateTo);

            $query->get()->each(function($t) use ($allTransactions) {
                $allTransactions->push([
                    'no' => 0, 'nomor_transaksi' => $t->nomor_transaksi,
                    'tipe' => $t->jenis_transaksi === 'uang_masuk' ? 'Pendapatan' : 'Pengeluaran',
                    'sumber' => 'Transaksi',
                    'jenis' => $t->kategoriTransaksi?->deskripsi ?? '-',
                    'tanggal' => $t->tgl_kwitansi?->format('d/m/Y') ?? '-',
                    'aktivitas' => $t->aktivitas ?? '-',
                    'kategori' => $t->itemTransaksi?->deskripsi ?? '-',
                    'nominal' => $t->nominal,
                    'status' => ucfirst($t->status),
                    'bank' => $t->accountBank?->nama_bank ?? '-',
                ]);
            });
        }

        // Panen (Pendapatan)
        if ($jenis === 'pendapatan') {
            $panenQuery = Panen::with(['siklus.blok', 'accountBank'])
                ->where('status', 'selesai');

            if ($filterSiklus) {
                $panenQuery->where('siklus_id', $filterSiklus);
            } elseif ($filterBlok) {
                $panenQuery->whereHas('siklus', fn($q) => $q->where('blok_id', $filterBlok));
            }

            if ($filterDateFrom) $panenQuery->where('tgl_panen', '>=', $filterDateFrom);
            if ($filterDateTo) $panenQuery->where('tgl_panen', '<=', $filterDateTo);

            $panenQuery->get()->each(function($p) use ($allTransactions) {
                $allTransactions->push([
                    'no' => 0, 'nomor_transaksi' => 'PANEN-' . $p->id,
                    'tipe' => 'Pendapatan', 'sumber' => 'Panen',
                    'jenis' => 'Hasil Panen', 'tanggal' => $p->tgl_panen?->format('d/m/Y') ?? '-',
                    'aktivitas' => 'Panen ' . ($p->siklus?->nama_siklus ?? '-'),
                    'kategori' => 'Penjualan', 'nominal' => $p->total_penjualan,
                    'status' => 'Selesai', 'bank' => $p->accountBank?->nama_bank ?? '-',
                ]);
            });
        }

        if ($jenis === 'pengeluaran') {
            // Gaji
            $gajiQuery = GajiKaryawan::with(['user', 'accountBank'])
                ->where('status', 'selesai');
            if ($filterDateFrom) $gajiQuery->where('created_at', '>=', $filterDateFrom);
            if ($filterDateTo) $gajiQuery->where('created_at', '<=', $filterDateTo);
            $gajiQuery->get()->each(function($g) use ($allTransactions) {
                $allTransactions->push([
                    'no' => 0, 'nomor_transaksi' => $g->nomor_transaksi ?? '-',
                    'tipe' => 'Pengeluaran', 'sumber' => 'Gaji',
                    'jenis' => 'Gaji Karyawan', 'tanggal' => $g->created_at?->format('d/m/Y') ?? '-',
                    'aktivitas' => 'Gaji ' . ($g->user?->nama ?? '-'),
                    'kategori' => 'THP', 'nominal' => $g->thp,
                    'status' => 'Selesai', 'bank' => $g->accountBank?->nama_bank ?? '-',
                ]);
            });

            // Pembelian Aset
            $asetQuery = PembelianAset::with(['kategoriAset', 'accountBank'])
                ->where('status', 'selesai');
            if ($filterDateFrom) $asetQuery->where('tgl_pembelian', '>=', $filterDateFrom);
            if ($filterDateTo) $asetQuery->where('tgl_pembelian', '<=', $filterDateTo);
            $asetQuery->get()->each(function($a) use ($allTransactions) {
                $allTransactions->push([
                    'no' => 0, 'nomor_transaksi' => $a->nomor_transaksi ?? '-',
                    'tipe' => 'Pengeluaran', 'sumber' => 'Pembelian Aset',
                    'jenis' => $a->kategoriAset?->deskripsi ?? '-', 'tanggal' => $a->tgl_pembelian?->format('d/m/Y') ?? '-',
                    'aktivitas' => $a->nama_aset, 'kategori' => 'Aset',
                    'nominal' => $a->nominal_pembelian, 'status' => 'Selesai',
                    'bank' => $a->accountBank?->nama_bank ?? '-',
                ]);
            });

            // Pembelian Persediaan (Pakan)
            $persediaanQuery = PembelianPersediaan::with(['accountBank', 'items.itemPersediaan'])
                ->where('status', 'selesai');
            if ($filterDateFrom) $persediaanQuery->where('tgl_pembelian', '>=', $filterDateFrom);
            if ($filterDateTo) $persediaanQuery->where('tgl_pembelian', '<=', $filterDateTo);
            $persediaanQuery->get()->each(function($p) use ($allTransactions) {
                $total = $p->items->sum('harga_total');
                $items = $p->items->map(fn($i) => $i->itemPersediaan?->deskripsi ?? '-')->implode(', ');
                $allTransactions->push([
                    'no' => 0, 'nomor_transaksi' => $p->nomor_transaksi ?? '-',
                    'tipe' => 'Pengeluaran', 'sumber' => 'Pembelian Persediaan',
                    'jenis' => 'Pembelian Pakan/Item', 'tanggal' => $p->tgl_pembelian?->format('d/m/Y') ?? '-',
                    'aktivitas' => $items ?: 'Pembelian Item', 'kategori' => 'Persediaan',
                    'nominal' => $total, 'status' => 'Selesai',
                    'bank' => $p->accountBank?->nama_bank ?? '-',
                ]);
            });

            // Hutang (pembayaran hutang)
            $hutangQuery = HutangPiutang::with(['kategoriHutangPiutang', 'accountBank'])
                ->where('jenis', 'hutang')
                ->where('status', 'selesai')
                ->where('nominal_bayar', '>', 0);
            if ($filterDateFrom) $hutangQuery->where('updated_at', '>=', $filterDateFrom);
            if ($filterDateTo) $hutangQuery->where('updated_at', '<=', $filterDateTo);
            $hutangQuery->get()->each(function($h) use ($allTransactions) {
                $allTransactions->push([
                    'no' => 0, 'nomor_transaksi' => $h->nomor_transaksi ?? '-',
                    'tipe' => 'Pengeluaran', 'sumber' => 'Pembayaran Hutang',
                    'jenis' => $h->kategoriHutangPiutang?->deskripsi ?? '-',
                    'tanggal' => $h->updated_at?->format('d/m/Y') ?? '-',
                    'aktivitas' => $h->aktivitas ?? 'Pembayaran Hutang',
                    'kategori' => 'Hutang', 'nominal' => $h->nominal_bayar,
                    'status' => 'Selesai', 'bank' => $h->accountBank?->nama_bank ?? '-',
                ]);
            });
        }

        // Sort by date desc
        $allTransactions = $allTransactions->sortByDesc('tanggal')->values();
        $allTransactions->each(function(&$t, $i) { $t['no'] = $i + 1; });

        return response()->json($allTransactions);
    }
}
