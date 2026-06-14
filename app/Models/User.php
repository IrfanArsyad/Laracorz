<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\Searchable;
use App\Support\Traits\Sortable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, Searchable, SoftDeletes, Sortable;

    public const string STATUS_ACTIVE = 'active';

    public const string STATUS_INACTIVE = 'inactive';

    public const string STATUS_BANNED = 'banned';

    protected array $searchable = ['name', 'email', 'username'];

    protected array $sortable = ['name', 'email', 'username', 'status', 'last_login_at', 'created_at'];

    protected $fillable = [
        'role_id',
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'status',
        'last_login_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPermission(string $action, int|string $module): bool
    {
        return $this->role?->canAccess($action, $module) ?? false;
    }

    public function canExtra(string $moduleName, string $action): bool
    {
        return $this->role?->canExtra($moduleName, $action) ?? false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->isSuperAdmin() ?? false;
    }

    public function avatarUrl(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return Storage::disk('public')->url($this->avatar);
    }
}
