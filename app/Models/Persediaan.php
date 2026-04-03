<?php

namespace App\Models;

class Persediaan extends BaseModel
{
    protected $fillable = ['item_persediaan_id', 'qty', 'unit', 'harga_per_unit', 'total_harga'];

    public function itemPersediaan() { return $this->belongsTo(ItemPersediaan::class); }
    public function riwayats() { return $this->hasMany(RiwayatPersediaan::class); }
}
