<?php

namespace App\Models;

use App\Traits\BelongsToActiveTambak;

class Investasi extends BaseModel
{
    use BelongsToActiveTambak;

    protected $fillable = [
        'tambak_id', 'nomor_transaksi', 'deskripsi', 'nominal', 'kategori_investasi_id',
        'eviden', 'catatan', 'jenis_pembayaran', 'account_bank_id', 'status',
        'created_by', 'reject_reason', 'created_at',
    ];

    protected function casts(): array
    {
        return ['nominal' => 'decimal:2', 'eviden' => 'array', 'created_at' => 'datetime'];
    }

    public function kategoriInvestasi() { return $this->belongsTo(KategoriInvestasi::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
    public function pembuat() { return $this->belongsTo(User::class, 'created_by'); }
}
