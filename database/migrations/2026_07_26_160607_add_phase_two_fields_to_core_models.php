<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['users', 'recipes', 'ingredients', 'cuisines', 'categories', 'media'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->ulid('public_id')->nullable()->unique();
            });
        }
        Schema::table('recipe_ingredients', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
        });
        foreach (['users', 'recipes', 'ingredients', 'cuisines', 'categories', 'media'] as $name) {
            DB::table($name)->whereNull('public_id')->orderBy('id')->each(fn ($row) => DB::table($name)->where('id', $row->id)->update(['public_id' => (string) Str::ulid()]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_ingredients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
        });
    }
};
