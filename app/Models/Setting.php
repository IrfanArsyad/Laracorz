<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use SerializesDates;

    protected $fillable = ['key', 'value', 'group', 'type', 'label', 'description', 'order'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'order' => 'integer',
        ];
    }
}
