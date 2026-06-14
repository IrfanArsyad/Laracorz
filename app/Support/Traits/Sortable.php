<?php

declare(strict_types=1);

namespace App\Support\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    public function scopeSort(Builder $query, ?string $field, ?string $direction = 'asc'): Builder
    {
        $allowed = property_exists($this, 'sortable') ? $this->sortable : [];
        $field = $field ?: ($this->defaultSort ?? null);
        $direction = strtolower((string) $direction) === 'desc' ? 'desc' : 'asc';

        if (! $field || ! in_array($field, $allowed, true)) {
            return $query;
        }

        return $query->orderBy($field, $direction);
    }
}
