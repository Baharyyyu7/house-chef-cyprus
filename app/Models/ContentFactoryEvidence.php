<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentFactoryEvidence extends Model
{
    use HasPublicId;

    protected $fillable = ['content_factory_issue_id', 'title', 'publisher', 'url', 'published_at', 'accessed_at', 'source_type', 'trust_level', 'supported_claim', 'confidence_score', 'conflicting_evidence', 'freshness_requirement', 'review_status', 'summary', 'usage_note'];

    protected function casts(): array
    {
        return ['published_at' => 'date', 'accessed_at' => 'datetime'];
    }

    /** @return BelongsTo<ContentFactoryIssue, $this> */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(ContentFactoryIssue::class, 'content_factory_issue_id');
    }
}
