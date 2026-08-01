<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AiConversation extends Model
{
    use HasPublicId;

    protected $fillable = ['user_id', 'purpose', 'memory', 'last_interacted_at'];

    protected function casts(): array
    {
        return ['memory' => 'array', 'last_interacted_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(AiInteraction::class);
    }
}
