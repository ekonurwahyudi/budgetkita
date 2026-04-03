<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;

class NotifikasiService
{
    public function kirim(string $userId, string $judul, string $pesan, string $tipe = 'info', ?string $link = null): Notifikasi
    {
        return Notifikasi::create([
            'user_id' => $userId,
            'judul' => $judul,
            'pesan' => $pesan,
            'tipe' => $tipe,
            'link' => $link,
        ]);
    }

    public function kirimKeRole(string $roleName, string $judul, string $pesan, string $tipe = 'info', ?string $link = null): void
    {
        $users = User::role($roleName)->get();
        foreach ($users as $user) {
            $this->kirim($user->id, $judul, $pesan, $tipe, $link);
        }
    }

    public function kirimApprovalRequest(string $judul, string $pesan, ?string $link = null): void
    {
        // Kirim ke semua user yang punya permission approve
        $users = User::role('Owner')->get();
        foreach ($users as $user) {
            $this->kirim($user->id, $judul, $pesan, 'approval', $link);
        }
    }
}
