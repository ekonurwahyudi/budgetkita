<?php

namespace App\Models;

use App\Traits\BelongsToActiveTambak;

class Blok extends BaseModel
{
    use BelongsToActiveTambak;

    protected $fillable = ['tambak_id', 'nama_blok', 'didirikan_pada', 'jumlah_anco', 'panjang', 'lebar', 'kedalaman', 'status_blok'];

    protected function casts(): array
    {
        return ['didirikan_pada' => 'date'];
    }

    public function tambak()
    {
        return $this->belongsTo(Tambak::class);
    }

    public function sikluses()
    {
        return $this->hasMany(Siklus::class);
    }
}
