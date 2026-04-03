<?php

namespace App\Models;

class ItemTransaksi extends BaseModel
{
    protected $fillable = ['kategori_transaksi_id', 'kode_item', 'deskripsi'];

    public function kategoriTransaksi()
    {
        return $this->belongsTo(KategoriTransaksi::class);
    }
}
