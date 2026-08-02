<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PersonalizationPreference extends Model
{
    use HasPublicId;

    public const SOURCES = ['explicitly_selected', 'inferred_from_favorites', 'inferred_from_cooking', 'inferred_from_pantries', 'inferred_from_feedback'];

    protected $fillable = ['personalization_profile_id', 'type', 'value', 'source', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return BelongsTo<PersonalizationProfile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(PersonalizationProfile::class, 'personalization_profile_id');
    }
}
