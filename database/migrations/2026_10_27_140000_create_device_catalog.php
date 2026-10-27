<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_brands', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('normalized_name')->unique();
            $table->timestamps();
        });
        Schema::create('device_models', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('device_brand_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('normalized_name');
            $table->timestamps();
            $table->unique(['device_brand_id', 'normalized_name']);
        });
        Schema::table('reparations', function (Blueprint $table): void {
            $table->foreignId('device_brand_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('device_model_id')->nullable()->constrained()->restrictOnDelete();
        });
        // Reuse the actual names without rewriting historical repair labels.
        DB::table('reparations')->orderBy('id')->chunkById(200, function ($repairs): void {
            foreach ($repairs as $repair) {
                $brandName = preg_replace('/\s+/u', ' ', trim($repair->marque));
                $modelName = preg_replace('/\s+/u', ' ', trim($repair->modele));
                if ($brandName === '' || $modelName === '') {
                    continue;
                }
                $brandKey = mb_strtolower($brandName);
                DB::table('device_brands')->insertOrIgnore(['name' => $brandName, 'normalized_name' => $brandKey, 'created_at' => now(), 'updated_at' => now()]);
                $brandId = DB::table('device_brands')->where('normalized_name', $brandKey)->value('id');
                $modelKey = mb_strtolower($modelName);
                DB::table('device_models')->insertOrIgnore(['device_brand_id' => $brandId, 'name' => $modelName, 'normalized_name' => $modelKey, 'created_at' => now(), 'updated_at' => now()]);
                $modelId = DB::table('device_models')->where('device_brand_id', $brandId)->where('normalized_name', $modelKey)->value('id');
                DB::table('reparations')->where('id', $repair->id)->update(['device_brand_id' => $brandId, 'device_model_id' => $modelId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('reparations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('device_model_id');
            $table->dropConstrainedForeignId('device_brand_id');
        });
        Schema::dropIfExists('device_models');
        Schema::dropIfExists('device_brands');
    }
};
