<?php

namespace App\Models;

use App\Traits\BelongsToActiveTambak;

class PembelianPersediaan extends BaseModel
{
    use BelongsToActiveTambak;

    protected $fillable = [
        'tambak_id', 'nomor_transaksi', 'tgl_pembelian', 'jenis_pembayaran',
        'account_bank_id', 'eviden', 'catatan', 'status', 'created_by', 'reject_reason',
    ];

    protected function casts(): array
    {
        return ['tgl_pembelian' => 'date', 'eviden' => 'array'];
    }

    public function items() { return $this->hasMany(PembelianPersediaanItem::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
    public function pembuat() { return $this->belongsTo(User::class, 'created_by'); }
}
