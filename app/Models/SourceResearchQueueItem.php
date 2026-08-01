<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SourceResearchQueueItem extends Model
{
    use HasPublicId;

    protected $fillable = ['content_factory_issue_id', 'claim', 'risk_level', 'risk_domain', 'required_source_type', 'suggested_search_terms', 'candidate_source_title', 'publisher', 'url', 'publication_date', 'accessed_at', 'source_type', 'evidence_summary', 'supported_claim', 'conflicting_evidence', 'confidence_score', 'freshness_requirement', 'copyright_safe_notes', 'reviewer_status', 'assigned_reviewer_id'];

    protected function casts(): array
    {
        return ['suggested_search_terms' => 'array', 'publication_date' => 'date', 'accessed_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /** @return BelongsTo<ContentFactoryIssue, $this> */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(ContentFactoryIssue::class, 'content_factory_issue_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_reviewer_id');
    }
}
