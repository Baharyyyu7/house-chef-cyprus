<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property string $public_id */
class CookingTimer extends Model
{
    use HasPublicId;

    protected $fillable = ['cooking_session_id', 'recipe_step_id', 'label', 'duration_seconds', 'remaining_seconds', 'started_at', 'paused_at', 'completed_at', 'status'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'paused_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    /** @return BelongsTo<CookingSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(CookingSession::class, 'cooking_session_id');
    }

    /** @return BelongsTo<RecipeStep, $this> */
    public function step(): BelongsTo
    {
        return $this->belongsTo(RecipeStep::class, 'recipe_step_id');
    }
}
