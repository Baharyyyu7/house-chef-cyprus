<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property list<string> $reason_codes */
final class PersonalizedRecommendation extends Model
{
    use HasPublicId;

    protected $fillable = ['user_id', 'recipe_id', 'score', 'score_version', 'reason_codes', 'soft_factors', 'status', 'generated_at', 'expires_at'];

    protected function casts(): array
    {
        return ['reason_codes' => 'array', 'soft_factors' => 'array', 'generated_at' => 'datetime', 'expires_at' => 'datetime'];
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
