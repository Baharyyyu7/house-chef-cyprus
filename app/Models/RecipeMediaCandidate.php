<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RecipeMediaCandidate extends Model
{
    use HasPublicId;

    protected $fillable = [
        'recipe_id', 'provider', 'external_photo_id', 'review_batch', 'rank', 'review_score',
        'score_breakdown', 'review_warnings', 'status', 'rejection_code', 'rejection_reason',
        'photographer', 'photographer_url', 'pexels_page_url', 'source_url', 'alt_text', 'width',
        'height', 'provider_metadata', 'description', 'alt_description', 'external_page_url', 'download_location',
        'small_url', 'thumb_url', 'orientation', 'color', 'blur_hash', 'utm_source', 'utm_medium',
        'download_tracked_at', 'tracking_status', 'tracking_error_code', 'scanned_at', 'selected_at', 'selected_by_user_id', 'media_id',
    ];

    protected function casts(): array
    {
        return [
            'score_breakdown' => 'array', 'review_warnings' => 'array', 'provider_metadata' => 'array',
            'scanned_at' => 'datetime', 'selected_at' => 'datetime', 'download_tracked_at' => 'datetime',
        ];
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
    public function selectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by_user_id');
    }
}
