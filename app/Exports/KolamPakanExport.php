<?php

namespace App\Exports;

use App\Models\Kolam;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class KolamPakanExport implements FromCollection, WithHeadings, WithMapping, WithEvents, ShouldAutoSize
{
    protected Kolam $kolam;
    protected int $rowNumber = 0;

    public function __construct(Kolam $kolam)
    {
        $this->kolam = $kolam;
    }

    public function collection()
    {
        $runningByDate = [];
        $running = 0;

        $this->kolam->pakan()
            ->where('puasa', false)
            ->whereNotNull('tgl_pakan')
            ->orderBy('tgl_pakan')
            ->get()
            ->groupBy(fn($p) => $p->tgl_pakan->format('Y-m-d'))
            ->each(function ($items, $date) use (&$runningByDate, &$running) {
                $running += (float) $items->sum('jumlah_pakan');
                $runningByDate[$date] = $running;
            });

        return $this->kolam
            ->pakan()
            ->with('itemPersediaan')
            ->whereNotNull('tgl_pakan')
            ->orderBy('tgl_pakan')
            ->get()
            ->groupBy(function ($p) {
                return implode('|', [
                    $p->tgl_pakan->format('Y-m-d'),
                    $p->puasa ? 'puasa' : ($p->item_persediaan_id ?? 'item'),
                    $p->unit ?? 'kg',
                ]);
            })
            ->map(function (Collection $items) use ($runningByDate) {
                $first = $items->first();
                $slots = ['06:00' => '', '10:00' => '', '14:00' => '', '18:00' => ''];

                foreach ($items->where('puasa', false) as $item) {
                    $time = $item->tgl_pakan->format('H:i');
                    if (array_key_exists($time, $slots)) {
                        $slots[$time] = (float) $item->jumlah_pakan;
                    }
                }

                return (object) [
                    'tanggal' => $first->tgl_pakan,
                    'jenis_pakan' => $first->puasa ? '' : trim(($first->itemPersediaan?->kode_item_persediaan ?? '') . ' - ' . ($first->itemPersediaan?->deskripsi ?? '')),
                    'slots' => $slots,
                    'jumlah' => (float) $items->where('puasa', false)->sum('jumlah_pakan'),
                    'puasa' => $items->where('puasa', true)->isNotEmpty(),
                    'kumulatif' => $runningByDate[$first->tgl_pakan->format('Y-m-d')] ?? '',
                ];
            })
            ->values();
    }

    public function headings(): array
    {
        return [
            ['No', 'Tanggal', 'Jenis Pakan', 'Pemberian Pakan', '', '', '', 'Jumlah Pakan', 'Puasa', 'Pakan Kumulatif'],
            ['', '', '', '06:00', '10:00', '14:00', '18:00', '', '', ''],
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->tanggal->format('d/m/Y'),
            $row->jenis_pakan,
            $row->slots['06:00'],
            $row->slots['10:00'],
            $row->slots['14:00'],
            $row->slots['18:00'],
            $row->jumlah ?: '',
            $row->puasa ? 'Ya' : 'Tidak',
            $row->kumulatif,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach (['A', 'B', 'C', 'H', 'I', 'J'] as $column) {
                    $sheet->mergeCells($column . '1:' . $column . '2');
                }
                $sheet->mergeCells('D1:G1');

                $sheet->getStyle('A1:J2')->getFont()->setBold(true);
                $sheet->getStyle('A1:J2')->getAlignment()->setHorizontal('center')->setVertical('center');
                $sheet->getStyle('D1:G2')->getFill()->setFillType('solid')->getStartColor()->setRGB('EAF1FB');
            },
        ];
    }
}
