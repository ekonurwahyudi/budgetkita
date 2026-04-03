<?php

namespace App\Models;

class Panen extends BaseModel
{
    protected $fillable = [
        'siklus_id', 'tgl_panen', 'umur', 'ukuran', 'total_berat',
        'harga_jual', 'sisa_bayar', 'total_penjualan', 'pembeli',
        'tipe_panen', 'jenis_pembayaran', 'account_bank_id',
        'pembayaran', 'status',
    ];

    protected function casts(): array
    {
        return ['tgl_panen' => 'date', 'total_penjualan' => 'decimal:2', 'harga_jual' => 'decimal:2'];
    }

    public function siklus() { return $this->belongsTo(Siklus::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
}
