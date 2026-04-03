<?php

namespace App\Models;

use App\Traits\HasUuid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuid, HasRoles;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nama',
        'jabatan',
        'no_hp',
        'email',
        'password',
        'status',
        'tempat_lahir',
        'tgl_lahir',
        'nomor_rekening',
        'bank',
        'mulai_bekerja',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tgl_lahir' => 'date',
            'mulai_bekerja' => 'date',
        ];
    }
}
