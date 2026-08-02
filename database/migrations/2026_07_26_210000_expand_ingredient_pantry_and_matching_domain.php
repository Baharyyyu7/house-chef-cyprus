<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $ingredientColumns = Schema::getColumnListing('ingredients');

        Schema::table('ingredients', function (Blueprint $table) use ($ingredientColumns): void {
            if (! in_array('canonical_name', $ingredientColumns, true)) {
                $table->string('canonical_name')->nullable()->after('name');
            }

            if (! in_array('category', $ingredientColumns, true)) {
                $table->string('category')->nullable()->after('slug');
            }

            if (! in_array('default_unit_id', $ingredientColumns, true)) {
                $table->foreignId('default_unit_id')->nullable()->after('default_unit');
                $table->foreign('default_unit_id', 'ing_default_unit_fk')->references('id')->on('units')->nullOnDelete();
            }

            if (! in_array('is_active', $ingredientColumns, true)) {
                $table->boolean('is_active')->default(true);
            }

            if (! in_array('is_allergen', $ingredientColumns, true)) {
                $table->boolean('is_allergen')->default(false);
            }

            if (! in_array('is_perishable', $ingredientColumns, true)) {
                $table->boolean('is_perishable')->default(false);
            }

            if (! in_array('typical_shelf_life_days', $ingredientColumns, true)) {
                $table->unsignedSmallInteger('typical_shelf_life_days')->nullable();
            }

            if (! in_array('storage_type', $ingredientColumns, true)) {
                $table->string('storage_type', 20)->nullable();
            }

            if (! in_array('notes', $ingredientColumns, true)) {
                $table->text('notes')->nullable();
            }
        });

        if (! Schema::hasTable('ingredient_aliases')) {
            Schema::create('ingredient_aliases', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('ingredient_id');
                $table->foreign('ingredient_id', 'ing_alias_ing_fk')->references('id')->on('ingredients')->cascadeOnDelete();
                $table->string('alias');
                $table->string('normalized_alias');
                $table->string('locale', 8)->default('tr');
                $table->string('source_type', 30)->default('editorial');
                $table->decimal('confidence_score', 5, 2)->default(100);
                $table->timestamps();
                $table->unique(['ingredient_id', 'normalized_alias', 'locale'], 'ing_alias_scope_uq');
                $table->index(['normalized_alias', 'locale'], 'ing_alias_lookup_ix');
            });
        }

        if (! Schema::hasTable('ingredient_substitutions')) {
            Schema::create('ingredient_substitutions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('ingredient_id');
                $table->foreign('ingredient_id', 'ing_sub_ing_fk')->references('id')->on('ingredients')->cascadeOnDelete();
                $table->foreignId('substitute_ingredient_id');
                $table->foreign('substitute_ingredient_id', 'ing_sub_sub_ing_fk')->references('id')->on('ingredients')->cascadeOnDelete();
                $table->string('substitution_type', 30);
                $table->decimal('ratio', 10, 3)->default(1);
                $table->string('unit_compatibility', 30)->nullable();
                $table->string('context')->nullable();
                $table->text('notes')->nullable();
                $table->decimal('confidence_score', 5, 2)->default(100);
                $table->boolean('is_verified')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['ingredient_id', 'substitute_ingredient_id', 'substitution_type'], 'ing_sub_type_uq');
            });
        }

        $recipeIngredientColumns = Schema::getColumnListing('recipe_ingredients');

        Schema::table('recipe_ingredients', function (Blueprint $table) use ($recipeIngredientColumns): void {
            if (! in_array('is_required', $recipeIngredientColumns, true)) {
                $table->boolean('is_required')->default(true)->after('is_optional');
            }

            if (! in_array('group_name', $recipeIngredientColumns, true)) {
                $table->string('group_name')->nullable()->after('is_required');
            }

            if (! in_array('preparation_note', $recipeIngredientColumns, true)) {
                $table->string('preparation_note')->nullable()->after('preparation');
            }

            if (! in_array('can_be_substituted', $recipeIngredientColumns, true)) {
                $table->boolean('can_be_substituted')->default(true)->after('substitution');
            }
        });

        // InnoDB can use the original composite unique key to enforce the
        // recipe_id foreign key. Create a dedicated support index before the
        // unique key is replaced so partially applied production migrations
        // never need a manual foreign-key intervention.
        if (! Schema::hasIndex('recipe_ingredients', 'recipe_ing_recipe_fk_ix')) {
            Schema::table('recipe_ingredients', fn (Blueprint $table) => $table->index('recipe_id', 'recipe_ing_recipe_fk_ix'));
        }

        foreach (['recipe_ingredients_recipe_id_ingredient_id_unique', 'recipe_ing_pair_uq'] as $index) {
            if (Schema::hasIndex('recipe_ingredients', $index)) {
                Schema::table('recipe_ingredients', fn (Blueprint $table) => $table->dropUnique($index));
            }
        }

        if (! Schema::hasIndex('recipe_ingredients', 'recipe_ing_sort_ix')) {
            Schema::table('recipe_ingredients', fn (Blueprint $table) => $table->index(['recipe_id', 'sort_order'], 'recipe_ing_sort_ix'));
        }

        $pantryColumns = Schema::getColumnListing('pantry_items');

        Schema::table('pantry_items', function (Blueprint $table) use ($pantryColumns): void {
            if (! in_array('unit_id', $pantryColumns, true)) {
                $table->foreignId('unit_id')->nullable()->after('unit');
                $table->foreign('unit_id', 'pantry_unit_fk')->references('id')->on('units')->nullOnDelete();
            }

            if (! in_array('opened_at', $pantryColumns, true)) {
                $table->timestamp('opened_at')->nullable()->after('expires_at');
            }

            if (! in_array('storage_location', $pantryColumns, true)) {
                $table->string('storage_location', 20)->default('pantry')->after('opened_at');
            }

            if (! in_array('notes', $pantryColumns, true)) {
                $table->text('notes')->nullable()->after('storage_location');
            }

            if (! in_array('is_available', $pantryColumns, true)) {
                $table->boolean('is_available')->default(true)->after('notes');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('recipe_ingredients', 'recipe_ing_sort_ix')) {
            Schema::table('recipe_ingredients', fn (Blueprint $table) => $table->dropIndex('recipe_ing_sort_ix'));
        }

        if (! Schema::hasIndex('recipe_ingredients', 'recipe_ing_pair_uq')) {
            Schema::table('recipe_ingredients', fn (Blueprint $table) => $table->unique(['recipe_id', 'ingredient_id'], 'recipe_ing_pair_uq'));
        }

        if (Schema::hasIndex('recipe_ingredients', 'recipe_ing_recipe_fk_ix')) {
            Schema::table('recipe_ingredients', fn (Blueprint $table) => $table->dropIndex('recipe_ing_recipe_fk_ix'));
        }

        Schema::dropIfExists('ingredient_substitutions');
        Schema::dropIfExists('ingredient_aliases');

        if (Schema::hasColumn('pantry_items', 'unit_id')) {
            $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';
            $hasForeignKey = Schema::hasForeignKey('pantry_items', $isSqlite ? ['unit_id'] : 'pantry_unit_fk');

            Schema::table('pantry_items', function (Blueprint $table) use ($hasForeignKey, $isSqlite): void {
                if ($hasForeignKey) {
                    $table->dropForeign($isSqlite ? ['unit_id'] : 'pantry_unit_fk');
                }

                $table->dropColumn('unit_id');
            });
        }

        $pantryColumns = array_values(array_intersect(
            ['opened_at', 'storage_location', 'notes', 'is_available'],
            Schema::getColumnListing('pantry_items'),
        ));

        if ($pantryColumns !== []) {
            Schema::table('pantry_items', fn (Blueprint $table) => $table->dropColumn($pantryColumns));
        }

        if (Schema::hasColumn('ingredients', 'default_unit_id')) {
            $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';
            $hasForeignKey = Schema::hasForeignKey('ingredients', $isSqlite ? ['default_unit_id'] : 'ing_default_unit_fk');

            Schema::table('ingredients', function (Blueprint $table) use ($hasForeignKey, $isSqlite): void {
                if ($hasForeignKey) {
                    $table->dropForeign($isSqlite ? ['default_unit_id'] : 'ing_default_unit_fk');
                }

                $table->dropColumn('default_unit_id');
            });
        }

        $ingredientColumns = array_values(array_intersect(
            ['canonical_name', 'category', 'is_active', 'is_allergen', 'is_perishable', 'typical_shelf_life_days', 'storage_type', 'notes'],
            Schema::getColumnListing('ingredients'),
        ));

        if ($ingredientColumns !== []) {
            Schema::table('ingredients', fn (Blueprint $table) => $table->dropColumn($ingredientColumns));
        }

        $recipeIngredientColumns = array_values(array_intersect(
            ['is_required', 'group_name', 'preparation_note', 'can_be_substituted'],
            Schema::getColumnListing('recipe_ingredients'),
        ));

        if ($recipeIngredientColumns !== []) {
            Schema::table('recipe_ingredients', fn (Blueprint $table) => $table->dropColumn($recipeIngredientColumns));
        }
    }
};
