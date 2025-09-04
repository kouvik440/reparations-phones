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
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); // ID auto-incrémenté
        $table->string('prenom'); // Prénom du client
        $table->string('nom'); // Nom
        $table->string('telephone')->unique(); // Téléphone (unique)
        $table->string('piece_identite'); // CIN / CNI / Passeport
        $table->timestamps(); // created_at / updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
