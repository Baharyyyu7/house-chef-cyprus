<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->index(['status', 'is_featured', 'created_at'], 'recipes_listing_index');
            $table->index(['status', 'cuisine_id'], 'recipes_status_cuisine_index');
            $table->index(['status', 'category_id'], 'recipes_status_category_index');
            $table->index(['status', 'difficulty'], 'recipes_status_difficulty_index');
        });
        Schema::table('recipe_ingredients', fn (Blueprint $table) => $table->index(['ingredient_id', 'recipe_id'], 'recipe_ingredients_ingredient_recipe_index'));
        Schema::table('pantry_items', fn (Blueprint $table) => $table->index(['user_id', 'is_available', 'expires_at'], 'pantry_available_expiry_index'));
        Schema::table('ingredient_substitutions', fn (Blueprint $table) => $table->index(['ingredient_id', 'is_active'], 'substitutions_active_lookup_index'));
        Schema::table('ingredients', fn (Blueprint $table) => $table->index('canonical_name', 'ingredients_canonical_name_index'));
        Schema::table('nutrition_facts', fn (Blueprint $table) => $table->index(['nutritionable_type', 'nutritionable_id', 'nutrient_id', 'basis_type'], 'nutrition_facts_lookup_index'));
        Schema::table('recipe_nutrition_snapshots', fn (Blueprint $table) => $table->index(['recipe_id', 'invalidated_at'], 'nutrition_snapshots_active_index'));
        Schema::table('ingredient_allergen_details', fn (Blueprint $table) => $table->index(['ingredient_id', 'is_active'], 'ingredient_allergens_active_index'));
        // Three 255-character utf8mb4 columns remain within MySQL/InnoDB's
        // 3072-byte key limit. The issue_type/entity lookup is covered by the
        // dedicated index added by the lifecycle migration.
        Schema::table('data_quality_issues', fn (Blueprint $table) => $table->index(['resolution_status', 'entity_type', 'entity_public_id'], 'quality_issue_open_lookup_index'));
        Schema::table('jobs', fn (Blueprint $table) => $table->index(['queue', 'reserved_at'], 'jobs_queue_reserved_index'));
        Schema::table('failed_jobs', fn (Blueprint $table) => $table->index('failed_at', 'failed_jobs_failed_at_index'));
        Schema::table('audit_logs', fn (Blueprint $table) => $table->index(['user_id', 'created_at'], 'audit_user_created_index'));
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->dropIndex('recipes_listing_index');
            $table->dropIndex('recipes_status_cuisine_index');
            $table->dropIndex('recipes_status_category_index');
            $table->dropIndex('recipes_status_difficulty_index');
        });
        foreach ([
            ['recipe_ingredients', 'recipe_ingredients_ingredient_recipe_index'], ['pantry_items', 'pantry_available_expiry_index'],
            ['ingredient_substitutions', 'substitutions_active_lookup_index'], ['ingredients', 'ingredients_canonical_name_index'],
            ['nutrition_facts', 'nutrition_facts_lookup_index'], ['recipe_nutrition_snapshots', 'nutrition_snapshots_active_index'],
            ['ingredient_allergen_details', 'ingredient_allergens_active_index'], ['data_quality_issues', 'quality_issue_open_lookup_index'],
            ['jobs', 'jobs_queue_reserved_index'], ['failed_jobs', 'failed_jobs_failed_at_index'], ['audit_logs', 'audit_user_created_index'],
        ] as [$tableName, $index]) {
            Schema::table($tableName, fn (Blueprint $table) => $table->dropIndex($index));
        }
    }
};
