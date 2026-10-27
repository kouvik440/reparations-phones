<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparations', function (Blueprint $table): void {
            $table->enum('statut', ['en_attente', 'en_cours', 'termine', 'restitue'])->default('en_attente')->change();
            $table->unsignedInteger('status_version')->default(0);
        });
        Schema::create('repair_status_changes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reparation_id')->constrained('reparations')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_name');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->unsignedInteger('version');
            $table->timestamp('created_at');
            $table->unique(['reparation_id', 'version']);
        });
    }

    public function down(): void
    {
        if (DB::table('reparations')->where('statut', 'restitue')->exists()) {
            throw new RuntimeException('Des appareils ont été restitués : retour arrière refusé pour préserver leurs statuts.');
        }
        Schema::dropIfExists('repair_status_changes');
        Schema::table('reparations', function (Blueprint $table): void {
            $table->dropColumn('status_version');
            $table->enum('statut', ['en_attente', 'en_cours', 'termine'])->default('en_attente')->change();
        });
    }
};
