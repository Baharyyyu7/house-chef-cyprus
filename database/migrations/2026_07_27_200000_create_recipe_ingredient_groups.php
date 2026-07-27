<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_ingredient_groups', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['recipe_id', 'name']);
            $table->index(['recipe_id', 'sort_order']);
        });
        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->foreignId('recipe_ingredient_group_id')->nullable()->after('recipe_id')->constrained()->nullOnDelete();
            $table->index(['recipe_ingredient_group_id', 'sort_order']);
        });
        DB::table('recipe_ingredients')->orderBy('recipe_id')->orderBy('sort_order')->get()->groupBy('recipe_id')->each(function ($lines, $recipeId): void {
            $groups = [];
            foreach ($lines as $line) {
                $name = trim((string) ($line->group_name ?: 'Malzemeler'));
                if (! isset($groups[$name])) {
                    $groups[$name] = DB::table('recipe_ingredient_groups')->insertGetId(['public_id' => (string) Str::ulid(), 'recipe_id' => $recipeId, 'name' => $name, 'sort_order' => count($groups) + 1, 'created_at' => now(), 'updated_at' => now()]);
                } DB::table('recipe_ingredients')->where('id', $line->id)->update(['recipe_ingredient_group_id' => $groups[$name]]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->dropForeign(['recipe_ingredient_group_id']);
            $table->dropIndex(['recipe_ingredient_group_id', 'sort_order']);
            $table->dropColumn('recipe_ingredient_group_id');
        });
        Schema::dropIfExists('recipe_ingredient_groups');
    }
};
