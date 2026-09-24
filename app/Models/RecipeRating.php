<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Üyeliksiz tarif oyu: tarif başına ziyaretçi başına tek oy (güncellenebilir). */
final class RecipeRating extends Model
{
    public $timestamps = false;

    protected $fillable = ['recipe_id', 'rating', 'voter_key', 'ip_hash'];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'created_at' => 'datetime'];
    }

    /** @return BelongsTo<Recipe, $this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
