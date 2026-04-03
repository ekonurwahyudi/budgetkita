<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AutoNumberService
{
    public function generate(string $prefix): string
    {
        $year = (int) date('Y');
        $yearShort = date('y');

        return DB::transaction(function () use ($prefix, $year, $yearShort) {
            $record = DB::table('auto_numbers')
                ->where('prefix', $prefix)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($record) {
                $nextNumber = $record->last_number + 1;
                DB::table('auto_numbers')
                    ->where('prefix', $prefix)
                    ->where('year', $year)
                    ->update(['last_number' => $nextNumber, 'updated_at' => now()]);
            } else {
                $nextNumber = 1;
                DB::table('auto_numbers')->insert([
                    'prefix' => $prefix,
                    'year' => $year,
                    'last_number' => $nextNumber,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return sprintf('%s-%s-%06d', $prefix, $yearShort, $nextNumber);
        });
    }
}
