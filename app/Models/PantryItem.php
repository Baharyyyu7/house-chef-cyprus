<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PantryItem extends Model
{
    protected $fillable = ['user_id', 'ingredient_id', 'quantity', 'canonical_quantity', 'canonical_unit', 'conversion_confidence', 'unit', 'unit_id', 'expires_at', 'opened_at', 'storage_location', 'notes', 'is_available'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:4', 'canonical_quantity' => 'decimal:4', 'expires_at' => 'date', 'opened_at' => 'datetime', 'is_available' => 'boolean'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Ingredient, $this> */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
