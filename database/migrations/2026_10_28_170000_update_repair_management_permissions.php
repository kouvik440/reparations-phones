<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('roles')->whereIn('slug', ['admin', 'user'])->get() as $role) {
            $permissions = json_decode($role->permissions, true) ?? [];
            $permissions = array_values(array_unique([...$permissions, 'repairs.view', 'repairs.edit', 'repairs.delete']));
            DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode($permissions), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('roles')->whereIn('slug', ['admin', 'user'])->get() as $role) {
            DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode(array_values(array_diff(json_decode($role->permissions, true) ?? [], ['repairs.edit', 'repairs.delete'])))]);
        }
    }
};
