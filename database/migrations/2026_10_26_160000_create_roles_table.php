<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('permissions');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
        $all = array_keys(config('permissions'));
        foreach ([
            ['slug' => 'admin', 'name' => 'Administrateur', 'description' => 'Accès complet. Rôle système protégé.', 'permissions' => $all],
            ['slug' => 'user', 'name' => 'Utilisateur', 'description' => 'Accès opérationnel historique aux clients et réparations.', 'permissions' => ['dashboard.view', 'clients.view', 'clients.create', 'repairs.view', 'repairs.create', 'repairs.change_status', 'repairs.set_price']],
        ] as $role) {
            $role['permissions'] = json_encode($role['permissions']);
            DB::table('roles')->insert([...$role, 'is_system' => true, 'created_at' => now(), 'updated_at' => now()]);
        }
        // Conserver les anciens rôles éventuels sans leur donner de nouveaux accès.
        foreach (DB::table('users')->distinct()->pluck('role') as $legacy) {
            if (! DB::table('roles')->where('slug', $legacy)->exists()) {
                DB::table('roles')->insert(['name' => 'Ancien rôle : '.$legacy, 'slug' => $legacy, 'permissions' => '[]', 'is_system' => false, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('role')->references('slug')->on('roles')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['role']);
        });
        Schema::dropIfExists('roles');
    }
};
