<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('ai_image_generations', 'aig_recipe_fk_ix')) {
            Schema::table('ai_image_generations', fn (Blueprint $table) => $table->index('recipe_id', 'aig_recipe_fk_ix'));
        }

        if (Schema::hasIndex('ai_image_generations', 'ai_image_generation_recipe_variation_scope_unique')) {
            Schema::table('ai_image_generations', fn (Blueprint $table) => $table->dropUnique('ai_image_generation_recipe_variation_scope_unique'));
        }

        if (! Schema::hasIndex('ai_image_generations', 'ai_image_generation_recipe_provider_variation_scope_unique')) {
            Schema::table('ai_image_generations', fn (Blueprint $table) => $table->unique(['recipe_id', 'provider', 'variation_number', 'generation_scope'], 'ai_image_generation_recipe_provider_variation_scope_unique'));
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('ai_image_generations', 'ai_image_generation_recipe_provider_variation_scope_unique')) {
            Schema::table('ai_image_generations', fn (Blueprint $table) => $table->dropUnique('ai_image_generation_recipe_provider_variation_scope_unique'));
        }

        if (! Schema::hasIndex('ai_image_generations', 'ai_image_generation_recipe_variation_scope_unique')) {
            Schema::table('ai_image_generations', fn (Blueprint $table) => $table->unique(['recipe_id', 'variation_number', 'generation_scope'], 'ai_image_generation_recipe_variation_scope_unique'));
        }
    }
};
