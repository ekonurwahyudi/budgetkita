<?php

namespace App\Traits;

use App\Models\Tambak;
use App\Support\ActiveTambak;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToActiveTambak
{
    protected static function bootBelongsToActiveTambak(): void
    {
        static::addGlobalScope('active_tambak', function (Builder $builder) {
            $activeTambakId = ActiveTambak::id();

            if ($activeTambakId) {
                $builder->where($builder->getModel()->getTable() . '.tambak_id', $activeTambakId);
            }
        });

        static::saving(function ($model) {
            if ($activeTambakId = ActiveTambak::id()) {
                $model->tambak_id = $activeTambakId;
            }
        });
    }

    public function tambak()
    {
        return $this->belongsTo(Tambak::class);
    }
}
