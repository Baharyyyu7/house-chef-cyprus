<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AiShoppingList extends Model
{
    use HasPublicId;

    protected $fillable = ['user_id', 'ai_meal_plan_id', 'ai_interaction_id', 'items', 'status'];

    protected function casts(): array
    {
        return ['items' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(AiMealPlan::class, 'ai_meal_plan_id');
    }

    public function interaction(): BelongsTo
    {
        return $this->belongsTo(AiInteraction::class, 'ai_interaction_id');
    }
}
