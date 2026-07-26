<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recipe_provenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('data_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('field_name')->nullable();
            $table->string('source_type', 30);
            $table->string('license')->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->timestamp('retrieved_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->boolean('is_ai_generated')->default(false);
            $table->boolean('is_user_generated')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_provenances');
    }
};
