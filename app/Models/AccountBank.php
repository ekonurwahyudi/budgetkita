<?php

namespace App\Models;

use App\Traits\BelongsToActiveTambak;

class AccountBank extends BaseModel
{
    use BelongsToActiveTambak;

    protected $fillable = ['tambak_id', 'kode_account', 'nama_bank', 'nama_pemilik', 'nomor_rekening', 'saldo', 'saldo_awal', 'status'];

    protected function casts(): array
    {
        return [
            'saldo' => 'decimal:2',
            'saldo_awal' => 'decimal:2',
        ];
    }
}
