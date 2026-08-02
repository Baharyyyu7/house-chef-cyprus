<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_media_candidates', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32)->default('pexels');
            $table->string('external_photo_id', 64);
            $table->unsignedTinyInteger('review_batch')->nullable();
            $table->unsignedTinyInteger('rank')->nullable();
            $table->unsignedTinyInteger('review_score')->default(0);
            $table->json('score_breakdown')->nullable();
            $table->json('review_warnings')->nullable();
            $table->string('status', 32)->default('pending_review');
            $table->string('rejection_code', 64)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('photographer')->nullable();
            $table->string('photographer_url')->nullable();
            $table->string('pexels_page_url')->nullable();
            $table->string('source_url')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->json('provider_metadata')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamp('selected_at')->nullable();
            $table->foreignId('selected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->timestamps();
            $table->unique(['recipe_id', 'provider', 'external_photo_id'], 'rmc_recipe_provider_photo_uq');
            $table->index(['review_batch', 'status']);
            $table->index(['provider', 'external_photo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_media_candidates');
    }
};
