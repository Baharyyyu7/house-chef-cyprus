<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class EditorialReviewBatch extends Model
{
    use HasPublicId;

    protected $fillable = ['planning_key', 'title', 'category', 'severity_mix', 'risk_mix', 'total_issues', 'source_required_count', 'evidence_ready_count', 'human_review_count', 'duplicate_group_count', 'estimated_review_minutes', 'priority_score', 'status', 'assigned_reviewer_id', 'started_at', 'completed_at', 'progress', 'blocking_reasons'];

    protected function casts(): array
    {
        return ['severity_mix' => 'array', 'risk_mix' => 'array', 'blocking_reasons' => 'array', 'started_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /** @return BelongsToMany<ContentFactoryIssue, $this> */
    public function issues(): BelongsToMany
    {
        return $this->belongsToMany(ContentFactoryIssue::class, 'editorial_review_batch_issue')->withTimestamps();
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_reviewer_id');
    }
}
