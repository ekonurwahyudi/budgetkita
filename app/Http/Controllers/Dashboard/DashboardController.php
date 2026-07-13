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
use App\Models\PenjualanAset;
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

        // Filters
        $filterYear = request('year', 'all');
        $isAllYears = $filterYear === 'all';

        if (!$hasTambak) {
            return view('dashboard.index', compact('hasTambak', 'filterYear'));
        }

        $activeTambakId = $this->activeTambakId();
        $filterActiveTambak = fn ($query) => $activeTambakId
            ? $query->where('tambak_id', $activeTambakId)
            : $query;
        $filterActiveTambakViaBlok = fn ($query) => $activeTambakId
            ? $query->whereHas('blok', fn ($q) => $q->where('tambak_id', $activeTambakId))
            : $query;
        $filterActiveTambakViaSiklus = fn ($query) => $activeTambakId
            ? $query->whereHas('siklus.blok', fn ($q) => $q->where('tambak_id', $activeTambakId))
            : $query;

        // Dropdowns
        $bloks = $filterActiveTambak(Blok::query())->orderBy('nama_blok')->get();
        $sikluses = $filterActiveTambakViaBlok(Siklus::where('status', 'aktif'))->orderBy('nama_siklus')->get();

        // Year boundaries
        $yearStart = $isAllYears ? null : $filterYear . '-01-01';
        $yearEnd = $isAllYears ? null : $filterYear . '-12-31';
        $filterDate = function ($query, string $column) use ($isAllYears, $yearStart, $yearEnd) {
            return $isAllYears ? $query : $query->whereBetween($column, [$yearStart, $yearEnd]);
        };
        $filterDateTime = function ($query, string $column) use ($isAllYears, $yearStart, $yearEnd) {
            return $isAllYears ? $query : $query->whereBetween($column, [$yearStart, $yearEnd . ' 23:59:59']);
        };

        // Filtered query scopes
        $transaksiScope = $filterActiveTambak($filterDate(TransaksiKeuangan::query()->where('status', 'selesai'), 'tgl_kwitansi'));
        $panenScope = $filterActiveTambakViaSiklus($filterDate(Panen::query()->where('status', 'selesai'), 'tgl_panen'));
        $investasiScope = $filterDateTime(Investasi::query()->where('status', 'selesai'), 'created_at');

        // === Stat Cards ===
        $totalInvestasi = $investasiScope->sum('nominal');

        $pendapatanTransaksi = (clone $transaksiScope)->where('jenis_transaksi', 'uang_masuk')->sum('nominal');
        $pendapatanPanen = $panenScope->sum('total_penjualan');
        $pendapatanPenjualanAset = $filterActiveTambak($filterDate(PenjualanAset::query(), 'tgl_penjualan'))->sum('nominal_dibayar');
        $totalPendapatan = $pendapatanTransaksi + $pendapatanPanen + $pendapatanPenjualanAset;

        $pengeluaranTransaksi = (clone $transaksiScope)->where('jenis_transaksi', 'uang_keluar')->sum('nominal');
        $pengeluaranGaji = $filterDateTime(GajiKaryawan::where('status', 'selesai'), 'created_at')->sum('thp');
        $pengeluaranPersediaan = $filterDate(PembelianPersediaan::where('status', 'selesai'), 'tgl_pembelian')
            ->sum('nominal_dibayar');
        $pengeluaranAset = $filterDate(PembelianAset::where('status', 'selesai'), 'tgl_pembelian')->sum('nominal_dibayar');
        $pengeluaranPiutang = $filterDateTime(HutangPiutang::where('jenis', 'piutang')->where('status', 'selesai')->doesntHave('penjualanAset'), 'created_at')->sum('nominal');
        $totalPengeluaran = $pengeluaranTransaksi + $pengeluaranGaji + $pengeluaranPersediaan + $pengeluaranAset + $pengeluaranPiutang;

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

        $pengeluaranKategoriAset = $filterDate(PembelianAset::where('status', 'selesai'), 'tgl_pembelian')
            ->with('kategoriAset')
            ->get()
            ->groupBy(fn($aset) => $aset->kategoriAset?->deskripsi ?? 'Aset')
            ->map(fn($items, $kategori) => [
                'kategori' => $kategori,
                'total' => (float) $items->sum('nominal_dibayar'),
            ])
            ->values();

        $pengeluaranKategoriPersediaan = $filterDate(PembelianPersediaan::where('status', 'selesai'), 'tgl_pembelian')
            ->get()
            ->map(fn($pembelian) => [
                'kategori' => 'Persediaan',
                'total' => (float) $pembelian->nominal_dibayar,
            ])
            ->groupBy('kategori')
            ->map(fn($items, $kategori) => [
                'kategori' => $kategori,
                'total' => (float) $items->sum('total'),
            ])
            ->values();

        $pengeluaranKategoriGaji = collect([
            [
                'kategori' => 'Gaji Karyawan',
                'total' => (float) $filterDateTime(GajiKaryawan::where('status', 'selesai'), 'created_at')->sum('thp'),
            ],
        ])->filter(fn($item) => $item['total'] > 0);

        $pengeluaranKategoriPiutang = $filterDateTime(HutangPiutang::with('kategoriHutangPiutang')
            ->where('jenis', 'piutang')
            ->where('status', 'selesai'), 'created_at')
            ->get()
            ->groupBy(fn($piutang) => $piutang->kategoriHutangPiutang?->deskripsi ?? 'Piutang')
            ->map(fn($items, $kategori) => [
                'kategori' => $kategori,
                'total' => (float) $items->sum('nominal'),
            ])
            ->values();

        $pengeluaranKategori = $pengeluaranKategoriTransaksi
            ->concat($pengeluaranKategoriAset)
            ->concat($pengeluaranKategoriPersediaan)
            ->concat($pengeluaranKategoriGaji)
            ->concat($pengeluaranKategoriPiutang)
            ->groupBy('kategori')
            ->map(fn($items, $kategori) => [
                'kategori' => $kategori,
                'total' => (float) $items->sum('total'),
            ])
            ->sortByDesc('total')
            ->values();

        // === Charts (selected year/all years, same sources as stat cards) ===
        $chartBucket = $isAllYears ? 'YYYY' : 'YYYY-MM';

        $pendapatanTransaksiBulanan = $filterActiveTambak($filterDate(TransaksiKeuangan::query()
            ->where('jenis_transaksi', 'uang_masuk')
            ->where('status', 'selesai'), 'tgl_kwitansi'))
            ->selectRaw("TO_CHAR(tgl_kwitansi, '{$chartBucket}') as bulan, SUM(nominal) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $pendapatanPanenBulanan = $filterActiveTambakViaSiklus($filterDate(Panen::query()
            ->where('status', 'selesai'), 'tgl_panen'))
            ->selectRaw("TO_CHAR(tgl_panen, '{$chartBucket}') as bulan, SUM(total_penjualan) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $pengeluaranTransaksiBulanan = $filterActiveTambak($filterDate(TransaksiKeuangan::query()
            ->where('jenis_transaksi', 'uang_keluar')
            ->where('status', 'selesai'), 'tgl_kwitansi'))
            ->selectRaw("TO_CHAR(tgl_kwitansi, '{$chartBucket}') as bulan, SUM(nominal) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $pengeluaranGajiBulanan = $filterDateTime(GajiKaryawan::query()
            ->where('status', 'selesai'), 'created_at')
            ->selectRaw("TO_CHAR(created_at, '{$chartBucket}') as bulan, SUM(thp) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $pengeluaranPersediaanBulanan = $filterDate(PembelianPersediaan::query()
            ->where('pembelian_persediaans.status', 'selesai'), 'pembelian_persediaans.tgl_pembelian')
            ->selectRaw("TO_CHAR(pembelian_persediaans.tgl_pembelian, '{$chartBucket}') as bulan, SUM(pembelian_persediaans.nominal_dibayar) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $pengeluaranAsetBulanan = $filterDate(PembelianAset::query()
            ->where('status', 'selesai'), 'tgl_pembelian')
            ->selectRaw("TO_CHAR(tgl_pembelian, '{$chartBucket}') as bulan, SUM(nominal_dibayar) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        $pengeluaranPiutangBulanan = $filterDateTime(HutangPiutang::query()
            ->where('jenis', 'piutang')
            ->where('status', 'selesai'), 'created_at')
            ->selectRaw("TO_CHAR(created_at, '{$chartBucket}') as bulan, SUM(nominal) as total")
            ->groupBy('bulan')->orderBy('bulan')
            ->pluck('total', 'bulan');

        if ($isAllYears) {
            $allMonths = collect()
                ->merge($pendapatanTransaksiBulanan->keys())
                ->merge($pendapatanPanenBulanan->keys())
                ->merge($pengeluaranTransaksiBulanan->keys())
                ->merge($pengeluaranGajiBulanan->keys())
                ->merge($pengeluaranPersediaanBulanan->keys())
                ->merge($pengeluaranAsetBulanan->keys())
                ->merge($pengeluaranPiutangBulanan->keys())
                ->unique()
                ->sort()
                ->values();

            if ($allMonths->isEmpty()) {
                $allMonths->push((string) now()->year);
            }
        } else {
            $chartStart = \Carbon\Carbon::createFromDate((int) $filterYear, 1, 1)->startOfYear();
            $allMonths = collect();
            for ($month = 1; $month <= 12; $month++) {
                $allMonths->push($chartStart->copy()->month($month)->format('Y-m'));
            }
        }
        $pendapatanChart = $allMonths->mapWithKeys(fn($m) => [
            $m => (float)($pendapatanTransaksiBulanan[$m] ?? 0) + (float)($pendapatanPanenBulanan[$m] ?? 0),
        ]);
        $pengeluaranChart = $allMonths->mapWithKeys(fn($m) => [
            $m => (float)($pengeluaranTransaksiBulanan[$m] ?? 0)
                + (float)($pengeluaranGajiBulanan[$m] ?? 0)
                + (float)($pengeluaranPersediaanBulanan[$m] ?? 0)
                + (float)($pengeluaranAsetBulanan[$m] ?? 0)
                + (float)($pengeluaranPiutangBulanan[$m] ?? 0),
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
        $hutangPiutangs = $filterDateTime(HutangPiutang::where('status', '!=', 'cancel'), 'created_at')
            ->latest('created_at')
            ->get();
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
        $totalTambak = $activeTambakId ? Tambak::whereKey($activeTambakId)->count() : Tambak::count();
        $totalBlok = $filterActiveTambak(Blok::query())->count();
        $kolamAktif = $filterActiveTambakViaSiklus(Kolam::where('status', 'aktif'))->count();
        $siklusAktif = $filterActiveTambakViaBlok(Siklus::where('status', 'aktif'))->count();
        $nilaiAset = $filterDate(PembelianAset::where('status', 'selesai'), 'tgl_pembelian')
            ->get()->sum(fn($a) => $a->nilai_buku_aset);

        // === Siklus Cards ===
        $siklusAktifData = $filterActiveTambakViaBlok(Siklus::with(['blok', 'panens', 'kolams']))
            ->when($filterYear !== 'all', fn($q) => $q->whereYear('tgl_siklus', (int) $filterYear))
            ->orderByRaw("CASE WHEN status = 'aktif' THEN 0 ELSE 1 END")
            ->orderByDesc('tgl_siklus')
            ->get()
            ->map(function ($siklus) {
                $blok = $siklus->blok;

                $transaksis = TransaksiKeuangan::where('siklus_id', $siklus->id)->get();

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
                    'blok_nama' => $blok?->nama_blok ?? '-',
                    'total_kolam' => $siklus->kolams->count(),
                    'tgl_siklus' => $siklus->tgl_siklus,
                    'status' => $siklus->status ?: ($blok?->status_blok ?? ''),
                    'uang_masuk' => $uangMasuk,
                    'uang_keluar' => $uangKeluar,
                    'keuntungan_kerugian' => $keuntunganKerugian,
                    'sharing_terpakai' => min(100, $sharingTerpakai),
                    'sharing_sisa' => max(0, 100 - $sharingTerpakai),
                ];
            })
            ->filter()
            ->values();

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
        $activeTambakId = $this->activeTambakId();
        $filterActiveTambak = fn ($query) => $activeTambakId
            ? $query->where('tambak_id', $activeTambakId)
            : $query;
        $filterActiveTambakViaSiklus = fn ($query) => $activeTambakId
            ? $query->whereHas('siklus.blok', fn ($q) => $q->where('tambak_id', $activeTambakId))
            : $query;

        $filterYear = request('year', 'all');
        $isAllYears = $filterYear === 'all';
        $yearStart = $isAllYears ? null : $filterYear . '-01-01';
        $yearEnd = $isAllYears ? null : $filterYear . '-12-31';
        $filterDate = function ($query, string $column) use ($isAllYears, $yearStart, $yearEnd) {
            return $isAllYears ? $query : $query->whereBetween($column, [$yearStart, $yearEnd]);
        };
        $filterDateTime = function ($query, string $column) use ($isAllYears, $yearStart, $yearEnd) {
            return $isAllYears ? $query : $query->whereBetween($column, [$yearStart, $yearEnd . ' 23:59:59']);
        };
        $jenis = request('jenis'); // pendapatan, pengeluaran, investasi

        $allTransactions = collect();

        if ($jenis === 'pendapatan' || $jenis === 'pengeluaran') {
            // Transaksi Keuangan
            $query = $filterActiveTambak($filterDate(TransaksiKeuangan::with(['kategoriTransaksi', 'itemTransaksi', 'accountBank', 'siklus.blok'])
                ->where('status', 'selesai'), 'tgl_kwitansi'));

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
            $panenQuery = $filterActiveTambakViaSiklus($filterDate(Panen::with(['siklus.blok', 'accountBank'])
                ->where('status', 'selesai'), 'tgl_panen'));

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
            $gajiQuery = $filterDateTime(GajiKaryawan::with(['user', 'accountBank'])
                ->where('status', 'selesai'), 'created_at');
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
            $asetQuery = $filterDate(PembelianAset::with(['kategoriAset', 'accountBank'])
                ->where('status', 'selesai'), 'tgl_pembelian');
            $asetQuery->get()->each(function($a) use ($allTransactions) {
                $allTransactions->push([
                    'no' => 0, 'nomor_transaksi' => $a->nomor_transaksi ?? '-',
                    'tipe' => 'Pengeluaran', 'sumber' => 'Pembelian Aset',
                    'jenis' => $a->kategoriAset?->deskripsi ?? '-', 'tanggal' => $a->tgl_pembelian?->format('d/m/Y') ?? '-',
                    'aktivitas' => $a->nama_aset, 'kategori' => 'Aset',
                    'nominal' => $a->nominal_dibayar, 'status' => 'Selesai',
                    'bank' => $a->accountBank?->nama_bank ?? '-',
                ]);
            });

            // Pembelian Persediaan (Pakan)
            $persediaanQuery = $filterDate(PembelianPersediaan::with(['accountBank', 'items.itemPersediaan'])
                ->where('status', 'selesai'), 'tgl_pembelian');
            $persediaanQuery->get()->each(function($p) use ($allTransactions) {
                $items = $p->items->map(fn($i) => $i->itemPersediaan?->deskripsi ?? '-')->implode(', ');
                $allTransactions->push([
                    'no' => 0, 'nomor_transaksi' => $p->nomor_transaksi ?? '-',
                    'tipe' => 'Pengeluaran', 'sumber' => 'Pembelian Persediaan',
                    'jenis' => 'Pembelian Pakan/Item', 'tanggal' => $p->tgl_pembelian?->format('d/m/Y') ?? '-',
                    'aktivitas' => $items ?: 'Pembelian Item', 'kategori' => 'Persediaan',
                    'nominal' => $p->nominal_dibayar, 'status' => 'Selesai',
                    'bank' => $p->accountBank?->nama_bank ?? '-',
                ]);
            });

            // Hutang (pembayaran hutang)
            $hutangQuery = $filterDateTime(HutangPiutang::with(['kategoriHutangPiutang', 'accountBank'])
                ->where('jenis', 'hutang')
                ->where('status', 'selesai')
                ->where('nominal_bayar', '>', 0), 'updated_at');
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

            // Piutang (uang keluar)
            $piutangQuery = $filterDateTime(HutangPiutang::with(['kategoriHutangPiutang', 'accountBank'])
                ->where('jenis', 'piutang')
                ->where('status', 'selesai'), 'created_at');
            $piutangQuery->get()->each(function($p) use ($allTransactions) {
                $allTransactions->push([
                    'no' => 0, 'nomor_transaksi' => $p->nomor_transaksi ?? '-',
                    'tipe' => 'Pengeluaran', 'sumber' => 'Piutang',
                    'jenis' => $p->kategoriHutangPiutang?->deskripsi ?? '-',
                    'tanggal' => $p->created_at?->format('d/m/Y') ?? '-',
                    'aktivitas' => $p->aktivitas ?? 'Piutang',
                    'kategori' => 'Piutang', 'nominal' => $p->nominal,
                    'status' => 'Selesai', 'bank' => $p->accountBank?->nama_bank ?? '-',
                ]);
            });
        }

        // Sort by date desc
        $allTransactions = $allTransactions->sortByDesc('tanggal')->values();
        $allTransactions->each(function(&$t, $i) { $t['no'] = $i + 1; });

        return response()->json($allTransactions);
    }

    private function activeTambakId(): ?string
    {
        $tambakIds = auth()->user()->tambaks()->pluck('tambaks.id');

        if ($tambakIds->isEmpty()) {
            return null;
        }

        $activeTambakId = session('active_tambak_id');

        if (!$activeTambakId || !$tambakIds->contains($activeTambakId)) {
            $activeTambakId = $tambakIds->first();
            session(['active_tambak_id' => $activeTambakId]);
        }

        return $activeTambakId;
    }
}
