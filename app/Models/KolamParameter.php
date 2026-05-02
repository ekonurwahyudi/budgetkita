<?php

namespace App\Models;

class KolamParameter extends BaseModel
{
    protected $fillable = [
        'kolam_id', 'user_id', 'tgl_parameter',
        'ph_pagi', 'ph_sore', 'do_pagi', 'do_sore',
        'suhu_pagi', 'suhu_sore', 'kecerahan_pagi', 'kecerahan_sore',
        'salinitas', 'tinggi_air', 'warna_air',
        'alk', 'ca', 'mg', 'mbw', 'masa', 'sr', 'pcr',
        'perlakuan_harian', 'status',
    ];

    protected function casts(): array
    {
        return ['tgl_parameter' => 'date'];
    }

    public function kolam() { return $this->belongsTo(Kolam::class); }
    public function user() { return $this->belongsTo(User::class); }
}
