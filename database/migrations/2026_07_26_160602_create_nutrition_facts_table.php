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
        Schema::create('nutrition_facts', function (Blueprint $table) {
            $table->id();
            $table->morphs('nutritionable');
            $table->foreignId('nutrient_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 14, 4);
            $table->string('basis_type', 20)->default('per_100g');
            $table->decimal('basis_amount', 12, 3)->default(100);
            $table->string('source_type', 30)->default('calculated');
            $table->unsignedTinyInteger('confidence_score')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrition_facts');
    }
};
