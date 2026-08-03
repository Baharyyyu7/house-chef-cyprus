<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_collections', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique('rc_public_id_uq');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('description', 280)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'name'], 'rc_user_name_uq');
        });

        Schema::create('recipe_collection_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_collection_id')->constrained('recipe_collections')->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['recipe_collection_id', 'recipe_id'], 'rci_collection_recipe_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_collection_items');
        Schema::dropIfExists('recipe_collections');
    }
};
