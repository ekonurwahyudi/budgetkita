<?php

namespace App\Exports;

use App\Models\Kolam;
use App\Models\Persediaan;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class KolamPakanTemplateExport implements FromCollection, WithHeadings, WithMapping, WithEvents, ShouldAutoSize
{
    protected Kolam $kolam;

    public function __construct(Kolam $kolam)
    {
        $this->kolam = $kolam;
    }

    public function collection()
    {
        $item = Persediaan::with('itemPersediaan.kategoriPersediaan')
            ->where('qty', '>', 0)
            ->get()
            ->first(fn($p) => stripos($p->itemPersediaan?->kategoriPersediaan?->deskripsi ?? '', 'pakan') !== false);

        return collect([
            (object) [
                'tanggal' => Carbon::today(),
                'jenis_pakan' => trim(($item?->itemPersediaan?->kode_item_persediaan ?? 'KODE_PAKAN') . ' - ' . ($item?->itemPersediaan?->deskripsi ?? 'Nama pakan')),
                'slot_06' => 1,
                'slot_10' => '',
                'slot_14' => '',
                'slot_18' => '',
                'jumlah' => '=SUM(D3:G3)',
                'puasa' => 'Tidak',
                'kumulatif' => '=SUM($H$3:H3)',
            ],
        ]);
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
        return [
            1,
            $row->tanggal->format('d/m/Y'),
            $row->jenis_pakan,
            $row->slot_06,
            $row->slot_10,
            $row->slot_14,
            $row->slot_18,
            $row->jumlah,
            $row->puasa,
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
