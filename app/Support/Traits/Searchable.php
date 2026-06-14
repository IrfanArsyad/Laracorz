<?php

declare(strict_types=1);

namespace App\Support\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $fields = property_exists($this, 'searchable') ? $this->searchable : [];
        if (empty($fields)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($fields, $term): void {
            foreach ($fields as $field) {
                $q->orWhere($field, 'ilike', '%'.$term.'%');
            }
        });
    }
}
