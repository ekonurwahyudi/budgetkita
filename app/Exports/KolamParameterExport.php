<?php

namespace App\Exports;

use App\Models\Kolam;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KolamParameterExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected Kolam $kolam;

    public function __construct(Kolam $kolam)
    {
        $this->kolam = $kolam;
    }

    public function collection()
    {
        $kolam = $this->kolam->load('parameters.user');

        $start = $kolam->tgl_berdiri ? $kolam->tgl_berdiri->copy() : Carbon::today();
        $end = Carbon::today();

        $parametersByKeyed = $kolam->parameters->keyBy(fn($p) => $p->tgl_parameter?->format('Y-m-d'));

        $rows = collect();
        while ($start->lte($end)) {
            $dateStr = $start->copy()->format('Y-m-d');
            $p = $parametersByKeyed->get($dateStr);
            $rows->push((object)[
                'tgl_parameter' => $start->copy(),
                'parameter' => $p,
            ]);
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
        $p = $row->parameter;
        return [
            $row->tgl_parameter->format('d/m/Y'),
            $p ? ucfirst($p->status) : '-',
            $p?->ph_pagi ?? '-',
            $p?->ph_sore ?? '-',
            $p?->do_pagi ?? '-',
            $p?->do_sore ?? '-',
            $p?->suhu_pagi ?? '-',
            $p?->suhu_sore ?? '-',
            $p?->kecerahan_pagi ?? '-',
            $p?->kecerahan_sore ?? '-',
            $p?->salinitas ?? '-',
            $p?->tinggi_air ?? '-',
            $p?->warna_air ?? '-',
            $p?->alk ?? '-',
            $p?->ca ?? '-',
            $p?->mg ?? '-',
            $p?->mbw ?? '-',
            $p?->masa ?? '-',
            $p?->sr ?? '-',
            $p?->pcr ?? '-',
            $p?->perlakuan_harian ?? '-',
            $p?->user?->nama ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}