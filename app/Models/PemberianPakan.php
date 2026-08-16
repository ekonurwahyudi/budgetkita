<?php

namespace App\Models;

use App\Traits\ScopedByActiveTambakRelation;

class PemberianPakan extends BaseModel
{
    use ScopedByActiveTambakRelation;

    public const ACTIVE_TAMBAK_RELATION = 'blok';

    protected $fillable = ['blok_id', 'siklus_id', 'kolam_id', 'tgl_pakan', 'jumlah_pakan', 'unit', 'puasa', 'item_persediaan_id'];

    protected function casts(): array
    {
        return ['tgl_pakan' => 'datetime', 'puasa' => 'boolean'];
    }

    public function blok() { return $this->belongsTo(Blok::class); }
    public function siklus() { return $this->belongsTo(Siklus::class); }
    public function kolam() { return $this->belongsTo(Kolam::class); }
    public function itemPersediaan() { return $this->belongsTo(ItemPersediaan::class); }
}
