<?php

namespace App\Exports;

use App\Models\TransaksiKeuangan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransaksiKeuanganExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
            'No. Transaksi', 'Jenis', 'Tanggal', 'Aktivitas/Kegiatan',
            'Kategori', 'Tambak', 'Blok', 'Siklus',
            'Nominal', 'Jenis Pembayaran', 'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->nomor_transaksi,
            match($row->jenis_transaksi) {
                'uang_masuk'  => 'Uang Masuk',
                'uang_keluar' => 'Uang Keluar',
                'cash_card'   => 'Cash Card',
                default       => $row->jenis_transaksi,
            },
            $row->tgl_kwitansi?->format('d/m/Y') ?? '-',
            $row->aktivitas,
            $row->kategoriTransaksi?->deskripsi ?? '-',
            $row->tambak?->nama_tambak ?? '-',
            $row->blok?->nama_blok ?? '-',
            $row->siklus?->nama_siklus ?? '-',
            $row->nominal,
            ucfirst($row->jenis_pembayaran),
            ucfirst(str_replace('_', ' ', $row->status ?? '')),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
