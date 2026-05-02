<?php

namespace App\Imports;

use App\Models\Kolam;
use App\Models\KolamParameter;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class KolamParameterImport implements ToCollection
{
    protected Kolam $kolam;

    public function __construct(Kolam $kolam)
    {
        $this->kolam = $kolam;
    }

    public function collection(Collection $rows)
    {
        $existingParams = KolamParameter::where('kolam_id', $this->kolam->id)
            ->get()
            ->keyBy(fn($p) => $p->tgl_parameter?->format('Y-m-d'));

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            $dateStr = $this->parseDate($row[0] ?? null);
            if (!$dateStr) continue;

            $data = [
                'ph_pagi'          => $this->parseNumeric($row[2] ?? null),
                'ph_sore'          => $this->parseNumeric($row[3] ?? null),
                'do_pagi'          => $this->parseNumeric($row[4] ?? null),
                'do_sore'          => $this->parseNumeric($row[5] ?? null),
                'suhu_pagi'        => $this->parseNumeric($row[6] ?? null),
                'suhu_sore'        => $this->parseNumeric($row[7] ?? null),
                'kecerahan_pagi'   => $this->parseNumeric($row[8] ?? null),
                'kecerahan_sore'   => $this->parseNumeric($row[9] ?? null),
                'salinitas'        => $this->parseNumeric($row[10] ?? null),
                'tinggi_air'       => $this->parseNumeric($row[11] ?? null),
                'warna_air'        => $this->nullIfDash($row[12] ?? null),
                'alk'              => $this->parseNumeric($row[13] ?? null),
                'ca'               => $this->parseNumeric($row[14] ?? null),
                'mg'               => $this->parseNumeric($row[15] ?? null),
                'mbw'              => $this->parseNumeric($row[16] ?? null),
                'masa'             => $this->parseNumeric($row[17] ?? null),
                'sr'               => $this->parseNumeric($row[18] ?? null),
                'pcr'              => $this->parseNumeric($row[19] ?? null),
                'perlakuan_harian' => $this->nullIfDash($row[20] ?? null),
                'status'           => $this->parseStatus($row[1] ?? null),
            ];

            $existing = $existingParams->get($dateStr);

            if ($existing) {
                $updateData = array_filter($data, fn($v) => !is_null($v));
                $existing->update($updateData);
            } else {
                KolamParameter::create(array_merge($data, [
                    'kolam_id'      => $this->kolam->id,
                    'tgl_parameter' => $dateStr,
                    'user_id'       => Auth::id(),
                ]));
            }
        }
    }

    private function parseDate($value): ?string
    {
        if (!$value) return null;

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        foreach ('d/m/Y' as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
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
        if ($value === null || $value === '' || $value === '-' || $value === '·') return null;
        $clean = str_replace(',', '.', $value);
        return is_numeric($clean) ? (float) $clean : null;
    }

    private function nullIfDash($value): ?string
    {
        if ($value === null || $value === '' || $value === '-' || $value === '·') return null;
        return (string) $value;
    }

    private function parseStatus($value): string
    {
        $lower = strtolower(trim((string) ($value ?? '')));
        if ($lower === 'perhatian') return 'perhatian';
        if ($lower === 'kritis') return 'kritis';
        return 'normal';
    }
}