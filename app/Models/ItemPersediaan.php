<?php

namespace App\Models;

class ItemPersediaan extends BaseModel
{
    protected $fillable = ['kategori_persediaan_id', 'kode_item_persediaan', 'deskripsi'];

    public function kategoriPersediaan()
    {
        return $this->belongsTo(KategoriPersediaan::class);
    }
}
