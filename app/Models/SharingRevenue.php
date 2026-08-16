<?php

namespace App\Models;

use App\Traits\ScopedByActiveTambakRelation;

class SharingRevenue extends BaseModel
{
    use ScopedByActiveTambakRelation;

    public const ACTIVE_TAMBAK_RELATION = 'blok';

    protected $fillable = [
        'nomor_transaksi', 'nama_penerima', 'blok_id', 'siklus_id',
        'total_keuntungan', 'persentase', 'nominal', 'jenis_pembayaran',
        'account_bank_id', 'eviden', 'catatan', 'status', 'created_by',
        'reject_reason',
    ];

    protected function casts(): array
    {
        return [
            'total_keuntungan' => 'decimal:2',
            'persentase' => 'decimal:2',
            'nominal' => 'decimal:2',
            'eviden' => 'array',
        ];
    }

    public function blok() { return $this->belongsTo(Blok::class); }
    public function siklus() { return $this->belongsTo(Siklus::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
    public function pembuat() { return $this->belongsTo(User::class, 'created_by'); }
}
