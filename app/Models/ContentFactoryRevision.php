<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentFactoryRevision extends Model
{
    use HasPublicId;

    protected $fillable = ['content_factory_issue_id', 'issue_public_id', 'entity_type', 'entity_public_id', 'field_name', 'before_value', 'proposed_value', 'reason', 'source_evidence', 'evidence_reference_ids', 'created_by_user_id', 'status', 'conflict_status', 'current_value_hash', 'before_value_hash', 'audit_metadata', 'approved_by_user_id', 'approved_at', 'applied_by_user_id', 'applied_at', 'rolled_back_by_user_id', 'rolled_back_at'];

    protected function casts(): array
    {
        return ['before_value' => 'array', 'proposed_value' => 'array', 'source_evidence' => 'array', 'evidence_reference_ids' => 'array', 'audit_metadata' => 'array', 'approved_at' => 'datetime', 'applied_at' => 'datetime', 'rolled_back_at' => 'datetime'];
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
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
