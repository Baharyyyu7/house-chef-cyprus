<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class EditorialReviewPack extends Model
{
    use HasPublicId;

    protected $fillable = ['content_factory_batch_id', 'content_type', 'entity_type', 'entity_public_id', 'snapshot', 'readiness_score', 'blocking_reasons', 'status', 'assigned_reviewer_id', 'reviewed_by_user_id', 'reviewed_at'];

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'blocking_reasons' => 'array', 'reviewed_at' => 'datetime'];
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

    /** @return HasMany<EditorialReviewSection, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(EditorialReviewSection::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_reviewer_id');
    }
}
