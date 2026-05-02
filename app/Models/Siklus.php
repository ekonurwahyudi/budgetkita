<?php

namespace App\Models;

class Siklus extends BaseModel
{
    protected $fillable = [
        'blok_id', 'nama_siklus', 'tgl_siklus', 'lama_persiapan', 'tgl_tebar',
        'total_tebar', 'spesies_udang', 'umur_awal', 'kecerahan', 'suhu',
        'do_level', 'salinitas', 'ph_pagi', 'ph_sore', 'selisih_ph',
        'fcr', 'adg', 'sr', 'mbw', 'size', 'status', 'harga_pakan',
    ];

    protected function casts(): array
    {
        return [
            'tgl_siklus' => 'date',
            'tgl_tebar' => 'date',
        ];
    }

    public function blok()
    {
        return $this->belongsTo(Blok::class);
    }

    public function panens()
    {
        return $this->hasMany(Panen::class);
    }

    public function kolams()
    {
        return $this->hasMany(Kolam::class);
    }
}
