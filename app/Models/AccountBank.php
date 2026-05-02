<?php

namespace App\Models;

class AccountBank extends BaseModel
{
    protected $fillable = ['kode_account', 'nama_bank', 'nama_pemilik', 'nomor_rekening', 'saldo', 'saldo_awal', 'status'];

    protected function casts(): array
    {
        return [
            'saldo' => 'decimal:2',
            'saldo_awal' => 'decimal:2',
        ];
    }
}
