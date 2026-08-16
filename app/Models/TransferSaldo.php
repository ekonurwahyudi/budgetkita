<?php

namespace App\Models;

class TransferSaldo extends BaseModel
{
    protected $fillable = ['dari_account_bank_id', 'ke_account_bank_id', 'nominal', 'catatan'];

    protected function casts(): array
    {
        return ['nominal' => 'decimal:2'];
    }

    public function dariBank() { return $this->belongsTo(AccountBank::class, 'dari_account_bank_id'); }
    public function keBank()   { return $this->belongsTo(AccountBank::class, 'ke_account_bank_id'); }
}
