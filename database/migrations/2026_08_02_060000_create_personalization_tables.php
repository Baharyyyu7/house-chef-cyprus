<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personalization_profiles', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('personalization_enabled')->default(true);
            $table->boolean('onboarding_completed')->default(false);
            $table->unsignedInteger('profile_version')->default(1);
            $table->unsignedTinyInteger('household_size')->nullable();
            $table->unsignedTinyInteger('adults')->nullable();
            $table->unsignedTinyInteger('children')->nullable();
            $table->unsignedTinyInteger('default_portions')->nullable();
            $table->unsignedTinyInteger('weekly_cooking_days')->nullable();
            $table->unsignedSmallInteger('preferred_duration_max')->nullable();
            $table->string('preferred_difficulty', 32)->nullable();
            $table->string('preferred_budget_level', 32)->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('last_recalculated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('personalization_preferences', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('personalization_profile_id')->constrained()->cascadeOnDelete();
            $table->string('type', 64);
            $table->string('value', 191);
            $table->string('source', 48)->default('explicitly_selected');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['personalization_profile_id', 'type', 'value'], 'personalization_preference_unique');
            $table->index(['personalization_profile_id', 'type', 'is_active']);
        });

        Schema::create('personalized_recommendations', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->string('score_version', 32);
            $table->json('reason_codes');
            $table->json('soft_factors')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamp('generated_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'recipe_id', 'score_version'], 'personalized_recommendation_unique');
            $table->index(['user_id', 'status', 'expires_at']);
        });

        Schema::create('personalization_feedback', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('personalized_recommendation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 64);
            $table->text('note')->nullable();
            $table->json('profile_impact')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'type', 'withdrawn_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personalization_feedback');
        Schema::dropIfExists('personalized_recommendations');
        Schema::dropIfExists('personalization_preferences');
        Schema::dropIfExists('personalization_profiles');
    }
};
