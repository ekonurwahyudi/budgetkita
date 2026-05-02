<?php

namespace App\Models;

class Kolam extends BaseModel
{
    protected $fillable = ['siklus_id', 'blok_id', 'nama_kolam', 'tgl_berdiri', 'total_tebar', 'status'];

    protected function casts(): array
    {
        return ['tgl_berdiri' => 'date'];
    }

    public function siklus() { return $this->belongsTo(Siklus::class); }
    public function blok() { return $this->belongsTo(Blok::class); }
    public function users() { return $this->belongsToMany(User::class, 'kolam_users'); }
    public function parameters() { return $this->hasMany(KolamParameter::class); }
}
