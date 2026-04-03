<?php

namespace App\Models;

class PemberianPakan extends BaseModel
{
    protected $fillable = ['blok_id', 'siklus_id', 'tgl_pakan', 'jumlah_pakan', 'item_persediaan_id'];

    protected function casts(): array
    {
        return ['tgl_pakan' => 'datetime'];
    }

    public function blok() { return $this->belongsTo(Blok::class); }
    public function siklus() { return $this->belongsTo(Siklus::class); }
    public function itemPersediaan() { return $this->belongsTo(ItemPersediaan::class); }
}
