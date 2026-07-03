<?php

namespace App\Models;

use App\Traits\ScopedByActiveTambakRelation;

class RiwayatPersediaan extends BaseModel
{
    use ScopedByActiveTambakRelation;

    public const ACTIVE_TAMBAK_RELATION = 'persediaan';

    protected $fillable = [
        'persediaan_id', 'jenis', 'qty_masuk', 'qty_keluar',
        'blok_id', 'siklus_id', 'harga_per_unit', 'harga_total', 'catatan',
    ];

    public function persediaan() { return $this->belongsTo(Persediaan::class); }
    public function blok() { return $this->belongsTo(\App\Models\Blok::class); }
    public function siklus() { return $this->belongsTo(\App\Models\Siklus::class); }
}
