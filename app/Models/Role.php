<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\ModuleRegistry;
use App\Support\Traits\HasActiveScope;
use App\Support\Traits\Searchable;
use App\Support\Traits\SerializesDates;
use App\Support\Traits\Sortable;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasActiveScope, HasFactory, Searchable, SerializesDates, SoftDeletes, Sortable;

    public const string ACTION_READ = 'read';

    public const string ACTION_CREATE = 'create';

    public const string ACTION_UPDATE = 'update';

    public const string ACTION_DELETE = 'delete';

    public const array ACTIONS = [self::ACTION_READ, self::ACTION_CREATE, self::ACTION_UPDATE, self::ACTION_DELETE];

    public const string WILDCARD = '*';

    public const string SUPER_ADMIN = 'super-admin';

    protected $activeColumn = 'is_active';

    protected array $searchable = ['name', 'display_name'];

    protected array $sortable = ['name', 'display_name', 'is_active', 'created_at'];

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_active',
        'read',
        'create',
        'update',
        'delete',
        'extra',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'read' => 'array',
            'create' => 'array',
            'update' => 'array',
            'delete' => 'array',
            'extra' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->name === self::SUPER_ADMIN
            || in_array(self::WILDCARD, (array) $this->read, true);
    }

    public function canAccess(string $action, int|string $module): bool
    {
        if (! in_array($action, self::ACTIONS, true)) {
            return false;
        }

        $list = (array) ($this->{$action} ?? []);
        if (empty($list)) {
            return false;
        }

        if (in_array(self::WILDCARD, $list, true)) {
            return true;
        }

        $id = is_int($module) ? $module : app(ModuleRegistry::class)->resolveId($module);
        if ($id === null) {
            return false;
        }

        return in_array($id, array_map('intval', $list), true);
    }

    public function canExtra(string $moduleName, string $action): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $extra = (array) ($this->extra ?? []);
        $actions = (array) ($extra[$moduleName] ?? []);

        return in_array($action, $actions, true) || in_array(self::WILDCARD, $actions, true);
    }
}
