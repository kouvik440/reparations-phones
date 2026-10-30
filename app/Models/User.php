<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Les attributs pouvant être remplis.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * Les attributs cachés lors de la sérialisation.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les attributs castés automatiquement.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'session_version' => 'integer',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Vérifie si l'utilisateur est un administrateur.
     */
    public function roleDefinition(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->is_active || ! array_key_exists($permission, config('permissions'))) {
            return false;
        }

        return $this->isAdmin() || in_array($permission, $this->roleDefinition?->permissions ?? [], true);
    }

    public function canAssignRole(Role $role): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        if ($role->isProtected()) {
            return false;
        }
        foreach ($role->permissions as $permission) {
            if (! $this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    public function canManageAccount(User $target): bool
    {
        return $this->hasPermission('users.manage') && ($this->isAdmin()
            || ($target->roleDefinition && $this->canAssignRole($target->roleDefinition)));
    }

    public function landingRoute(): string
    {
        if ($this->isAdmin()) {
            return 'dashboard';
        }
        foreach (['repairs.view' => 'reparations.index', 'dashboard.view' => 'dashboard', 'clients.view' => 'clients.index', 'users.manage' => 'users.index', 'roles.manage' => 'roles.index', 'catalog.manage' => 'catalog.index'] as $permission => $route) {
            if ($this->hasPermission($permission)) {
                return $route;
            }
        }

        return 'access.pending';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
