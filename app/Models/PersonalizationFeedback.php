<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PersonalizationFeedback extends Model
{
    use HasPublicId;

    protected $fillable = ['user_id', 'recipe_id', 'personalized_recommendation_id', 'type', 'note', 'profile_impact', 'processed_at', 'withdrawn_at'];

    protected function casts(): array
    {
        return ['profile_impact' => 'array', 'processed_at' => 'datetime', 'withdrawn_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Recipe, $this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
