<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NeracaKeuanganExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected array $data;
    protected int $tahun;

    public function __construct(array $data, int $tahun)
    {
        $this->data = $data;
        $this->tahun = $tahun;
    }

    public function title(): string
    {
        return 'Neraca ' . $this->tahun;
    }

    public function collection()
    {
        return collect([
            ['ASET', ''],
            ['Kas & Bank', $this->data['kasBank']],
            ['Piutang', $this->data['piutang']],
            ['Persediaan', $this->data['persediaan']],
            ['Investasi', $this->data['investasi']],
            ['Aset Tetap (Bruto)', $this->data['asetTetapBruto']],
            ['Akumulasi Depresiasi', -$this->data['akumulasiDepresiasi']],
            ['Aset Tetap (Netto)', $this->data['asetTetapNetto']],
            ['TOTAL ASET', $this->data['totalAset']],
            ['', ''],
            ['KEWAJIBAN', ''],
            ['Hutang', $this->data['hutang']],
            ['TOTAL KEWAJIBAN', $this->data['totalKewajiban']],
            ['', ''],
            ['EKUITAS', ''],
            ['Pendapatan - Transaksi Keuangan', $this->data['pendapatanTransaksi']],
            ['Pendapatan - Panen', $this->data['pendapatanPanen']],
            ['Pendapatan - Investasi', $this->data['pendapatanInvestasi']],
            ['Total Pendapatan', $this->data['totalPendapatan']],
            ['Pengeluaran - Transaksi Keuangan', -$this->data['pengeluaranTransaksi']],
            ['Pengeluaran - Gaji Karyawan', -$this->data['pengeluaranGaji']],
            ['Pengeluaran - Pembelian Pakan', -$this->data['pengeluaranPersediaan']],
            ['Pengeluaran - Pembelian Aset', -$this->data['pengeluaranAset']],
            ['Total Pengeluaran', -$this->data['totalPengeluaran']],
            ['Laba / Rugi ' . $this->tahun, $this->data['labaRugi']],
            ['TOTAL KEWAJIBAN + EKUITAS', $this->data['totalKewajiban'] + $this->data['ekuitas']],
        ]);
    }

    public function headings(): array
    {
        return ['Keterangan', 'Nominal (Rp)'];
    }

    public function map($row): array
    {
        return [
            $row[0],
            $row[1],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $boldRows = [1, 2, 10, 12, 14, 23, 24, 25];
        return [
            1 => ['font' => ['bold' => true]],
            ...collect($boldRows)->mapWithKeys(fn($r) => [$r => ['font' => ['bold' => true]]])->toArray(),
        ];
    }
}