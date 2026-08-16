<?php

namespace App\Http\Controllers\Keuangan;

use App\Exports\LaporanKeuanganExport;
use App\Http\Controllers\Controller;
use App\Models\TransaksiKeuangan;
use App\Models\GajiKaryawan;
use App\Models\Investasi;
use App\Models\PembelianPersediaan;
use App\Models\PembelianPersediaanReturn;
use App\Models\PembelianAset;
use App\Models\PenjualanAset;
use App\Models\HutangPiutang;
use App\Models\Panen;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getQueryData($request);

        return view('keuangan.laporan-keuangan.index', array_merge($data, [
            'jenis' => $request->get('jenis', 'semua'),
            'status' => $request->get('status', ''),
            'tgl_dari' => $request->get('tgl_dari', ''),
            'tgl_sampai' => $request->get('tgl_sampai', ''),
            'search' => $request->get('search', ''),
        ]));
    }

    public function export(Request $request)
    {
        $data = $this->getQueryData($request);
        $filename = 'laporan-keuangan-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new LaporanKeuanganExport($data['rows']), $filename);
    }

    private function getQueryData(Request $request)
    {
        $tambakIds = auth()->user()->tambaks()->pluck('tambaks.id');

        $jenis = $request->get('jenis', 'semua');
        $status = $request->get('status', '');
        $tgl_dari = $request->get('tgl_dari', '');
        $tgl_sampai = $request->get('tgl_sampai', '');
        $search = $request->get('search', '');

        $rows = collect();

        // Transaksi Keuangan
        if ($jenis === 'semua' || $jenis === 'uang_masuk' || $jenis === 'uang_keluar') {
            $q = TransaksiKeuangan::with(['kategoriTransaksi', 'accountBank', 'blok', 'siklus'])
                ->whereIn('tambak_id', $tambakIds);
            if ($status) $q->where('status', $status);
            if ($jenis === 'uang_masuk') $q->where('jenis_transaksi', 'uang_masuk');
            if ($jenis === 'uang_keluar') $q->where('jenis_transaksi', 'uang_keluar');
            if ($tgl_dari) $q->whereDate('tgl_kwitansi', '>=', $tgl_dari);
            if ($tgl_sampai) $q->whereDate('tgl_kwitansi', '<=', $tgl_sampai);
            if ($search) $q->where('aktivitas', 'like', "%{$search}%");
            foreach ($q->latest('tgl_kwitansi')->get() as $t) {
                $rows->push([
                    'type' => 'Transaksi Keuangan',
                    'nomor' => $t->nomor_transaksi,
                    'jenis' => $t->jenis_transaksi === 'uang_masuk' ? 'Uang Masuk' : ($t->jenis_transaksi === 'uang_keluar' ? 'Uang Keluar' : 'Cash Card'),
                    'jenis_raw' => $t->jenis_transaksi,
                    'tanggal' => $t->tgl_kwitansi,
                    'sort_at' => $t->updated_at ?? $t->created_at ?? $t->tgl_kwitansi,
                    'aktivitas' => $t->aktivitas,
                    'blok' => $t->blok?->nama_blok,
                    'siklus' => $t->siklus?->nama_siklus,
                    'kategori' => $t->kategoriTransaksi?->deskripsi,
                    'nominal' => (float) $t->nominal,
                    'status' => $t->status,
                    'bank' => $t->accountBank?->kode_account ?? '-',
                    'route' => route('transaksi.show', $t),
                ]);
            }
        }

        // Gaji Karyawan
        if ($jenis === 'semua' || $jenis === 'uang_keluar') {
            $q = GajiKaryawan::with(['user', 'accountBank']);
            if ($status) $q->where('status', $status);
            if ($tgl_dari) $q->whereDate('created_at', '>=', $tgl_dari);
            if ($tgl_sampai) $q->whereDate('created_at', '<=', $tgl_sampai);
            if ($search) $q->whereHas('user', fn($qq) => $qq->where('name', 'like', "%{$search}%"));
            foreach ($q->latest('created_at')->get() as $g) {
                $rows->push([
                    'type' => 'Gaji Karyawan',
                    'nomor' => $g->nomor_transaksi,
                    'jenis' => 'Uang Keluar',
                    'jenis_raw' => 'uang_keluar',
                    'tanggal' => $g->created_at,
                    'sort_at' => $g->updated_at ?? $g->created_at,
                    'aktivitas' => 'Gaji ' . ($g->user?->name ?? '-'),
                    'blok' => null,
                    'siklus' => null,
                    'kategori' => 'Gaji Karyawan',
                    'nominal' => (float) $g->thp,
                    'status' => $g->status,
                    'bank' => $g->accountBank?->kode_account ?? '-',
                    'route' => route('gaji.show', $g),
                ]);
            }
        }

        // Investasi
        if ($jenis === 'semua' || $jenis === 'uang_masuk') {
            $q = Investasi::with(['kategoriInvestasi', 'accountBank']);
            if ($status) $q->where('status', $status);
            if ($tgl_dari) $q->whereDate('created_at', '>=', $tgl_dari);
            if ($tgl_sampai) $q->whereDate('created_at', '<=', $tgl_sampai);
            if ($search) $q->where('deskripsi', 'like', "%{$search}%");
            foreach ($q->latest('created_at')->get() as $i) {
                $rows->push([
                    'type' => 'Investasi',
                    'nomor' => $i->nomor_transaksi,
                    'jenis' => 'Uang Masuk',
                    'jenis_raw' => 'uang_masuk',
                    'tanggal' => $i->created_at,
                    'sort_at' => $i->updated_at ?? $i->created_at,
                    'aktivitas' => $i->deskripsi,
                    'blok' => null,
                    'siklus' => null,
                    'kategori' => $i->kategoriInvestasi?->deskripsi ?? 'Investasi',
                    'nominal' => (float) $i->nominal,
                    'status' => $i->status,
                    'bank' => $i->accountBank?->kode_account ?? '-',
                    'route' => route('investasi.show', $i),
                ]);
            }
        }

        // Pembelian Persediaan
        if ($jenis === 'semua' || $jenis === 'uang_keluar') {
            $q = PembelianPersediaan::with(['accountBank', 'blok', 'siklus'])->where('nominal_dibayar', '>', 0);
            if ($status) $q->where('status', $status);
            if ($tgl_dari) $q->whereDate('tgl_pembelian', '>=', $tgl_dari);
            if ($tgl_sampai) $q->whereDate('tgl_pembelian', '<=', $tgl_sampai);
            foreach ($q->latest('created_at')->get() as $p) {
                $rows->push([
                    'type' => 'Pembelian Persediaan',
                    'nomor' => $p->nomor_transaksi,
                    'jenis' => 'Uang Keluar',
                    'jenis_raw' => 'uang_keluar',
                    'tanggal' => $p->tgl_pembelian,
                    'sort_at' => $p->updated_at ?? $p->created_at ?? $p->tgl_pembelian,
                    'aktivitas' => $p->catatan ?? 'Pembelian Persediaan',
                    'blok' => $p->blok?->nama_blok,
                    'siklus' => $p->siklus?->nama_siklus,
                    'kategori' => 'Persediaan',
                    'nominal' => (float) $p->nominal_dibayar,
                    'status' => $p->status,
                    'bank' => $p->accountBank?->kode_account ?? '-',
                    'route' => route('pembelian-persediaan.show', $p),
                ]);
            }
        }

        // Pengembalian Persediaan (refund dari pembelian lunas/sebagian)
        if ($jenis === 'semua' || $jenis === 'uang_masuk') {
            $q = PembelianPersediaanReturn::with(['pembelianPersediaan.accountBank', 'pembelianPersediaan.blok', 'pembelianPersediaan.siklus', 'item.itemPersediaan'])
                ->where('nominal_refund', '>', 0);
            if ($status) $q->whereHas('pembelianPersediaan', fn($qq) => $qq->where('status', $status));
            if ($tgl_dari) $q->whereDate('created_at', '>=', $tgl_dari);
            if ($tgl_sampai) $q->whereDate('created_at', '<=', $tgl_sampai);
            if ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('catatan', 'like', "%{$search}%")
                        ->orWhereHas('pembelianPersediaan', fn($p) => $p->where('nomor_transaksi', 'like', "%{$search}%"))
                        ->orWhereHas('item.itemPersediaan', fn($item) => $item->where('deskripsi', 'like', "%{$search}%"));
                });
            }
            foreach ($q->latest('created_at')->get() as $return) {
                $pembelian = $return->pembelianPersediaan;
                $rows->push([
                    'type' => 'Pengembalian Persediaan',
                    'nomor' => $pembelian?->nomor_transaksi,
                    'jenis' => 'Uang Masuk',
                    'jenis_raw' => 'uang_masuk',
                    'tanggal' => $return->created_at,
                    'sort_at' => $return->updated_at ?? $return->created_at,
                    'aktivitas' => 'Pengembalian ' . ($return->item?->itemPersediaan?->deskripsi ?? 'Persediaan'),
                    'blok' => $pembelian?->blok?->nama_blok,
                    'siklus' => $pembelian?->siklus?->nama_siklus,
                    'kategori' => 'Pengembalian Persediaan',
                    'nominal' => (float) $return->nominal_refund,
                    'status' => $pembelian?->status ?? 'selesai',
                    'bank' => $pembelian?->accountBank?->kode_account ?? '-',
                    'route' => $pembelian ? route('pembelian-persediaan.show', $pembelian) : '#',
                ]);
            }
        }

        // Pembelian Aset
        if ($jenis === 'semua' || $jenis === 'uang_keluar') {
            $q = PembelianAset::with(['kategoriAset', 'accountBank'])->where('nominal_dibayar', '>', 0);
            if ($status) $q->where('status', $status);
            if ($tgl_dari) $q->whereDate('tgl_pembelian', '>=', $tgl_dari);
            if ($tgl_sampai) $q->whereDate('tgl_pembelian', '<=', $tgl_sampai);
            if ($search) $q->where('nama_aset', 'like', "%{$search}%");
            foreach ($q->latest('created_at')->get() as $a) {
                $rows->push([
                    'type' => 'Pembelian Aset',
                    'nomor' => $a->nomor_transaksi,
                    'jenis' => 'Uang Keluar',
                    'jenis_raw' => 'uang_keluar',
                    'tanggal' => $a->tgl_pembelian,
                    'sort_at' => $a->updated_at ?? $a->created_at ?? $a->tgl_pembelian,
                    'aktivitas' => $a->nama_aset,
                    'blok' => null,
                    'siklus' => null,
                    'kategori' => $a->kategoriAset?->deskripsi ?? 'Aset',
                    'nominal' => (float) $a->nominal_dibayar,
                    'status' => $a->status,
                    'bank' => $a->accountBank?->kode_account ?? '-',
                    'route' => route('pembelian-aset.show', $a),
                ]);
            }
        }

        // Hutang Piutang
        if ($jenis === 'semua') {
            $q = HutangPiutang::with(['kategoriHutangPiutang', 'accountBank']);
            $q->where(fn($sub) => $sub->where('jenis', '!=', 'piutang')->orDoesntHave('penjualanAset'));
            if ($status) $q->where('status', $status);
            if ($tgl_dari) $q->whereDate('jatuh_tempo', '>=', $tgl_dari);
            if ($tgl_sampai) $q->whereDate('jatuh_tempo', '<=', $tgl_sampai);
            if ($search) $q->where('aktivitas', 'like', "%{$search}%");
            foreach ($q->latest('created_at')->get() as $h) {
                $rows->push([
                    'type' => 'Hutang/Piutang',
                    'nomor' => $h->nomor_transaksi,
                    'jenis' => $h->jenis === 'hutang' ? 'Uang Masuk (Hutang)' : 'Uang Keluar (Piutang)',
                    'jenis_raw' => $h->jenis === 'hutang' ? 'uang_masuk' : 'uang_keluar',
                    'tanggal' => $h->jatuh_tempo,
                    'sort_at' => $h->updated_at ?? $h->created_at ?? $h->jatuh_tempo,
                    'aktivitas' => $h->aktivitas,
                    'blok' => null,
                    'siklus' => null,
                    'kategori' => $h->kategoriHutangPiutang?->deskripsi ?? 'Hutang/Piutang',
                    'nominal' => (float) $h->nominal,
                    'status' => $h->status,
                    'bank' => $h->accountBank?->kode_account ?? '-',
                    'route' => route('hutang-piutang.show', $h),
                ]);
            }
        } elseif ($jenis === 'uang_masuk') {
            $q = HutangPiutang::with(['kategoriHutangPiutang', 'accountBank'])->where('jenis', 'hutang');
            if ($status) $q->where('status', $status);
            if ($tgl_dari) $q->whereDate('jatuh_tempo', '>=', $tgl_dari);
            if ($tgl_sampai) $q->whereDate('jatuh_tempo', '<=', $tgl_sampai);
            if ($search) $q->where('aktivitas', 'like', "%{$search}%");
            foreach ($q->latest('created_at')->get() as $h) {
                $rows->push([
                    'type' => 'Hutang/Piutang',
                    'nomor' => $h->nomor_transaksi,
                    'jenis' => 'Uang Masuk (Hutang)',
                    'jenis_raw' => 'uang_masuk',
                    'tanggal' => $h->jatuh_tempo,
                    'sort_at' => $h->updated_at ?? $h->created_at ?? $h->jatuh_tempo,
                    'aktivitas' => $h->aktivitas,
                    'blok' => null,
                    'siklus' => null,
                    'kategori' => $h->kategoriHutangPiutang?->deskripsi ?? 'Hutang/Piutang',
                    'nominal' => (float) $h->nominal,
                    'status' => $h->status,
                    'bank' => $h->accountBank?->kode_account ?? '-',
                    'route' => route('hutang-piutang.show', $h),
                ]);
            }
        } elseif ($jenis === 'uang_keluar') {
            $q = HutangPiutang::with(['kategoriHutangPiutang', 'accountBank'])->where('jenis', 'piutang');
            $q->doesntHave('penjualanAset');
            if ($status) $q->where('status', $status);
            if ($tgl_dari) $q->whereDate('jatuh_tempo', '>=', $tgl_dari);
            if ($tgl_sampai) $q->whereDate('jatuh_tempo', '<=', $tgl_sampai);
            if ($search) $q->where('aktivitas', 'like', "%{$search}%");
            foreach ($q->latest('created_at')->get() as $h) {
                $rows->push([
                    'type' => 'Hutang/Piutang',
                    'nomor' => $h->nomor_transaksi,
                    'jenis' => 'Uang Keluar (Piutang)',
                    'jenis_raw' => 'uang_keluar',
                    'tanggal' => $h->jatuh_tempo,
                    'sort_at' => $h->updated_at ?? $h->created_at ?? $h->jatuh_tempo,
                    'aktivitas' => $h->aktivitas,
                    'blok' => null,
                    'siklus' => null,
                    'kategori' => $h->kategoriHutangPiutang?->deskripsi ?? 'Hutang/Piutang',
                    'nominal' => (float) $h->nominal,
                    'status' => $h->status,
                    'bank' => $h->accountBank?->kode_account ?? '-',
                    'route' => route('hutang-piutang.show', $h),
                ]);
            }
        }

        // Panen
        if ($jenis === 'semua' || $jenis === 'uang_masuk') {
            $q = Panen::with(['siklus.blok', 'accountBank']);
            if ($status) $q->where('status', $status);
            if ($tgl_dari) $q->whereDate('tgl_panen', '>=', $tgl_dari);
            if ($tgl_sampai) $q->whereDate('tgl_panen', '<=', $tgl_sampai);
            foreach ($q->latest('created_at')->get() as $p) {
                $rows->push([
                    'type' => 'Panen',
                    'nomor' => '-',
                    'jenis' => 'Uang Masuk',
                    'jenis_raw' => 'uang_masuk',
                    'tanggal' => $p->tgl_panen,
                    'sort_at' => $p->updated_at ?? $p->created_at ?? $p->tgl_panen,
                    'aktivitas' => 'Panen ' . ($p->siklus?->blok?->nama_blok ?? '-'),
                    'blok' => $p->siklus?->blok?->nama_blok,
                    'siklus' => $p->siklus?->nama_siklus,
                    'kategori' => 'Panen',
                    'nominal' => (float) $p->total_penjualan,
                    'status' => $p->status,
                    'bank' => $p->accountBank?->kode_account ?? '-',
                    'route' => route('panen.show', $p),
                ]);
            }
        }

        // Penjualan Aset
        if ($jenis === 'semua' || $jenis === 'uang_masuk') {
            $q = PenjualanAset::with(['pembelianAset', 'accountBank'])->where('nominal_dibayar', '>', 0);
            if ($tgl_dari) $q->whereDate('tgl_penjualan', '>=', $tgl_dari);
            if ($tgl_sampai) $q->whereDate('tgl_penjualan', '<=', $tgl_sampai);
            if ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nomor_transaksi', 'like', "%{$search}%")
                        ->orWhere('pembeli', 'like', "%{$search}%")
                        ->orWhereHas('pembelianAset', fn($aset) => $aset->where('nama_aset', 'like', "%{$search}%"));
                });
            }

            foreach ($q->latest('created_at')->get() as $jual) {
                $rows->push([
                    'type' => 'Penjualan Aset',
                    'nomor' => $jual->nomor_transaksi,
                    'jenis' => 'Uang Masuk',
                    'jenis_raw' => 'uang_masuk',
                    'tanggal' => $jual->tgl_penjualan,
                    'sort_at' => $jual->updated_at ?? $jual->created_at ?? $jual->tgl_penjualan,
                    'aktivitas' => 'Penjualan aset ' . ($jual->pembelianAset?->nama_aset ?? '-'),
                    'blok' => null,
                    'siklus' => null,
                    'kategori' => 'Aset',
                    'nominal' => (float) $jual->nominal_dibayar,
                    'status' => 'selesai',
                    'bank' => $jual->accountBank?->kode_account ?? '-',
                    'route' => $jual->pembelianAset ? route('pembelian-aset.show', $jual->pembelianAset) : null,
                ]);
            }
        }

        $rows = $rows->sortByDesc('sort_at')->values();

        // Kartu hanya menghitung transaksi yang benar-benar terealisasi (selesai),
        // agar Total Masuk/Keluar konsisten dengan kartu per-kategori.
        $selesaiOnly = $rows->where('status', 'selesai');
        $totalMasuk = $selesaiOnly->where('jenis_raw', 'uang_masuk')->sum('nominal');
        $totalKeluar = $selesaiOnly->where('jenis_raw', 'uang_keluar')->sum('nominal');

        $totals = [
            'transaksi' => $selesaiOnly->where('type', 'Transaksi Keuangan')->sum('nominal'),
            'gaji' => $selesaiOnly->where('type', 'Gaji Karyawan')->sum('nominal'),
            'investasi' => $selesaiOnly->where('type', 'Investasi')->sum('nominal'),
            'persediaan' => $selesaiOnly->where('type', 'Pembelian Persediaan')->sum('nominal'),
            'aset' => $selesaiOnly->where('type', 'Pembelian Aset')->sum('nominal'),
            'hutang_piutang' => $selesaiOnly->where('type', 'Hutang/Piutang')->sum('nominal'),
            'panen' => $selesaiOnly->where('type', 'Panen')->sum('nominal'),
        ];

        return compact('rows', 'totalMasuk', 'totalKeluar', 'totals');
    }
}
