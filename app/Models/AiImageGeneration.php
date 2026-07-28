<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AiImageGeneration extends Model
{
    use HasPublicId;

    protected $fillable = ['recipe_id', 'media_id', 'provider', 'generation_scope', 'model', 'prompt', 'negative_prompt', 'prompt_version', 'aspect_ratio', 'variation_number', 'status', 'estimated_cost_cents', 'actual_cost_cents', 'error_code', 'error_message', 'response_status', 'provider_request_id', 'response_content_type', 'response_body_present', 'candidate_count', 'part_count', 'image_part_count', 'text_part_count', 'finish_reason', 'moderation_status', 'request_bytes', 'response_bytes', 'provider_latency_ms', 'billing_status', 'parser_result', 'diagnostic_snapshot', 'status_transitions', 'review_notes', 'requested_at', 'generated_at', 'reviewed_at', 'reviewed_by_user_id'];

    protected function casts(): array
    {
        return ['requested_at' => 'datetime', 'generated_at' => 'datetime', 'reviewed_at' => 'datetime', 'response_body_present' => 'boolean', 'diagnostic_snapshot' => 'array', 'status_transitions' => 'array'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /** @return BelongsTo<Recipe, $this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /** @return BelongsTo<Media, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
