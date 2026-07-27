<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_sources', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('entity_type', 120)->index();
            $table->ulid('entity_public_id')->nullable()->index();
            $table->string('source_type', 32)->index();
            $table->string('source_name');
            $table->string('source_url', 2048)->nullable();
            $table->string('source_title')->nullable();
            $table->string('publisher')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('accessed_at')->nullable();
            $table->string('license')->nullable();
            $table->boolean('attribution_required')->default(false);
            $table->string('language', 12)->default('tr');
            $table->string('country', 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('verification_status', 24)->default('unreviewed')->index();
            $table->unsignedTinyInteger('confidence')->default(0);
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['entity_type', 'entity_public_id', 'is_active'], 'content_sources_entity_active_index');
        });

        Schema::table('recipes', function (Blueprint $table): void {
            $table->string('verification_status', 24)->default('needs_review')->index();
            $table->string('editorial_status', 24)->default('draft')->index();
            $table->unsignedTinyInteger('completeness_score')->default(0);
            $table->string('completeness_status', 24)->default('incomplete')->index();
            $table->timestamp('editorial_reviewed_at')->nullable();
            $table->foreignId('editorial_reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->json('equipment')->nullable();
            $table->json('tips')->nullable();
            $table->json('common_mistakes')->nullable();
            $table->text('serving_suggestions')->nullable();
            $table->text('reheating_notes')->nullable();
            $table->text('freezing_notes')->nullable();
            $table->text('cultural_notes')->nullable();
            $table->json('seasonality')->nullable();
            $table->string('image_status', 24)->default('missing')->index();
            $table->string('image_license')->nullable();
            $table->string('image_attribution')->nullable();
            $table->timestamp('image_reviewed_at')->nullable();
        });

        Schema::table('recipe_provenances', function (Blueprint $table): void {
            $table->ulid('public_id')->nullable()->unique();
            $table->foreignId('content_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('verification_status', 24)->default('unreviewed')->index();
            $table->unsignedTinyInteger('confidence')->default(0);
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::table('media', function (Blueprint $table): void {
            $table->string('source_name')->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->string('license')->nullable();
            $table->string('attribution')->nullable();
            $table->string('verification_status', 24)->default('unreviewed')->index();
            $table->unsignedTinyInteger('confidence')->default(0);
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('is_placeholder')->default(false)->index();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropColumn(['source_name', 'source_url', 'license', 'attribution', 'verification_status', 'confidence', 'reviewed_at', 'is_placeholder']);
        });
        Schema::table('recipe_provenances', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('content_source_id');
            $table->dropConstrainedForeignId('reviewed_by_user_id');
            $table->dropColumn(['public_id', 'verification_status', 'confidence', 'reviewed_at', 'last_checked_at', 'is_active']);
        });
        Schema::table('recipes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('editorial_reviewed_by_user_id');
            $table->dropColumn(['verification_status', 'editorial_status', 'completeness_score', 'completeness_status', 'editorial_reviewed_at', 'published_at', 'equipment', 'tips', 'common_mistakes', 'serving_suggestions', 'reheating_notes', 'freezing_notes', 'cultural_notes', 'seasonality', 'image_status', 'image_license', 'image_attribution', 'image_reviewed_at']);
        });
        Schema::dropIfExists('content_sources');
    }
};
