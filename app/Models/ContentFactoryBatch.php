<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ContentFactoryBatch extends Model
{
    use HasPublicId;

    protected $fillable = ['content_type', 'sequence', 'status', 'planned_items', 'created_items', 'technical_passed_items', 'failed_items', 'distribution', 'started_at', 'finished_at'];

    protected function casts(): array
    {
        return ['distribution' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /** @return HasMany<ContentFactoryReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(ContentFactoryReview::class);
    }

    /** @return HasMany<Recipe, $this> */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /** @return HasMany<BlogPost, $this> */
    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }
}
