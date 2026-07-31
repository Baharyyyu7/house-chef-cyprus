<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentFactoryIssue extends Model
{
    use HasPublicId;

    protected $fillable = ['content_factory_batch_id', 'entity_type', 'entity_public_id', 'fingerprint', 'issue_type', 'field_name', 'claim', 'risk_level', 'source_requirement', 'confidence_score', 'status', 'reviewed_by_user_id', 'resolution_note', 'resolved_at'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
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
