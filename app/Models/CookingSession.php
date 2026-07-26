<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property string $public_id */
class CookingSession extends Model
{
    use HasPublicId;

    protected $fillable = ['user_id', 'recipe_id', 'current_step_id', 'servings_snapshot', 'status', 'started_at', 'completed_at', 'last_activity_at'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'completed_at' => 'datetime', 'last_activity_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
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

    /** @return BelongsTo<RecipeStep, $this> */
    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(RecipeStep::class, 'current_step_id');
    }

    /** @return BelongsToMany<RecipeStep, $this> */
    public function completedSteps(): BelongsToMany
    {
        return $this->belongsToMany(RecipeStep::class, 'cooking_session_steps')->withPivot('completed_at')->withTimestamps();
    }

    /** @return HasMany<CookingTimer, $this> */
    public function timers(): HasMany
    {
        return $this->hasMany(CookingTimer::class);
    }
}
