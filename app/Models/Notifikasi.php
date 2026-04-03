<?php

namespace App\Models;

class Notifikasi extends BaseModel
{
    protected $fillable = ['user_id', 'judul', 'pesan', 'tipe', 'link', 'dibaca_pada'];

    protected function casts(): array
    {
        return ['dibaca_pada' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeBelumDibaca($query)
    {
        return $query->whereNull('dibaca_pada');
    }
}
