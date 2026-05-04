<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanKeuanganExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected Collection $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No. Transaksi', 'Tipe', 'Jenis', 'Tanggal', 'Aktivitas/Kegiatan',
            'Blok', 'Siklus', 'Kategori', 'Nominal', 'Status', 'Bank',
        ];
    }

    public function map($row): array
    {
        return [
            $row['nomor'],
            $row['type'],
            $row['jenis'],
            $row['tanggal']?->format('d/m/Y') ?? '-',
            $row['aktivitas'],
            $row['blok'] ?? '-',
            $row['siklus'] ?? '-',
            $row['kategori'] ?? '-',
            $row['nominal'],
            ucfirst(str_replace('_', ' ', $row['status'] ?? '')),
            $row['bank'] ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}