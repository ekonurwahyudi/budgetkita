<?php

namespace App\Imports;

use App\Models\ItemPersediaan;
use App\Models\Kolam;
use App\Models\PemberianPakan;
use App\Models\Persediaan;
use App\Models\RiwayatPersediaan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class KolamPakanImport implements ToCollection
{
    protected Kolam $kolam;

    public function __construct(Kolam $kolam)
    {
        $this->kolam = $kolam->loadMissing('siklus');
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                if ($index < 2) continue;

                $date = $this->parseDate($row[1] ?? null);
                if (!$date) continue;

                $puasa = $this->parseBoolean($row[8] ?? null);

                if ($puasa) {
                    $this->createPuasa($date);
                    continue;
                }

                $item = $this->findItem($row[2] ?? null);
                if (!$item) {
                    throw new \RuntimeException('Jenis pakan tidak ditemukan pada baris ' . ($index + 1) . '.');
                }

                $slots = [
                    '06:00' => $this->parseNumeric($row[3] ?? null),
                    '10:00' => $this->parseNumeric($row[4] ?? null),
                    '14:00' => $this->parseNumeric($row[5] ?? null),
                    '18:00' => $this->parseNumeric($row[6] ?? null),
                ];

                if (!collect($slots)->filter(fn($qty) => $qty !== null && $qty > 0)->count()) {
                    continue;
                }

                foreach ($slots as $time => $jumlah) {
                    if (!$jumlah || $jumlah <= 0) continue;
                    $this->createPakan($item, $date, $time, $jumlah, $index + 1);
                }
            }
        });
    }

    private function createPuasa(string $date): void
    {
        PemberianPakan::create([
            'blok_id'            => $this->kolam->siklus->blok_id,
            'siklus_id'          => $this->kolam->siklus_id,
            'kolam_id'           => $this->kolam->id,
            'tgl_pakan'          => Carbon::parse($date . ' 06:00'),
            'jumlah_pakan'       => 0,
            'unit'               => 'kg',
            'puasa'              => true,
            'item_persediaan_id' => null,
        ]);
    }

    private function createPakan(ItemPersediaan $item, string $date, string $time, float $jumlah, int $rowNumber): void
    {
        $persediaan = Persediaan::where('item_persediaan_id', $item->id)->first();
        if (!$persediaan) {
            throw new \RuntimeException('Persediaan item pakan tidak ditemukan pada baris ' . $rowNumber . '.');
        }

        $unit = $persediaan->unit ?: 'kg';
        $qtyBase = $this->toBaseUnit($jumlah, $unit);

        if ((float) $persediaan->qty < $qtyBase) {
            throw new \RuntimeException('Stok tidak mencukupi untuk ' . ($item->deskripsi ?? $item->kode_item_persediaan) . ' pada baris ' . $rowNumber . '.');
        }

        $pakan = PemberianPakan::create([
            'blok_id'            => $this->kolam->siklus->blok_id,
            'siklus_id'          => $this->kolam->siklus_id,
            'kolam_id'           => $this->kolam->id,
            'tgl_pakan'          => Carbon::parse($date . ' ' . $time),
            'jumlah_pakan'       => $jumlah,
            'unit'               => $unit,
            'puasa'              => false,
            'item_persediaan_id' => $item->id,
        ]);

        $persediaan->decrement('qty', $qtyBase);
        $persediaan->total_harga = $persediaan->qty * $persediaan->harga_per_unit;
        $persediaan->save();

        RiwayatPersediaan::create([
            'persediaan_id'  => $persediaan->id,
            'jenis'          => 'pengeluaran',
            'qty_masuk'      => 0,
            'qty_keluar'     => $qtyBase,
            'blok_id'        => $this->kolam->siklus->blok_id,
            'siklus_id'      => $this->kolam->siklus_id,
            'harga_per_unit' => $persediaan->harga_per_unit,
            'harga_total'    => $qtyBase * $persediaan->harga_per_unit,
            'catatan'        => 'Import pemberian pakan kolam ' . $this->kolam->nama_kolam . ' - ' . ($pakan->itemPersediaan?->deskripsi ?? ''),
        ]);
    }

    private function findItem($value): ?ItemPersediaan
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') return null;

        $parts = array_map('trim', explode(' - ', $value, 2));
        $code = $parts[0] ?? $value;
        $name = $parts[1] ?? $value;

        return ItemPersediaan::with('kategoriPersediaan')
            ->where(function ($query) use ($value, $code, $name) {
                $query->where('kode_item_persediaan', $value)
                    ->orWhere('kode_item_persediaan', $code)
                    ->orWhere('deskripsi', $value)
                    ->orWhere('deskripsi', $name);
            })
            ->get()
            ->first(fn($item) => stripos($item->kategoriPersediaan?->deskripsi ?? '', 'pakan') !== false);
    }

    private function parseDate($value): ?string
    {
        if (!$value) return null;

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
        }

        foreach (['d/m/Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim((string) $value))->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseNumeric($value): ?float
    {
        if ($value === null || $value === '' || $value === '-') return null;
        $clean = str_replace(',', '.', (string) $value);
        return is_numeric($clean) ? (float) $clean : null;
    }

    private function parseBoolean($value): bool
    {
        $value = strtolower(trim((string) ($value ?? '')));
        return in_array($value, ['1', 'ya', 'yes', 'true', 'puasa'], true);
    }

    private function toBaseUnit(float $qty, string $unit): float
    {
        return match(strtolower($unit)) {
            'gram', 'ml' => $qty / 1000,
            default => $qty,
        };
    }
}
