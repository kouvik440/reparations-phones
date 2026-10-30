<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparations', fn (Blueprint $table) => $table->date('date_retrait')->nullable());
        Schema::table('repair_status_changes', function (Blueprint $table): void {
            $table->date('date_retrait')->nullable();
            $table->date('previous_date_retrait')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reparations', fn (Blueprint $table) => $table->dropColumn('date_retrait'));
        Schema::table('repair_status_changes', fn (Blueprint $table) => $table->dropColumn(['date_retrait', 'previous_date_retrait']));
    }
};
