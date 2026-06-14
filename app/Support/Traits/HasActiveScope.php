<?php

declare(strict_types=1);

namespace App\Support\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveScope
{
    public function scopeActive(Builder $query): Builder
    {
        $column = property_exists($this, 'activeColumn') && $this->activeColumn
            ? $this->activeColumn
            : 'is_active';

        return $query->where($column, true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        $column = property_exists($this, 'activeColumn') && $this->activeColumn
            ? $this->activeColumn
            : 'is_active';

        return $query->where($column, false);
    }
}
