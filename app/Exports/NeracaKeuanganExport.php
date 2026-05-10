<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class NeracaKeuanganExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected array $data;
    protected int $tahun;
    protected Carbon $tanggalCutoff;

    public function __construct(array $data, int $tahun, Carbon $tanggalCutoff)
    {
        $this->data = $data;
        $this->tahun = $tahun;
        $this->tanggalCutoff = $tanggalCutoff;
    }

    public function title(): string
    {
        return 'Neraca Per ' . $this->tanggalCutoff->format('d-m-Y');
    }

    public function collection()
    {
        return collect([
            ['ASET', ''],
            ['   Aset Lancar', ''],
            ['      Kas & Bank', $this->data['kasBank']],
            ['      Piutang', $this->data['piutang']],
            ['      Persediaan', $this->data['persediaan']],
            ['      Investasi', $this->data['investasi']],
            ['   Subtotal Aset Lancar', $this->data['asetLancar']],
            ['', ''],
            ['   Aset Tetap', ''],
            ['      Aset Tetap (Bruto)', $this->data['asetTetapBruto']],
            ['      Akumulasi Penyusutan', -$this->data['akumulasiDepresiasi']],
            ['   Subtotal Aset Tetap (Netto)', $this->data['asetTetapNetto']],
            ['', ''],
            ['TOTAL ASET', $this->data['totalAset']],
            ['', ''],
            ['KEWAJIBAN', ''],
            ['   Hutang Usaha', $this->data['hutang']],
            ['TOTAL KEWAJIBAN', $this->data['totalKewajiban']],
            ['', ''],
            ['EKUITAS', ''],
            ['   Modal Pemilik', $this->data['modalPemilik']],
            ['   Laba Tahun Berjalan', $this->data['labaBerjalan']],
            ['TOTAL EKUITAS', $this->data['totalEkuitas']],
            ['', ''],
            ['TOTAL KEWAJIBAN + EKUITAS', $this->data['totalKewajiban'] + $this->data['totalEkuitas']],
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
        $boldRows = [1, 14, 17, 20, 23, 25];
        return [
            1 => ['font' => ['bold' => true]],
            ...collect($boldRows)->mapWithKeys(fn($r) => [$r => ['font' => ['bold' => true]]])->toArray(),
        ];
    }
}