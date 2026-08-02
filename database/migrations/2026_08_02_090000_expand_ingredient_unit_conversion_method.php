<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INGREDIENT_CONVERSIONS = 'ingredient_unit_conversions';

    private const RECIPE_INGREDIENTS = 'recipe_ingredients';

    public function up(): void
    {
        if (Schema::hasTable(self::INGREDIENT_CONVERSIONS) && Schema::hasColumn(self::INGREDIENT_CONVERSIONS, 'conversion_method')) {
            Schema::table(self::INGREDIENT_CONVERSIONS, function (Blueprint $table): void {
                $table->string('conversion_method', 64)->default('ingredient_specific')->change();
            });
        }

        if (Schema::hasTable(self::RECIPE_INGREDIENTS) && Schema::hasColumn(self::RECIPE_INGREDIENTS, 'conversion_method')) {
            Schema::table(self::RECIPE_INGREDIENTS, function (Blueprint $table): void {
                $table->string('conversion_method', 64)->default('not_convertible')->change();
            });
        }
    }

    public function down(): void
    {
        foreach ([self::INGREDIENT_CONVERSIONS, self::RECIPE_INGREDIENTS] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'conversion_method')) {
                // This was the only application-owned value exceeding the legacy
                // VARCHAR(30) contract. Keep its meaning while allowing rollback.
                DB::table($table)
                    ->where('conversion_method', 'editorial_canonical_measurement')
                    ->update(['conversion_method' => 'editorial_measurement']);
            }
        }

        if (Schema::hasTable(self::RECIPE_INGREDIENTS) && Schema::hasColumn(self::RECIPE_INGREDIENTS, 'conversion_method')) {
            Schema::table(self::RECIPE_INGREDIENTS, function (Blueprint $table): void {
                $table->string('conversion_method', 30)->default('not_convertible')->change();
            });
        }

        if (Schema::hasTable(self::INGREDIENT_CONVERSIONS) && Schema::hasColumn(self::INGREDIENT_CONVERSIONS, 'conversion_method')) {
            Schema::table(self::INGREDIENT_CONVERSIONS, function (Blueprint $table): void {
                $table->string('conversion_method', 30)->default('ingredient_specific')->change();
            });
        }
    }
};
