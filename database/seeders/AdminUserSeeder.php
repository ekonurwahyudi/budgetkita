<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@budgetkita.com'],
            [
                'nama' => 'Administrator',
                'jabatan' => 'Owner',
                'no_hp' => '081234567890',
                'password' => Hash::make('password'),
                'status' => 'aktif',
            ]
        );

        $admin->assignRole('Owner');
    }
}
