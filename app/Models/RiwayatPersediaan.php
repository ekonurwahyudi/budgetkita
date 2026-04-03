<?php

namespace App\Models;

class RiwayatPersediaan extends BaseModel
{
    protected $fillable = [
        'persediaan_id', 'jenis', 'qty_masuk', 'qty_keluar',
        'blok_id', 'siklus_id', 'harga_per_unit', 'harga_total', 'catatan',
    ];

    public function persediaan() { return $this->belongsTo(Persediaan::class); }
}
