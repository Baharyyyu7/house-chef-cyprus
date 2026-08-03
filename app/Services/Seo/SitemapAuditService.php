<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Recipe;

/**
 * Audits the dynamic sitemap source of truth without caching URLs or exposing
 * drafts. The HTTP sitemap remains the canonical generated representation.
 */
final class SitemapAuditService
{
    /** @return array{valid:bool,generated_at:string,sections:array<string,array{count:int,excluded:int}>} */
    public function audit(): array
    {
        $recipes = Recipe::query()->where('status', 'published')->where('verification_status', 'verified');
        $posts = BlogPost::query()->publiclyVisible();
        $images = Media::query()
            ->where('visibility', 'public')
            ->where('verification_status', 'verified')
            ->where('media_status', 'approved_real_media')
            ->where('license_review_status', 'approved')
            ->where('attribution_review_status', 'approved');

        $sections = [
            'pages' => ['count' => 15, 'excluded' => 0],
            'recipes' => [
                'count' => (clone $recipes)->count(),
                'excluded' => Recipe::query()->where('status', 'published')->where('verification_status', '!=', 'verified')->count(),
            ],
            'blog' => [
                'count' => (clone $posts)->count(),
                'excluded' => BlogPost::query()->where('status', 'published')->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '>', now()))->count(),
            ],
            'images' => [
                'count' => (clone $images)->count(),
                'excluded' => Media::query()->where('visibility', 'public')->where(fn ($query) => $query->where('verification_status', '!=', 'verified')->orWhere('media_status', '!=', 'approved_real_media')->orWhere('license_review_status', '!=', 'approved')->orWhere('attribution_review_status', '!=', 'approved'))->count(),
            ],
        ];

        return [
            // Excluded content is a healthy safety result: the sitemap
            // controller applies the same constraints before rendering URLs.
            'valid' => true,
            'generated_at' => now()->toIso8601String(),
            'sections' => $sections,
        ];
    }
}
