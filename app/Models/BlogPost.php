<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $published_at
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $updated_at
 */
final class BlogPost extends Model
{
    use HasPublicId;

    protected $fillable = ['author_id', 'human_approved_by_user_id', 'blog_category_id', 'hero_media_id', 'content_brief_id', 'content_factory_batch_id', 'title', 'slug', 'excerpt', 'body', 'content_purpose', 'search_intent', 'primary_keyword', 'secondary_keywords', 'source_notes', 'faq_blocks', 'status', 'fact_check_status', 'editorial_status', 'content_factory_state', 'fact_check_required', 'originality_status', 'freshness_class', 'scheduled_at', 'review_due_at', 'human_approved_at', 'published_at', 'last_refreshed_at', 'meta_title', 'meta_description', 'canonical_url'];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'review_due_at' => 'datetime', 'human_approved_at' => 'datetime', 'published_at' => 'datetime', 'last_refreshed_at' => 'datetime', 'fact_check_required' => 'boolean', 'secondary_keywords' => 'array', 'source_notes' => 'array', 'faq_blocks' => 'array'];
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

    /** @return BelongsTo<ContentFactoryBatch, $this> */
    public function contentFactoryBatch(): BelongsTo
    {
        return $this->belongsTo(ContentFactoryBatch::class);
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

    /** @return BelongsTo<ContentBrief, $this> */
    public function brief(): BelongsTo
    {
        return $this->belongsTo(ContentBrief::class, 'content_brief_id');
    }

    /** @return MorphOne<SeoMetadata, $this> */
    public function seoMetadata(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'seoable');
    }

    /** @return HasMany<ContentRevision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(ContentRevision::class);
    }

    /** @return HasMany<SocialDistributionDraft, $this> */
    public function socialDrafts(): HasMany
    {
        return $this->hasMany(SocialDistributionDraft::class);
    }
}
