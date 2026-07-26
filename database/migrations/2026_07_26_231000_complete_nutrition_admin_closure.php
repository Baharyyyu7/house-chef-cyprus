<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredient_allergen_details', function (Blueprint $table): void {
            $table->string('source_name')->nullable();
            $table->boolean('is_active')->default(true);
        });
        Schema::table('recipe_allergens', function (Blueprint $table): void {
            $table->string('source_name')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
        });
        Schema::create('nutrition_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->decimal('value', 14, 4);
            $table->string('unit', 20)->nullable();
            $table->timestamps();
        });
        Schema::create('nutrition_recalculation_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('error_code', 80)->nullable();
            $table->string('error_message', 255)->nullable();
            $table->unsignedTinyInteger('previous_confidence')->nullable();
            $table->unsignedTinyInteger('new_confidence')->nullable();
            $table->unsignedTinyInteger('previous_completeness')->nullable();
            $table->unsignedTinyInteger('new_completeness')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_recalculation_runs');
        Schema::dropIfExists('nutrition_settings');
    }
};
