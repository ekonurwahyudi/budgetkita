<?php

namespace App\Models;

use App\Traits\ScopedByActiveTambakRelation;

class PembelianPersediaanItem extends BaseModel
{
    use ScopedByActiveTambakRelation;

    public const ACTIVE_TAMBAK_RELATION = 'pembelianPersediaan';

    protected $fillable = [
        'pembelian_persediaan_id', 'item_persediaan_id',
        'qty', 'satuan', 'harga_satuan', 'harga_total',
    ];

    public function pembelianPersediaan() { return $this->belongsTo(PembelianPersediaan::class); }
    public function itemPersediaan() { return $this->belongsTo(ItemPersediaan::class); }
    public function returns() { return $this->hasMany(PembelianPersediaanReturn::class); }
}
