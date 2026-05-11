<?php

namespace App\Models;

class Persediaan extends BaseModel
{
    protected $fillable = ['item_persediaan_id', 'qty', 'minimum_stok', 'unit', 'harga_per_unit', 'total_harga'];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'minimum_stok' => 'decimal:2',
            'harga_per_unit' => 'decimal:2',
            'total_harga' => 'decimal:2',
        ];
    }

    public function itemPersediaan() { return $this->belongsTo(ItemPersediaan::class); }
    public function riwayats() { return $this->hasMany(RiwayatPersediaan::class); }
    public function penyesuaians() { return $this->hasMany(PenyesuaianPersediaan::class); }
}
