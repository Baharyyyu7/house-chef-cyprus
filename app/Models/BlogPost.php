<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class BlogPost extends Model
{
    use HasPublicId;

    protected $fillable = ['author_id', 'blog_category_id', 'hero_media_id', 'title', 'slug', 'excerpt', 'body', 'status', 'scheduled_at', 'published_at', 'meta_title', 'meta_description', 'canonical_url'];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'published_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @param Builder<self> $query @return Builder<self> */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsTo<BlogCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    /** @return BelongsTo<Media, $this> */
    public function heroMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'hero_media_id');
    }

    /** @return BelongsToMany<BlogTag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class);
    }

    /** @return BelongsToMany<Recipe, $this> */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class);
    }
}
