<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeProvenance extends Model
{
    use HasPublicId;

    protected $fillable = ['recipe_id', 'data_source_id', 'content_source_id', 'field_name', 'source_type', 'license', 'source_url', 'content_hash', 'retrieved_at', 'raw_payload', 'is_ai_generated', 'is_user_generated', 'verification_status', 'confidence', 'reviewed_at', 'reviewed_by_user_id', 'last_checked_at', 'is_active'];

    protected function casts(): array
    {
        return ['retrieved_at' => 'datetime', 'raw_payload' => 'array', 'is_ai_generated' => 'boolean', 'is_user_generated' => 'boolean', 'reviewed_at' => 'datetime', 'last_checked_at' => 'datetime', 'is_active' => 'boolean'];
    }

    /** @return BelongsTo<Recipe, $this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /** @return BelongsTo<ContentSource, $this> */
    public function contentSource(): BelongsTo
    {
        return $this->belongsTo(ContentSource::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
