<?php

namespace App\Models;

use App\Traits\BelongsToActiveTambak;

class GajiKaryawan extends BaseModel
{
    use BelongsToActiveTambak;

    protected $fillable = [
        'tambak_id', 'nomor_transaksi', 'user_id', 'gaji_pokok', 'upah_lembur', 'bonus',
        'thp', 'pajak', 'bpjs', 'potongan', 'jenis_pembayaran',
        'account_bank_id', 'eviden', 'status', 'created_by', 'reject_reason', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'gaji_pokok' => 'decimal:2', 'upah_lembur' => 'decimal:2',
            'bonus' => 'decimal:2', 'thp' => 'decimal:2',
            'pajak' => 'decimal:2', 'bpjs' => 'decimal:2', 'potongan' => 'decimal:2',
            'eviden' => 'array', 'created_at' => 'datetime',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
    public function pembuat() { return $this->belongsTo(User::class, 'created_by'); }
}
