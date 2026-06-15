<?php

namespace App\Exports;

use App\Models\Kolam;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KolamParameterTemplateExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected Kolam $kolam;

    public function __construct(Kolam $kolam)
    {
        $this->kolam = $kolam;
    }

    public function collection()
    {
        $start = $this->kolam->tgl_berdiri ? $this->kolam->tgl_berdiri->copy() : Carbon::today();
        $end = Carbon::today();

        $rows = collect();
        while ($start->lte($end)) {
            $rows->push($start->copy());
            $start->addDay();
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Tanggal', 'Status',
            'pH Pagi', 'pH Sore', 'DO Pagi', 'DO Sore',
            'Suhu Pagi', 'Suhu Sore', 'Kecerahan Pagi', 'Kecerahan Sore',
            'Salinitas', 'Tinggi Air', 'Warna Air',
            'ALK', 'CA', 'MG', 'MBW', 'MASA', 'SR', 'PCR',
            'Perlakuan Harian', 'Oleh',
        ];
    }

    public function map($row): array
    {
        return [
            $row->format('d/m/Y'),
            'Normal',
            '', '', '', '',
            '', '', '', '',
            '', '', '',
            '', '', '', '', '', '', '',
            '',
            auth()->user()?->nama ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
