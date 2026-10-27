<?php

namespace App\Actions\Accounts;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SaveRole
{
    public function handle(array $data, User $actor, ?Role $role = null): Role
    {
        return DB::transaction(function () use ($data, $actor, $role): Role {
            if ($role) {
                $role = Role::query()->lockForUpdate()->findOrFail($role->id);
                abort_if($role->isProtected(), 403);
            }
            abort_unless($actor->fresh()?->hasPermission('roles.manage'), 403);
            $data['permissions'] = array_values($data['permissions']);
            sort($data['permissions']);
            if (! $role) {
                $role = new Role;
                $role->slug = 'role-'.Str::uuid();
            }
            $before = $role->permissions ?? [];
            sort($before);
            $changed = $role->exists && $before !== $data['permissions'];
            $role->fill($data);
            $role->save();
            if ($changed) {
                $role->users()->update(['session_version' => DB::raw('session_version + 1'), 'remember_token' => null, 'updated_at' => now()]);
            }

            return $role;
        });
    }
}
