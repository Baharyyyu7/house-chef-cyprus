<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $public_id
 * @property array<string, string>|null $thumbnail_paths
 */
class Media extends Model
{
    use HasPublicId;

    protected $fillable = ['kind', 'disk', 'path', 'embed_url', 'mime_type', 'size', 'sort_order', 'visibility', 'alt_text', 'width', 'height', 'processing_status', 'thumbnail_status', 'thumbnail_paths', 'processing_error_code', 'processing_error_message', 'processed_at', 'source_name', 'source_url', 'license', 'attribution', 'verification_status', 'confidence', 'reviewed_at', 'is_placeholder', 'generated_by_ai', 'ai_provider', 'ai_model', 'ai_prompt_version', 'ai_review_status', 'focal_point_x', 'focal_point_y', 'checksum_sha256', 'perceptual_hash', 'approved_by_user_id', 'approved_at', 'provider_metadata'];

    protected function casts(): array
    {
        return ['thumbnail_paths' => 'array', 'processed_at' => 'datetime', 'reviewed_at' => 'datetime', 'is_placeholder' => 'boolean', 'generated_by_ai' => 'boolean', 'approved_at' => 'datetime', 'provider_metadata' => 'array'];
    }

    /** @return MorphTo<Model, $this> */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
