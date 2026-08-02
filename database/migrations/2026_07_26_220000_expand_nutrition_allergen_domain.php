<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nutrients', function (Blueprint $table): void {
            $table->unsignedTinyInteger('decimal_precision')->default(2);
            $table->decimal('daily_reference_value', 14, 4)->nullable();
            $table->boolean('is_macro')->default(false);
            $table->boolean('is_micronutrient')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
        });
        Schema::table('nutrition_facts', function (Blueprint $table): void {
            $table->string('source_name')->nullable();
            $table->string('calculation_method')->nullable();
            $table->string('origin', 30)->default('editorial');
            $table->text('notes')->nullable();
            $table->unique(['nutritionable_type', 'nutritionable_id', 'nutrient_id', 'basis_type', 'basis_amount', 'source_type'], 'nutrition_fact_source_unique');
        });
        Schema::create('ingredient_allergen_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('allergen_id')->constrained()->cascadeOnDelete();
            $table->string('relation_type', 30)->default('contains');
            $table->unsignedTinyInteger('confidence_score')->default(100);
            $table->string('source_type', 30)->default('editorial');
            $table->boolean('is_verified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['ingredient_id', 'allergen_id']);
        });
        Schema::create('recipe_nutrition_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('servings');
            $table->json('payload');
            $table->unsignedTinyInteger('completeness_percentage');
            $table->unsignedTinyInteger('confidence_score');
            $table->string('source_version')->default('v1');
            $table->timestamp('calculated_at');
            $table->timestamp('invalidated_at')->nullable();
            $table->timestamps();
            $table->unique(['recipe_id', 'servings', 'source_version'], 'rns_recipe_servings_source_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_nutrition_snapshots');
        Schema::dropIfExists('ingredient_allergen_details');
    }
};
