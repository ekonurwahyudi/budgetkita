<?php

namespace App\Models;

class GajiKaryawan extends BaseModel
{
    protected $fillable = [
        'nomor_transaksi', 'user_id', 'gaji_pokok', 'upah_lembur', 'bonus',
        'thp', 'pajak', 'bpjs', 'potongan', 'jenis_pembayaran',
        'account_bank_id', 'status',
    ];

    protected function casts(): array
    {
        return [
            'gaji_pokok' => 'decimal:2', 'upah_lembur' => 'decimal:2',
            'bonus' => 'decimal:2', 'thp' => 'decimal:2',
            'pajak' => 'decimal:2', 'bpjs' => 'decimal:2', 'potongan' => 'decimal:2',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
}
