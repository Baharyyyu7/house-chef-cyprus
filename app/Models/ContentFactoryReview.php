<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentFactoryReview extends Model
{
    use HasPublicId;

    protected $fillable = ['content_factory_batch_id', 'content_type', 'entity_type', 'entity_public_id', 'stage', 'status', 'fact_check_required', 'findings', 'reviewed_by_user_id', 'reviewed_at'];

    protected function casts(): array
    {
        return ['fact_check_required' => 'boolean', 'findings' => 'array', 'reviewed_at' => 'datetime'];
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
