<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PersonalizationProfile extends Model
{
    use HasPublicId;

    protected $fillable = ['user_id', 'personalization_enabled', 'onboarding_completed', 'profile_version', 'household_size', 'adults', 'children', 'default_portions', 'weekly_cooking_days', 'preferred_duration_max', 'preferred_difficulty', 'preferred_budget_level', 'settings', 'last_recalculated_at'];

    protected function casts(): array
    {
        return ['personalization_enabled' => 'boolean', 'onboarding_completed' => 'boolean', 'settings' => 'array', 'last_recalculated_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<PersonalizationPreference, $this> */
    public function preferences(): HasMany
    {
        return $this->hasMany(PersonalizationPreference::class);
    }
}
