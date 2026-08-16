<?php

namespace App\Traits;

use App\Support\ActiveTambak;
use Illuminate\Database\Eloquent\Builder;

trait ScopedByActiveTambakRelation
{
    protected static function bootScopedByActiveTambakRelation(): void
    {
        static::addGlobalScope('active_tambak_relation', function (Builder $builder) {
            $activeTambakId = ActiveTambak::id();
            $relation = static::activeTambakRelation();

            if ($activeTambakId && $relation) {
                $builder->whereHas($relation, fn ($query) => $query->where('tambak_id', $activeTambakId));
            }
        });
    }

    protected static function activeTambakRelation(): ?string
    {
        $constant = static::class . '::ACTIVE_TAMBAK_RELATION';

        return defined($constant) ? constant($constant) : null;
    }
}
