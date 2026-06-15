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
        // Fast path: snapshot session yang dibuat saat login. Fallback ke
        // DB hanya kalau snapshot belum ada (mis. session lama, atau
        // request luar konteks user yang sedang login).
        if (app()->bound('session.store') && session()->has(\App\Services\UserSessionService::SESSION_KEY)) {
            $result = app(\App\Services\UserSessionService::class)->hasPermission($action, $module);
            if ($result !== null) {
                return $result;
            }
        }

        return $this->role?->canAccess($action, $module) ?? false;
    }

    public function canExtra(string $moduleName, string $action): bool
    {
        if (app()->bound('session.store') && session()->has(\App\Services\UserSessionService::SESSION_KEY)) {
            $snapshot = app(\App\Services\UserSessionService::class)->get();
            if ($snapshot !== null) {
                if (! empty($snapshot['is_super_admin'])) {
                    return true;
                }
                $entry = collect($snapshot['permissions'] ?? [])->first(fn ($p) => ($p['name'] ?? null) === $moduleName);
                $extra = (array) ($entry['extra'] ?? []);

                return in_array($action, $extra, true) || in_array(\App\Models\Role::WILDCARD, $extra, true);
            }
        }

        return $this->role?->canExtra($moduleName, $action) ?? false;
    }

    public function isSuperAdmin(): bool
    {
        if (app()->bound('session.store') && session()->has(\App\Services\UserSessionService::SESSION_KEY)) {
            $snapshot = app(\App\Services\UserSessionService::class)->get();
            if ($snapshot !== null) {
                return (bool) ($snapshot['is_super_admin'] ?? false);
            }
        }

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
