<?php

namespace App\Models;

class SaldoAdjustment extends BaseModel
{
    protected $fillable = [
        'account_bank_id',
        'saldo_sebelumnya',
        'saldo_baru',
        'selisih',
        'jenis',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'saldo_sebelumnya' => 'decimal:2',
            'saldo_baru'       => 'decimal:2',
            'selisih'          => 'decimal:2',
        ];
    }

    public function accountBank()
    {
        return $this->belongsTo(AccountBank::class);
    }
}
