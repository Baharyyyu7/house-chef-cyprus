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
        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->ulid('public_id')->nullable()->after('id');
        });
        DB::table('recipe_ingredients')->whereNull('public_id')->orderBy('id')->eachById(function (object $line): void {
            DB::table('recipe_ingredients')->where('id', $line->id)->update(['public_id' => (string) Str::ulid()]);
        });
        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->unique('public_id');
        });
    }

    public function down(): void
    {
        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });
    }
};
