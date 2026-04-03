<?php

namespace App\Models;

class KategoriPersediaan extends BaseModel
{
    protected $fillable = ['kode_persediaan', 'deskripsi'];

    public function itemPersediaans()
    {
        return $this->hasMany(ItemPersediaan::class);
    }
}
