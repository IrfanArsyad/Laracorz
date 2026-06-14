<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\Searchable;
use App\Support\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminLog extends Model
{
    use Searchable, Sortable;

    public $timestamps = false;

    protected array $searchable = ['description', 'user_name', 'module'];

    protected array $sortable = ['created_at', 'action', 'module'];

    protected $fillable = [
        'user_id',
        'user_name',
        'module',
        'action',
        'description',
        'loggable_id',
        'loggable_type',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}
