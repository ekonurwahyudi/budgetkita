<?php

namespace App\Models;

class NeracaCutoff extends BaseModel
{
    protected $fillable = ['tahun', 'tanggal_cutoff', 'label'];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'tanggal_cutoff' => 'date',
        ];
    }
}