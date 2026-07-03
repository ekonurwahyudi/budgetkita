<?php

namespace App\Models;

use App\Traits\ScopedByActiveTambakRelation;

class PenyesuaianPersediaan extends BaseModel
{
    use ScopedByActiveTambakRelation;

    public const ACTIVE_TAMBAK_RELATION = 'persediaan';

    protected $fillable = ['persediaan_id', 'tgl_penyesuaian', 'qty_sistem', 'qty_fisik', 'catatan'];

    protected function casts(): array
    {
        return ['tgl_penyesuaian' => 'date'];
    }

    public function persediaan() { return $this->belongsTo(Persediaan::class); }
}
