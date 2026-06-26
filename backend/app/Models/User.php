<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'pin',
        'role_id',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    protected $appends = ['permissions'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function getPermissionsAttribute(): array
    {
        return $this->role?->permissions?->pluck('permission')->toArray() ?? [];
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role?->permissions()
            ->where('permission', $permission)
            ->exists() ?? false;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role?->slug, $roles);
    }
}
