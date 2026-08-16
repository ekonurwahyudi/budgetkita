<?php

namespace App\Models;

use App\Traits\BelongsToActiveTambak;

class PembelianPersediaan extends BaseModel
{
    use BelongsToActiveTambak;

    protected $fillable = [
        'tambak_id', 'blok_id', 'siklus_id', 'nomor_transaksi', 'tgl_pembelian', 'jenis_pembayaran',
        'account_bank_id', 'status_pembayaran', 'nominal_dibayar', 'hutang_piutang_id',
        'eviden', 'catatan', 'status', 'created_by', 'reject_reason',
    ];

    protected function casts(): array
    {
        return ['tgl_pembelian' => 'date', 'nominal_dibayar' => 'decimal:2', 'eviden' => 'array'];
    }

    public function items() { return $this->hasMany(PembelianPersediaanItem::class); }
    public function returns() { return $this->hasMany(PembelianPersediaanReturn::class); }
    public function blok() { return $this->belongsTo(Blok::class); }
    public function siklus() { return $this->belongsTo(Siklus::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
    public function hutangPiutang() { return $this->belongsTo(HutangPiutang::class); }
    public function pembuat() { return $this->belongsTo(User::class, 'created_by'); }
}
