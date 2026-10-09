<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

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

    /**
     * Bugünden son kullanma gününe kalan gün. Gelecek tarih pozitif, geçmiş negatif.
     * Carbon'un diffInDays işaretini tarih sırasına göre döndürür; ters çağrı gelecek
     * ürünleri "yakında bitecek" sayıyordu.
     */
    public function kalanGun(): ?int
    {
        return $this->expires_at === null ? null : (int) today()->diffInDays(Carbon::parse($this->expires_at)->startOfDay(), false);
    }

    /** @return 'expired'|'soon'|'fresh'|'undated' */
    public function durum(): string
    {
        $kalan = $this->kalanGun();

        return match (true) {
            $kalan === null => 'undated',
            $kalan < 0 => 'expired',
            $kalan <= 3 => 'soon',
            default => 'fresh',
        };
    }

    /** @return BelongsTo<Ingredient, $this> */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
