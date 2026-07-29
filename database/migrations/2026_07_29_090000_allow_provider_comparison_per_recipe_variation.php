<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_image_generations', function (Blueprint $table): void {
            $table->dropUnique('ai_image_generation_recipe_variation_scope_unique');
            $table->unique(['recipe_id', 'provider', 'variation_number', 'generation_scope'], 'ai_image_generation_recipe_provider_variation_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ai_image_generations', function (Blueprint $table): void {
            $table->dropUnique('ai_image_generation_recipe_provider_variation_scope_unique');
            $table->unique(['recipe_id', 'variation_number', 'generation_scope'], 'ai_image_generation_recipe_variation_scope_unique');
        });
    }
};
