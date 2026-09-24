<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** @property string $public_id */
class RecipeStep extends Model
{
    use HasPublicId, HasTranslations;

    /**
     * Bir tarifin gövdesi adımlarıdır. Başlığı İngilizce, adımları Türkçe
     * bir tarif sayfası çevrilmiş sayılmaz; İngilizce bir sayfada Türkçe
     * yönerge okuyan ziyaretçi için sayfa hiç çevrilmemiştir.
     *
     * @return list<string>
     */
    public function translatableFields(): array
    {
        return ['title', 'instruction', 'chef_tip', 'safety_note'];
    }

    protected $fillable = ['recipe_id', 'position', 'title', 'instruction', 'duration_seconds', 'temperature_value', 'temperature_unit', 'timer_required', 'is_optional', 'equipment', 'chef_tip', 'safety_note', 'animation_key', 'completion_hint'];

    protected function casts(): array
    {
        return ['timer_required' => 'boolean', 'is_optional' => 'boolean'];
    }

    /** @return BelongsTo<Recipe, $this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /** @return BelongsToMany<CookingSession, $this> */
    public function cookingSessions(): BelongsToMany
    {
        return $this->belongsToMany(CookingSession::class, 'cooking_session_steps')->withPivot('completed_at')->withTimestamps();
    }
}
