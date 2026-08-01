<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->string('media_status', 40)->default('no_media')->index()->after('visibility');
            $table->string('fidelity_status', 32)->nullable()->index()->after('media_status');
            $table->unsignedTinyInteger('fidelity_confidence')->nullable()->after('fidelity_status');
            $table->json('fidelity_reasons')->nullable()->after('fidelity_confidence');
            $table->string('license_review_status', 32)->default('pending')->index()->after('license');
            $table->string('attribution_review_status', 32)->default('pending')->index()->after('attribution');
            $table->string('optimization_status', 32)->default('not_requested')->index()->after('thumbnail_status');
            $table->json('responsive_variants')->nullable()->after('thumbnail_paths');
            $table->timestamp('media_reviewed_at')->nullable()->after('reviewed_at');
            $table->foreignId('media_reviewer_user_id')->nullable()->after('approved_by_user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('license_reviewed_at')->nullable()->after('media_reviewed_at');
            $table->timestamp('attribution_reviewed_at')->nullable()->after('license_reviewed_at');
            $table->timestamp('health_scanned_at')->nullable()->after('attribution_reviewed_at');
            $table->string('health_rule_version', 48)->nullable()->after('health_scanned_at');
            $table->index(['mediable_type', 'mediable_id', 'media_status'], 'media_entity_status_index');
            $table->index(['checksum_sha256', 'visibility'], 'media_checksum_visibility_index');
        });

        Schema::table('recipe_media_candidates', function (Blueprint $table): void {
            $table->string('fidelity_status', 32)->nullable()->index()->after('review_score');
            $table->unsignedTinyInteger('fidelity_confidence')->nullable()->after('fidelity_status');
            $table->json('fidelity_reasons')->nullable()->after('fidelity_confidence');
            $table->string('license_status', 32)->default('pending')->index()->after('status');
            $table->string('attribution_status', 32)->default('pending')->index()->after('license_status');
            $table->timestamp('fidelity_scanned_at')->nullable()->after('scanned_at');
        });
    }

    public function down(): void
    {
        Schema::table('recipe_media_candidates', function (Blueprint $table): void {
            $table->dropIndex(['fidelity_status']);
            $table->dropIndex(['license_status']);
            $table->dropIndex(['attribution_status']);
            $table->dropColumn(['fidelity_status', 'fidelity_confidence', 'fidelity_reasons', 'license_status', 'attribution_status', 'fidelity_scanned_at']);
        });
        Schema::table('media', function (Blueprint $table): void {
            $table->dropIndex(['media_status']);
            $table->dropIndex(['fidelity_status']);
            $table->dropIndex(['license_review_status']);
            $table->dropIndex(['attribution_review_status']);
            $table->dropIndex(['optimization_status']);
            $table->dropIndex('media_entity_status_index');
            $table->dropIndex('media_checksum_visibility_index');
            $table->dropColumn(['media_status', 'fidelity_status', 'fidelity_confidence', 'fidelity_reasons', 'license_review_status', 'attribution_review_status', 'optimization_status', 'responsive_variants', 'media_reviewed_at', 'media_reviewer_user_id', 'license_reviewed_at', 'attribution_reviewed_at', 'health_scanned_at', 'health_rule_version']);
        });
    }
};
