<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\Searchable;
use App\Support\Traits\SerializesDates;
use App\Support\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use Searchable, SerializesDates, Sortable;

    public $timestamps = false;

    protected array $searchable = ['message', 'event', 'channel'];

    protected array $sortable = ['created_at', 'level', 'channel'];

    protected $fillable = [
        'level',
        'channel',
        'event',
        'message',
        'context',
        'exception',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
