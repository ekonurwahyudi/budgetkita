<?php

namespace App\Models;

class PembelianPersediaan extends BaseModel
{
    protected $fillable = [
        'nomor_transaksi', 'tgl_pembelian', 'jenis_pembayaran',
        'account_bank_id', 'eviden', 'catatan', 'status',
    ];

    protected function casts(): array
    {
        return ['tgl_pembelian' => 'date', 'eviden' => 'array'];
    }

    public function items() { return $this->hasMany(PembelianPersediaanItem::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
}
