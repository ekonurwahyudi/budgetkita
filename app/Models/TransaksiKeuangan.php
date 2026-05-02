<?php

namespace App\Models;

class TransaksiKeuangan extends BaseModel
{
    protected $fillable = [
        'nomor_transaksi', 'jenis_transaksi', 'tgl_kwitansi', 'aktivitas',
        'nominal', 'item_transaksi_id', 'kategori_transaksi_id', 'tambak_id',
        'blok_id', 'siklus_id', 'sumber_dana_id', 'jenis_pembayaran',
        'account_bank_id', 'eviden', 'catatan', 'status',
    ];

    protected function casts(): array
    {
        return ['tgl_kwitansi' => 'date', 'nominal' => 'decimal:2', 'eviden' => 'array'];
    }

    public function itemTransaksi() { return $this->belongsTo(ItemTransaksi::class); }
    public function kategoriTransaksi() { return $this->belongsTo(KategoriTransaksi::class); }
    public function tambak() { return $this->belongsTo(Tambak::class); }
    public function blok() { return $this->belongsTo(Blok::class); }
    public function siklus() { return $this->belongsTo(Siklus::class); }
    public function sumberDana() { return $this->belongsTo(SumberDana::class); }
    public function accountBank() { return $this->belongsTo(AccountBank::class); }
}
