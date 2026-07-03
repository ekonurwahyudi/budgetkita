<?php

namespace App\Models;

use App\Traits\ScopedByActiveTambakRelation;

class Panen extends BaseModel
{
    use ScopedByActiveTambakRelation;

    public const ACTIVE_TAMBAK_RELATION = 'siklus.blok';

    protected $fillable = [
        'siklus_id', 'kolam_id', 'tgl_panen', 'umur', 'ukuran', 'total_berat',
        'harga_jual', 'sisa_bayar', 'total_penjualan', 'pembeli',
        'tipe_panen', 'jenis_pembayaran', 'account_bank_id',
        'pembayaran', 'status',
    ];

    protected function casts(): array
    {
        return ['tgl_panen' => 'date', 'total_penjualan' => 'decimal:2', 'harga_jual' => 'decimal:2'];
    }

    public function siklus() { return $this->belongsTo(Siklus::class); }
    public function kolam() { return $this->belongsTo(Kolam::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
}
