<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reparations', function (Blueprint $table) {
           $table->id();
        $table->foreignId('client_id')->constrained()->onDelete('cascade');
        $table->string('code')->unique(); // Code auto-généré
        $table->string('marque');
        $table->string('modele');
        $table->text('probleme');
        $table->enum('statut', ['en_attente', 'en_cours', 'termine'])->default('en_attente');
        $table->date('date_reception')->default(now());
        $table->date('date_depot'); 
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reparations');
    }
};
