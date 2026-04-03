<?php

namespace App\Models;

class PenyesuaianPersediaan extends BaseModel
{
    protected $fillable = ['persediaan_id', 'tgl_penyesuaian', 'qty_sistem', 'qty_fisik', 'catatan'];

    protected function casts(): array
    {
        return ['tgl_penyesuaian' => 'date'];
    }

    public function persediaan() { return $this->belongsTo(Persediaan::class); }
}
