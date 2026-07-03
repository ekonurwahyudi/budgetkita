<?php

namespace App\Support;

class ActiveTambak
{
    public static function id(): ?string
    {
        if (!auth()->check()) {
            return null;
        }

        $tambakIds = auth()->user()->tambaks()->pluck('tambaks.id');

        if ($tambakIds->isEmpty()) {
            return null;
        }

        $activeTambakId = session('active_tambak_id');

        if (!$activeTambakId || !$tambakIds->contains($activeTambakId)) {
            $activeTambakId = $tambakIds->first();
            session(['active_tambak_id' => $activeTambakId]);
        }

        return $activeTambakId;
    }
}
