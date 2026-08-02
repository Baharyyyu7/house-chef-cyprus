<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_image_generations', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 32);
            $table->string('model', 128)->nullable();
            $table->text('prompt');
            $table->text('negative_prompt')->nullable();
            $table->string('prompt_version', 32);
            $table->string('aspect_ratio', 12)->default('4:3');
            $table->unsignedTinyInteger('variation_number')->default(1);
            $table->string('status', 32)->default('planned')->index();
            $table->unsignedInteger('estimated_cost_cents')->default(0);
            $table->unsignedInteger('actual_cost_cents')->nullable();
            $table->string('error_code', 64)->nullable();
            $table->string('error_message', 255)->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['recipe_id', 'variation_number'], 'ai_image_generation_recipe_variation_unique');
            // Keep the recipe foreign key independent from later unique-key
            // revisions in follow-up migrations.
            $table->index('recipe_id', 'aig_recipe_fk_ix');
            $table->index(['provider', 'status']);
        });

        Schema::table('media', function (Blueprint $table): void {
            $table->boolean('generated_by_ai')->default(false)->index();
            $table->string('ai_provider', 32)->nullable();
            $table->string('ai_model', 128)->nullable();
            $table->string('ai_prompt_version', 32)->nullable();
            $table->string('ai_review_status', 24)->nullable()->index();
            $table->unsignedTinyInteger('focal_point_x')->nullable();
            $table->unsignedTinyInteger('focal_point_y')->nullable();
            $table->string('checksum_sha256', 64)->nullable()->index();
            $table->string('perceptual_hash', 64)->nullable()->index();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('provider_metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('approved_by_user_id');
            $table->dropIndex(['generated_by_ai']);
            $table->dropIndex(['ai_review_status']);
            $table->dropIndex(['checksum_sha256']);
            $table->dropIndex(['perceptual_hash']);
            $table->dropColumn(['generated_by_ai', 'ai_provider', 'ai_model', 'ai_prompt_version', 'ai_review_status', 'focal_point_x', 'focal_point_y', 'checksum_sha256', 'perceptual_hash', 'approved_at', 'provider_metadata']);
        });
        Schema::dropIfExists('ai_image_generations');
    }
};
