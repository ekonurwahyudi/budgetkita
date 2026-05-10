<?php

namespace App\Models;

class Investasi extends BaseModel
{
    protected $fillable = [
        'nomor_transaksi', 'deskripsi', 'nominal', 'kategori_investasi_id',
        'eviden', 'catatan', 'jenis_pembayaran', 'account_bank_id', 'status', 'created_at',
    ];

    protected function casts(): array
    {
        return ['nominal' => 'decimal:2', 'eviden' => 'array', 'created_at' => 'datetime'];
    }

    public function kategoriInvestasi() { return $this->belongsTo(KategoriInvestasi::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
}
