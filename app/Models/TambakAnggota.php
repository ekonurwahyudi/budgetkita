<?php

namespace App\Models;

class TambakAnggota extends BaseModel
{
    protected $fillable = ['tambak_id', 'user_id', 'peran'];

    public function tambak()
    {
        return $this->belongsTo(Tambak::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
