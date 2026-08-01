<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

final class ContentQualityProfile extends Model
{
    use HasPublicId;

    public const RULE_VERSION = 'production-readiness-v1';

    public const STATUSES = [
        'not_started', 'incomplete', 'needs_revision', 'source_required', 'in_review', 'blocked',
        'passed_automated_checks', 'ready_for_human_review', 'human_approved', 'published', 'rejected', 'archived',
    ];

    protected $fillable = [
        'entity_type', 'entity_public_id', 'content_type', 'graduation_stage', 'content_quality_status', 'technical_quality_status',
        'editorial_quality_status', 'fact_check_status', 'source_coverage_status', 'measurement_status',
        'nutrition_status', 'allergen_status', 'dietary_status', 'media_status', 'seo_status',
        'accessibility_status', 'final_approval_status', 'publication_status', 'readiness_score', 'readiness_cap',
        'rule_version', 'last_scanned_at', 'human_approved_by_user_id', 'human_approved_at', 'published_by_user_id', 'blocking_reasons', 'metadata',
    ];

    protected function casts(): array
    {
        return ['last_scanned_at' => 'datetime', 'human_approved_at' => 'datetime', 'blocking_reasons' => 'array', 'metadata' => 'array'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
