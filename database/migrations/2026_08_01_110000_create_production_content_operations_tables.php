<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_quality_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('entity_type', 120);
            $table->string('entity_public_id', 26);
            $table->string('content_type', 20)->index();
            $table->string('content_quality_status', 40)->default('not_started')->index();
            $table->string('technical_quality_status', 40)->default('not_started')->index();
            $table->string('editorial_quality_status', 40)->default('not_started')->index();
            $table->string('fact_check_status', 40)->default('not_started')->index();
            $table->string('source_coverage_status', 40)->default('not_started')->index();
            $table->string('measurement_status', 40)->default('not_started')->index();
            $table->string('nutrition_status', 40)->default('not_started')->index();
            $table->string('allergen_status', 40)->default('not_started')->index();
            $table->string('dietary_status', 40)->default('not_started')->index();
            $table->string('media_status', 40)->default('needs_real_media')->index();
            $table->string('seo_status', 40)->default('not_started')->index();
            $table->string('accessibility_status', 40)->default('not_started')->index();
            $table->string('final_approval_status', 40)->default('not_started')->index();
            $table->string('publication_status', 40)->default('draft')->index();
            $table->unsignedTinyInteger('readiness_score')->default(0)->index();
            $table->unsignedTinyInteger('readiness_cap')->default(100);
            $table->string('rule_version', 40);
            $table->timestamp('last_scanned_at')->nullable()->index();
            $table->json('blocking_reasons')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['entity_type', 'entity_public_id'], 'content_quality_profile_entity_unique');
        });

        Schema::create('production_release_states', function (Blueprint $table): void {
            $table->id();
            $table->string('state', 30)->default('local_qa')->index();
            $table->string('last_ci_commit', 40)->nullable();
            $table->timestamp('last_ci_at')->nullable();
            $table->timestamp('last_smoke_at')->nullable();
            $table->timestamp('last_backup_at')->nullable();
            $table->timestamp('last_migration_at')->nullable();
            $table->timestamp('last_content_health_scan_at')->nullable();
            $table->json('blockers')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_release_states');
        Schema::dropIfExists('content_quality_profiles');
    }
};
