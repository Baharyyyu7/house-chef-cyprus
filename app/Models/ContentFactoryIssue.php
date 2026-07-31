<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentFactoryIssue extends Model
{
    use HasPublicId;

    protected $fillable = ['content_factory_batch_id', 'entity_type', 'entity_public_id', 'fingerprint', 'group_key', 'issue_type', 'category', 'field_name', 'claim', 'risk_level', 'risk_domain', 'source_requirement', 'confidence_score', 'priority_score', 'resolution_mode', 'status', 'reviewed_by_user_id', 'resolution_note', 'evidence_pack', 'resolved_at', 'triaged_at'];

    protected function casts(): array
    {
        return ['evidence_pack' => 'array', 'resolved_at' => 'datetime', 'triaged_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /** @return BelongsTo<ContentFactoryBatch, $this> */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ContentFactoryBatch::class, 'content_factory_batch_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
