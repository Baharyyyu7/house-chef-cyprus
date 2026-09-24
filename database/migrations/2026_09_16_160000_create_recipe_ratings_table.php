<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_ratings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            // Ziyaretçi kimliği: uzun ömürlü çerezdeki uuid + IP'nin HMAC karması.
            // Ham IP asla saklanmaz; aynı ziyaretçinin ikinci oyu güncelleme sayılır.
            $table->string('voter_key', 36);
            $table->string('ip_hash', 64);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['recipe_id', 'voter_key'], 'recipe_ratings_recipe_voter_uq');
            $table->index(['recipe_id'], 'recipe_ratings_recipe_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ratings');
    }
};
