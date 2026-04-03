<?php

namespace App\Models;

class KategoriTransaksi extends BaseModel
{
    protected $fillable = ['kode_kategori', 'deskripsi'];

    public function itemTransaksis()
    {
        return $this->hasMany(ItemTransaksi::class);
    }
}
