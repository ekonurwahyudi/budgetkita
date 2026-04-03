<?php

namespace App\Models;

class Tambak extends BaseModel
{
    protected $fillable = ['nama_tambak', 'lokasi', 'alamat', 'total_lahan', 'didirikan_pada', 'catatan'];

    protected function casts(): array
    {
        return ['didirikan_pada' => 'date'];
    }

    public function bloks()
    {
        return $this->hasMany(Blok::class);
    }

    public function anggotas()
    {
        return $this->hasMany(TambakAnggota::class);
    }
}
