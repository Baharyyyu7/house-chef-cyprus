<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property array<int, array{day:int, recipe:array{public_id:string, title:string, slug:string, minutes:int, servings:int}|null}> $plan */
final class AiMealPlan extends Model
{
    use HasPublicId;

    protected $fillable = ['user_id', 'ai_interaction_id', 'days', 'constraints', 'plan', 'status'];

    protected function casts(): array
    {
        return ['constraints' => 'array', 'plan' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interaction(): BelongsTo
    {
        return $this->belongsTo(AiInteraction::class, 'ai_interaction_id');
    }
}
