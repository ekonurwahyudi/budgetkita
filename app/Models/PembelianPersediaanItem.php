<?php

namespace App\Models;

class PembelianPersediaanItem extends BaseModel
{
    protected $fillable = [
        'pembelian_persediaan_id', 'item_persediaan_id',
        'qty', 'satuan', 'harga_satuan', 'harga_total',
    ];

    public function pembelianPersediaan() { return $this->belongsTo(PembelianPersediaan::class); }
    public function itemPersediaan() { return $this->belongsTo(ItemPersediaan::class); }
}
