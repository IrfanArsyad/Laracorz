<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\HasActiveScope;
use App\Support\Traits\SerializesDates;
use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    /** @use HasFactory<ModuleFactory> */
    use HasActiveScope, HasFactory, SerializesDates, SoftDeletes;

    protected $activeColumn = 'active';

    protected $fillable = [
        'parent_id',
        'module_group_id',
        'name',
        'label',
        'icon',
        'url',
        'route_name',
        'badge_source',
        'extra_actions',
        'active',
        'external',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'extra_actions' => 'array',
            'active' => 'boolean',
            'external' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    public function moduleGroup(): BelongsTo
    {
        return $this->belongsTo(ModuleGroup::class);
    }

    public function group(): ?ModuleGroup
    {
        $node = $this;
        while ($node->parent_id) {
            $node = $node->parent()->first();
            if (! $node) {
                return null;
            }
        }

        return $node->moduleGroup;
    }

    public function isLeaf(): bool
    {
        return ! empty($this->url) || ! empty($this->route_name);
    }

    public function isContainer(): bool
    {
        return ! $this->isLeaf();
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }
}
