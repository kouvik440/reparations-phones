<?php

namespace App\Actions\Accounts;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateAccount
{
    public function handle(User $target, array $changes, User $actor): User
    {
        return DB::transaction(function () use ($target, $changes, $actor): User {
            // Même ordre de verrouillage pour toutes les modifications de comptes.
            $accounts = User::query()->with('roleDefinition')->orderBy('id')->lockForUpdate()->get();
            $operator = $accounts->firstWhere('id', $actor->id);
            abort_unless($operator?->hasPermission('users.manage'), 403);
            $user = $accounts->firstWhere('id', $target->id);
            abort_unless($user, 404);
            $active = $changes['is_active'] ?? $user->is_active;
            $role = $changes['role'] ?? $user->role;
            $definition = \App\Models\Role::where('slug', $role)->firstOrFail();
            abort_unless($operator->canManageAccount($user) && $operator->canAssignRole($definition), 403);
            if (! $active && $actor->id === $user->id) {
                throw ValidationException::withMessages(['account' => 'Vous ne pouvez pas désactiver votre propre compte.']);
            }
            if ($user->is_active && $user->isAdmin() && (! $active || $role !== 'admin')
                && $accounts->where('role', 'admin')->where('is_active', true)->count() <= 1) {
                throw ValidationException::withMessages(['role' => 'Conservez au moins un administrateur actif.']);
            }
            if (empty($changes['password'])) {
                unset($changes['password']);
            }
            $user->fill($changes);
            if ($user->isDirty(['email', 'role', 'password', 'is_active'])) {
                $user->session_version++;
                $user->remember_token = Str::random(60);
            }
            $user->save();

            return $user;
        });
    }
}
