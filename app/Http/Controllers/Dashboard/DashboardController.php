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
use App\Models\PemberianPakan;
use App\Models\Persediaan;
use App\Models\Siklus;
use App\Models\SharingRevenue;
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
        $filterYear = request('year', now()->year);

        // Dropdowns
        $bloks = Blok::orderBy('nama_blok')->get();
        $sikluses = Siklus::where('status', 'aktif')->orderBy('nama_siklus')->get();

        // Year boundaries
        $yearStart = $filterYear . '-01-01';
        $yearEnd = $filterYear . '-12-31';

        // Filtered query scopes
        $transaksiScope = TransaksiKeuangan::query()->where('status', 'selesai')
            ->whereBetween('tgl_kwitansi', [$yearStart, $yearEnd]);
        $panenScope = Panen::query()->where('status', 'selesai')
            ->whereBetween('tgl_panen', [$yearStart, $yearEnd]);
        $investasiScope = Investasi::query()->where('status', 'selesai')
            ->whereBetween('created_at', [$yearStart, $yearEnd . ' 23:59:59']);

        // === Stat Cards ===
        $totalInvestasi = $investasiScope->sum('nominal');

        $pendapatanTransaksi = (clone $transaksiScope)->where('jenis_transaksi', 'uang_masuk')->sum('nominal');
        $pendapatanPanen = $panenScope->sum('total_penjualan');
        $totalPendapatan = $pendapatanTransaksi + $pendapatanPanen;

        $pengeluaranTransaksi = (clone $transaksiScope)->where('jenis_transaksi', 'uang_keluar')->sum('nominal');
        $pengeluaranGaji = GajiKaryawan::where('status', 'selesai')
            ->whereBetween('created_at', [$yearStart, $yearEnd . ' 23:59:59'])->sum('thp');
        $pengeluaranPersediaan = PembelianPersediaan::where('status', 'selesai')
            ->whereBetween('tgl_pembelian', [$yearStart, $yearEnd])
            ->with('items')->get()->sum(fn($p) => $p->items->sum('harga_total'));
        $pengeluaranAset = PembelianAset::where('status', 'selesai')
            ->whereBetween('tgl_pembelian', [$yearStart, $yearEnd])->sum('nominal_pembelian');
        $totalPengeluaran = $pengeluaranTransaksi + $pengeluaranGaji + $pengeluaranPersediaan + $pengeluaranAset;

        $labaRugi = $totalPendapatan - $totalPengeluaran;

        $pengeluaranKategoriTransaksi = (clone $transaksiScope)
            ->with('kategoriTransaksi')
            ->where('jenis_transaksi', 'uang_keluar')
            ->get()
            ->groupBy(fn($transaksi) => $transaksi->kategoriTransaksi?->deskripsi ?? 'Tanpa Kategori')
            ->map(fn($items, $kategori) => [
                'kategori' => $kategori,
                'total' => (float) $items->sum('nominal'),
            ])
            ->values();

        $pengeluaranKategoriAset = PembelianAset::where('status', 'selesai')
            ->whereBetween('tgl_pembelian', [$yearStart, $yearEnd])
            ->with('kategoriAset')
            ->get()
            ->groupBy(fn($aset) => $aset->kategoriAset?->deskripsi ?? 'Aset')
            ->map(fn($items, $kategori) => [
                'kategori' => $kategori,
                'total' => (float) $items->sum('nominal_pembelian'),
            ])
            ->values();

        $pengeluaranKategoriPersediaan = PembelianPersediaan::where('status', 'selesai')
            ->whereBetween('tgl_pembelian', [$yearStart, $yearEnd])
            ->with('items.itemPersediaan.kategoriPersediaan')
            ->get()
            ->flatMap(function ($pembelian) {
                return $pembelian->items->map(function ($item) {
                    $namaKategori = $item->itemPersediaan?->kategoriPersediaan?->deskripsi ?? '';
                    $namaItem = $item->itemPersediaan?->deskripsi ?? '';
                    $teks = strtolower($namaKategori . ' ' . $namaItem);

                    return [
                        'kategori' => str_contains($teks, 'pakan') ? 'Pakan' : 'Bahan Kimia',
                        'total' => (float) $item->harga_total,
                    ];
                });
            })
            ->groupBy('kategori')
            ->map(fn($items, $kategori) => [
                'kategori' => $kategori,
                'total' => (float) $items->sum('total'),
            ])
            ->values();

        $pengeluaranKategoriGaji = collect([
            [
                'kategori' => 'Gaji',
                'total' => (float) GajiKaryawan::where('status', 'selesai')
                    ->whereBetween('created_at', [$yearStart, $yearEnd . ' 23:59:59'])->sum('thp'),
            ],
        ])->filter(fn($item) => $item['total'] > 0);

        $pengeluaranKategori = $pengeluaranKategoriTransaksi
            ->concat($pengeluaranKategoriAset)
            ->concat($pengeluaranKategoriPersediaan)
            ->concat($pengeluaranKategoriGaji)
            ->groupBy('kategori')
            ->map(fn($items, $kategori) => [
                'kategori' => $kategori,
                'total' => (float) $items->sum('total'),
            ])
            ->sortByDesc('total')
            ->values();

        // === Charts (selected year, same sources as stat cards) ===
        $chartStart = \Carbon\Carbon::createFromDate($filterYear, 1, 1)->startOfYear();

        $pendapatanTransaksiBulanan = TransaksiKeuangan::query()
            ->where('jenis_transaksi', 'uang_masuk')
            ->where('status', 'selesai')
            ->whereBetween('tgl_kwitansi', [$yearStart, $yearEnd])
            ->selectRaw("TO_CHAR(tgl_kwitansi, 'YYYY-MM') as bulan, SUM(nominal) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $pendapatanPanenBulanan = Panen::query()
            ->where('status', 'selesai')
            ->whereBetween('tgl_panen', [$yearStart, $yearEnd])
            ->selectRaw("TO_CHAR(tgl_panen, 'YYYY-MM') as bulan, SUM(total_penjualan) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $pengeluaranTransaksiBulanan = TransaksiKeuangan::query()
            ->where('jenis_transaksi', 'uang_keluar')
            ->where('status', 'selesai')
            ->whereBetween('tgl_kwitansi', [$yearStart, $yearEnd])
            ->selectRaw("TO_CHAR(tgl_kwitansi, 'YYYY-MM') as bulan, SUM(nominal) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $pengeluaranGajiBulanan = GajiKaryawan::query()
            ->where('status', 'selesai')
            ->whereBetween('created_at', [$yearStart, $yearEnd . ' 23:59:59'])
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as bulan, SUM(thp) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $pengeluaranPersediaanBulanan = PembelianPersediaan::query()
            ->join('pembelian_persediaan_items', 'pembelian_persediaans.id', '=', 'pembelian_persediaan_items.pembelian_persediaan_id')
            ->where('pembelian_persediaans.status', 'selesai')
            ->whereBetween('pembelian_persediaans.tgl_pembelian', [$yearStart, $yearEnd])
            ->selectRaw("TO_CHAR(pembelian_persediaans.tgl_pembelian, 'YYYY-MM') as bulan, SUM(pembelian_persediaan_items.harga_total) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $pengeluaranAsetBulanan = PembelianAset::query()
            ->where('status', 'selesai')
            ->whereBetween('tgl_pembelian', [$yearStart, $yearEnd])
            ->selectRaw("TO_CHAR(tgl_pembelian, 'YYYY-MM') as bulan, SUM(nominal_pembelian) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $allMonths = collect();
        for ($month = 1; $month <= 12; $month++) {
            $allMonths->push($chartStart->copy()->month($month)->format('Y-m'));
        }
        $pendapatanChart = $allMonths->mapWithKeys(fn($m) => [
            $m => (float)($pendapatanTransaksiBulanan[$m] ?? 0) + (float)($pendapatanPanenBulanan[$m] ?? 0),
        ]);
        $pengeluaranChart = $allMonths->mapWithKeys(fn($m) => [
            $m => (float)($pengeluaranTransaksiBulanan[$m] ?? 0)
                + (float)($pengeluaranGajiBulanan[$m] ?? 0)
                + (float)($pengeluaranPersediaanBulanan[$m] ?? 0)
                + (float)($pengeluaranAsetBulanan[$m] ?? 0),
        ]);

        // === Stok Persediaan ===
        $stokPersediaan = Persediaan::with('itemPersediaan.kategoriPersediaan')
            ->orderByRaw('CASE WHEN minimum_stok IS NOT NULL AND minimum_stok > 0 AND qty <= minimum_stok THEN 0 ELSE 1 END')
            ->orderBy('qty')
            ->limit(10)
            ->get();
        $stokPersediaanCount = Persediaan::where('qty', '>', 0)->count();
        $stokMinimumCount = Persediaan::whereNotNull('minimum_stok')
            ->where('minimum_stok', '>', 0)
            ->whereColumn('qty', '<=', 'minimum_stok')
            ->count();

        // === Account Banks ===
        $accountBanks = AccountBank::where('status', 'aktif')->orderBy('nama_bank')->get();
        $totalSaldoBank = $accountBanks->sum('saldo');

        // === Hutang & Piutang ===
        $hutangPiutangs = HutangPiutang::where('status', '!=', 'cancel')
            ->whereBetween('created_at', [$yearStart, $yearEnd . ' 23:59:59'])->get();
        $totalHutang = $hutangPiutangs->where('jenis', 'hutang')->sum(fn($item) => $item->total_bayar ?? $item->nominal ?? 0);
        $sisaHutang = $hutangPiutangs->where('jenis', 'hutang')->sum(fn($item) => $item->sisa_pembayaran ?? ($item->total_bayar ?? $item->nominal ?? 0));
        $totalPiutang = $hutangPiutangs->where('jenis', 'piutang')->sum('nominal');
        $sisaPiutang = $hutangPiutangs->where('jenis', 'piutang')->sum(fn($item) => $item->sisa_pembayaran ?? $item->nominal ?? 0);
        $hutangPiutangBelumLunas = $hutangPiutangs->filter(fn($item) => (float) ($item->sisa_pembayaran ?? $item->nominal ?? 0) > 0);
        $hutangPiutangTelat = $hutangPiutangBelumLunas->filter(fn($item) => $item->jatuh_tempo && $item->jatuh_tempo->isPast());
        $hutangPiutangDeadline = $hutangPiutangBelumLunas->filter(fn($item) => $item->jatuh_tempo && !$item->jatuh_tempo->isPast() && $item->jatuh_tempo->lte(now()->addDays(7)));
        $hutangPiutangTelatCount = $hutangPiutangTelat->count();
        $hutangPiutangDeadlineCount = $hutangPiutangDeadline->count();
        $hutangPiutangTelatNominal = $hutangPiutangTelat->sum(fn($item) => $item->sisa_pembayaran ?? $item->nominal ?? 0);
        $hutangPiutangDeadlineNominal = $hutangPiutangDeadline->sum(fn($item) => $item->sisa_pembayaran ?? $item->nominal ?? 0);

        // === Operasional ===
        $totalTambak = Tambak::count();
        $totalBlok = Blok::count();
        $kolamAktif = Kolam::where('status', 'aktif')->count();
        $siklusAktif = Siklus::where('status', 'aktif')->count();
        $nilaiAset = PembelianAset::where('status', 'selesai')
            ->whereBetween('tgl_pembelian', [$yearStart, $yearEnd])
            ->get()->sum(fn($a) => $a->nilai_buku_aset);

        // === Siklus Cards (same logic as SiklusController@show) ===
        $siklusAktifData = Siklus::with(['blok.tambak', 'panens', 'kolams'])
            ->whereYear('tgl_siklus', $filterYear)
            ->orderByRaw("CASE WHEN status = 'aktif' THEN 0 ELSE 1 END")
            ->orderByDesc('tgl_siklus')
            ->get()
            ->map(function ($siklus) {
                $transaksis = TransaksiKeuangan::where(function ($q) use ($siklus) {
                    $q->where('siklus_id', $siklus->id)
                      ->orWhere('blok_id', $siklus->blok_id);
                })->get();

                $uangMasukTransaksi = $transaksis->where('jenis_transaksi', 'uang_masuk')->sum('nominal');
                $uangKeluarTransaksi = $transaksis->where('jenis_transaksi', 'uang_keluar')->sum('nominal');
                $totalPanen = $siklus->panens->sum('total_penjualan');

                $semuaPemberian = PemberianPakan::with('itemPersediaan.kategoriPersediaan', 'itemPersediaan.persediaan')
                    ->where('siklus_id', $siklus->id)
                    ->get();

                $toBaseUnit = fn(float $qty, ?string $unit): float => match (strtolower(trim($unit ?? 'kg'))) {
                    'gram', 'ml' => $qty / 1000,
                    default => $qty,
                };

                $pemberianPakans = $semuaPemberian->filter(fn($p) =>
                    !$p->itemPersediaan?->kategoriPersediaan ||
                    stripos($p->itemPersediaan->kategoriPersediaan->deskripsi, 'pakan') !== false
                )->map(function($p) use ($toBaseUnit) {
                    $persediaan = $p->itemPersediaan?->persediaan;
                    $jumlahBase = $toBaseUnit((float) ($p->jumlah_pakan ?? 0), $p->unit ?? 'kg');
                    $p->biaya = $jumlahBase * ($persediaan?->harga_per_unit ?? 0);
                    return $p;
                });

                $pemberianKimia = $semuaPemberian->filter(fn($p) =>
                    $p->itemPersediaan?->kategoriPersediaan &&
                    stripos($p->itemPersediaan->kategoriPersediaan->deskripsi, 'pakan') === false
                )->map(function($p) use ($toBaseUnit) {
                    $persediaan = $p->itemPersediaan?->persediaan;
                    $jumlahBase = $toBaseUnit((float) ($p->jumlah_pakan ?? 0), $p->unit ?? 'kg');
                    $p->biaya = $jumlahBase * ($persediaan?->harga_per_unit ?? 0);
                    return $p;
                });

                $totalBiayaPakan = $pemberianPakans->sum('biaya');
                $totalBiayaKimia = $pemberianKimia->sum('biaya');

                $uangMasuk = $uangMasukTransaksi + $totalPanen;
                $uangKeluar = $uangKeluarTransaksi + $totalBiayaPakan + $totalBiayaKimia;
                $keuntunganKerugian = $uangMasuk - $uangKeluar;
                $sharingTerpakai = (float) SharingRevenue::where('siklus_id', $siklus->id)
                    ->where('status', '!=', 'cancel')
                    ->sum('persentase');

                return [
                    'id' => $siklus->id,
                    'blok_id' => $siklus->blok_id,
                    'nama_siklus' => $siklus->nama_siklus,
                    'blok_nama' => $siklus->blok?->nama_blok ?? '-',
                    'total_kolam' => $siklus->kolams->count(),
                    'tgl_siklus' => $siklus->tgl_siklus,
                    'status' => $siklus->status,
                    'uang_masuk' => $uangMasuk,
                    'uang_keluar' => $uangKeluar,
                    'keuntungan_kerugian' => $keuntunganKerugian,
                    'sharing_terpakai' => min(100, $sharingTerpakai),
                    'sharing_sisa' => max(0, 100 - $sharingTerpakai),
                ];
            });

        return view('dashboard.index', compact(
            'hasTambak',
            'bloks', 'sikluses', 'filterYear',
            'totalInvestasi', 'totalPendapatan', 'totalPengeluaran', 'labaRugi',
            'pengeluaranKategori',
            'pendapatanChart', 'pengeluaranChart', 'allMonths',
            'stokPersediaan', 'stokPersediaanCount', 'stokMinimumCount',
            'accountBanks', 'totalSaldoBank',
            'totalHutang', 'sisaHutang', 'totalPiutang', 'sisaPiutang',
            'hutangPiutangTelatCount', 'hutangPiutangDeadlineCount',
            'hutangPiutangTelatNominal', 'hutangPiutangDeadlineNominal',
            'totalTambak', 'totalBlok', 'kolamAktif', 'siklusAktif', 'nilaiAset',
            'siklusAktifData'
        ));
    }

    public function transactions()
    {
        $filterYear = request('year', now()->year);
        $yearStart = $filterYear . '-01-01';
        $yearEnd = $filterYear . '-12-31';
        $jenis = request('jenis'); // pendapatan, pengeluaran, investasi

        $allTransactions = collect();

        if ($jenis === 'pendapatan' || $jenis === 'pengeluaran') {
            // Transaksi Keuangan
            $query = TransaksiKeuangan::with(['kategoriTransaksi', 'itemTransaksi', 'accountBank', 'siklus.blok'])
                ->where('status', 'selesai')
                ->whereBetween('tgl_kwitansi', [$yearStart, $yearEnd]);

            if ($jenis === 'pendapatan') {
                $query->where('jenis_transaksi', 'uang_masuk');
            } else {
                $query->where('jenis_transaksi', 'uang_keluar');
            }

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
                ->where('status', 'selesai')
                ->whereBetween('tgl_panen', [$yearStart, $yearEnd]);

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
                ->where('status', 'selesai')
                ->whereBetween('created_at', [$yearStart, $yearEnd . ' 23:59:59']);
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
                ->where('status', 'selesai')
                ->whereBetween('tgl_pembelian', [$yearStart, $yearEnd]);
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
                ->where('status', 'selesai')
                ->whereBetween('tgl_pembelian', [$yearStart, $yearEnd]);
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
                ->where('nominal_bayar', '>', 0)
                ->whereBetween('updated_at', [$yearStart, $yearEnd . ' 23:59:59']);
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
