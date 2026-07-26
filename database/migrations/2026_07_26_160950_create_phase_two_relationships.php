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
        Schema::create('recipe_allergens', function (Blueprint $table) {
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('allergen_id')->constrained()->cascadeOnDelete();
            $table->string('risk_type', 30)->default('contains');
            $table->string('derived_from')->nullable();
            $table->primary(['recipe_id', 'allergen_id']);
        });
        Schema::create('recipe_dietary_tag', function (Blueprint $table) {
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dietary_tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['recipe_id', 'dietary_tag_id']);
        });
        Schema::create('ingredient_allergens', function (Blueprint $table) {
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('allergen_id')->constrained()->cascadeOnDelete();
            $table->string('risk_type', 30)->default('contains');
            $table->primary(['ingredient_id', 'allergen_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_allergens');
        Schema::dropIfExists('recipe_dietary_tag');
        Schema::dropIfExists('recipe_allergens');
    }
};
