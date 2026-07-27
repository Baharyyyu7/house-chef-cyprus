<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ContentSource extends Model
{
    use HasPublicId;

    protected $fillable = ['entity_type', 'entity_public_id', 'source_type', 'source_name', 'source_url', 'source_title', 'publisher', 'author', 'published_at', 'accessed_at', 'license', 'attribution_required', 'language', 'country', 'notes', 'verification_status', 'confidence', 'reviewed_at', 'reviewed_by_user_id', 'last_checked_at', 'is_active'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'accessed_at' => 'datetime', 'attribution_required' => 'boolean', 'reviewed_at' => 'datetime', 'last_checked_at' => 'datetime', 'is_active' => 'boolean'];
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /** @return HasMany<RecipeProvenance, $this> */
    public function recipeProvenances(): HasMany
    {
        return $this->hasMany(RecipeProvenance::class);
    }
}
