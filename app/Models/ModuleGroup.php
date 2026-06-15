<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\HasActiveScope;
use App\Support\Traits\SerializesDates;
use Database\Factories\ModuleGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleGroup extends Model
{
    /** @use HasFactory<ModuleGroupFactory> */
    use HasActiveScope, HasFactory, SerializesDates;

    protected $activeColumn = 'active';

    protected $fillable = [
        'name',
        'label',
        'icon',
        'order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->whereNull('parent_id');
    }
}
