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
use App\Models\NeracaCutoff;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class NeracaKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);
        $tanggalCutoff = $request->get('tanggal_cutoff');

        if ($tanggalCutoff) {
            $tanggalCutoff = Carbon::parse($tanggalCutoff)->endOfDay();
            $tahun = $tanggalCutoff->year;
        } else {
            $tanggalCutoff = Carbon::create($tahun, 12, 31)->endOfDay();
        }

        $data = $this->getNeracaData((int) $tahun, $tanggalCutoff);

        $cutoffs = NeracaCutoff::orderByDesc('tahun')->orderByDesc('tanggal_cutoff')->get();

        return view('keuangan.neraca-keuangan.index', array_merge($data, [
            'tahun' => $tahun,
            'tanggalCutoff' => $tanggalCutoff->format('Y-m-d'),
            'cutoffs' => $cutoffs,
        ]));
    }

    public function storeCutoff(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer',
            'tanggal_cutoff' => 'required|date',
            'label' => 'nullable|string|max:255',
        ]);

        $validated['tanggal_cutoff'] = Carbon::parse($validated['tanggal_cutoff'])->format('Y-m-d');

        NeracaCutoff::create($validated);

        return redirect()->route('neraca-keuangan.index', [
            'tahun' => $validated['tahun'],
            'tanggal_cutoff' => $validated['tanggal_cutoff'],
        ])->with('success', 'Tanggal cut-off berhasil disimpan.');
    }

    public function destroyCutoff($id)
    {
        $cutoff = NeracaCutoff::findOrFail($id);
        $cutoff->delete();

        return redirect()->route('neraca-keuangan.index')->with('success', 'Tanggal cut-off berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);
        $tanggalCutoff = $request->get('tanggal_cutoff');

        if ($tanggalCutoff) {
            $tanggalCutoff = Carbon::parse($tanggalCutoff)->endOfDay();
            $tahun = $tanggalCutoff->year;
        } else {
            $tanggalCutoff = Carbon::create($tahun, 12, 31)->endOfDay();
        }

        $data = $this->getNeracaData((int) $tahun, $tanggalCutoff);
        $filename = 'neraca-keuangan-per-' . $tanggalCutoff->format('d-m-Y') . '.xlsx';
        return Excel::download(new NeracaKeuanganExport($data, (int) $tahun, $tanggalCutoff), $filename);
    }

    private function getNeracaData(int $tahun, Carbon $tanggalCutoff): array
    {
        // === ASET LANCAR ===
        $kasBank = AccountBank::where('status', 'aktif')->sum('saldo');
        $piutang = HutangPiutang::where('jenis', 'piutang')
            ->where('status', 'selesai')
            ->where('jatuh_tempo', '<=', $tanggalCutoff)
            ->sum('sisa_pembayaran');
        $persediaan = Persediaan::where('qty', '>', 0)->sum('total_harga');
        $investasi = Investasi::where('status', 'selesai')
            ->where('created_at', '<=', $tanggalCutoff)
            ->sum('nominal');
        $asetLancar = $kasBank + $piutang + $persediaan + $investasi;

        // === ASET TETAP ===
        $asets = PembelianAset::where('status', 'selesai')
            ->where('tgl_pembelian', '<=', $tanggalCutoff)
            ->get();
        $asetTetapBruto = $asets->sum('nominal_pembelian');
        $akumulasiDepresiasi = $asets->sum(function ($a) use ($tahun) {
            $umur = min($tahun - $a->tgl_pembelian->year, $a->umur_manfaat);
            $umur = max(0, $umur);
            if ($a->metode_depresiasi === 'persen') {
                $depPerTahun = ($a->nominal_pembelian - ($a->nilai_residu ?? 0)) * ((float) $a->persen_depresiasi / 100);
            } elseif ($a->metode_depresiasi === 'tanpa') {
                $depPerTahun = 0;
            } else {
                $depPerTahun = $a->umur_manfaat > 0 ? ($a->nominal_pembelian - ($a->nilai_residu ?? 0)) / max(1, $a->umur_manfaat) : 0;
            }
            return min($depPerTahun * $umur, max(0, (float) $a->nominal_pembelian - (float) ($a->nilai_residu ?? 0)));
        });
        $asetTetapNetto = max(0, $asetTetapBruto - $akumulasiDepresiasi);

        $totalAset = $asetLancar + $asetTetapNetto;

        // === KEWAJIBAN ===
        $hutang = HutangPiutang::where('jenis', 'hutang')
            ->where('status', 'selesai')
            ->where('jatuh_tempo', '<=', $tanggalCutoff)
            ->sum('sisa_pembayaran');
        $totalKewajiban = $hutang;

        // === LABA BERJALAN ===
        $awalTahun = Carbon::create($tahun, 1, 1)->startOfDay();

        $totalPendapatan =
            TransaksiKeuangan::where('jenis_transaksi', 'uang_masuk')
                ->where('status', 'selesai')
                ->whereBetween('tgl_kwitansi', [$awalTahun, $tanggalCutoff])
                ->sum('nominal')
            + Panen::where('status', 'selesai')
                ->whereBetween('tgl_panen', [$awalTahun, $tanggalCutoff])
                ->sum('total_penjualan')
            + Investasi::where('status', 'selesai')
                ->whereBetween('created_at', [$awalTahun, $tanggalCutoff])
                ->sum('nominal');

        $totalPengeluaran =
            TransaksiKeuangan::where('jenis_transaksi', 'uang_keluar')
                ->where('status', 'selesai')
                ->whereBetween('tgl_kwitansi', [$awalTahun, $tanggalCutoff])
                ->sum('nominal')
            + GajiKaryawan::where('status', 'selesai')
                ->whereBetween('created_at', [$awalTahun, $tanggalCutoff])
                ->sum('thp')
            + PembelianPersediaan::where('status', 'selesai')
                ->whereBetween('tgl_pembelian', [$awalTahun, $tanggalCutoff])
                ->withSum('items as total_nominal', 'harga_total')
                ->get()
                ->sum('total_nominal')
            + PembelianAset::where('status', 'selesai')
                ->whereBetween('tgl_pembelian', [$awalTahun, $tanggalCutoff])
                ->sum('nominal_pembelian');

        $labaBerjalan = $totalPendapatan - $totalPengeluaran;

        // === EKUITAS ===
        $modalPemilik = $totalAset - $totalKewajiban - $labaBerjalan;
        $totalEkuitas = $modalPemilik + $labaBerjalan;

        return compact(
            'tahun',
            'kasBank', 'piutang', 'persediaan', 'investasi', 'asetLancar',
            'asetTetapBruto', 'akumulasiDepresiasi', 'asetTetapNetto',
            'totalAset',
            'hutang', 'totalKewajiban',
            'modalPemilik', 'labaBerjalan', 'totalEkuitas'
        );
    }
}