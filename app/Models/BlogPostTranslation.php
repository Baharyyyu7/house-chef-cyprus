<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property list<string>|null $keywords
 * @property list<array{question:string,answer:string}>|null $faq_blocks
 */
final class BlogPostTranslation extends Model
{
    protected $fillable = ['blog_post_id', 'locale', 'slug', 'title', 'excerpt', 'body', 'category_label', 'keywords', 'faq_blocks', 'meta_title', 'meta_description', 'status', 'source_hash', 'translated_at'];

    protected function casts(): array
    {
        return ['keywords' => 'array', 'faq_blocks' => 'array', 'translated_at' => 'datetime'];
    }

    /** @return BelongsTo<BlogPost, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePubliclyVisible(Builder $query, string $locale): Builder
    {
        return $query->where($query->qualifyColumn('locale'), $locale)->where($query->qualifyColumn('status'), 'published')
            ->whereHas('post', fn (Builder $post) => $post->where($post->qualifyColumn('status'), 'published')->whereNotNull($post->qualifyColumn('published_at'))->where($post->qualifyColumn('published_at'), '<=', now()));
    }
}
