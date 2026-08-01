<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\ContentTopicCluster;
use App\Models\KeywordTopic;
use App\Models\Media;
use App\Models\Recipe;
use App\Models\SeoIssue;

/**
 * Read-only, evidence-based SEO health measurements.
 *
 * This service deliberately measures what is in the catalogue. It does not
 * infer search volume, authority, links, ratings or review status.
 */
final class SeoAuthorityService
{
    /** @return array{score:int,indexable:array<string,int>,coverage:array<string,int>,issues:array<string,int>,topics:array<string,int>,last_audit_at:?string} */
    public function snapshot(): array
    {
        $openIssues = SeoIssue::query()->whereNull('resolved_at');
        $issues = [
            'critical' => (clone $openIssues)->where('severity', 'critical')->count(),
            'error' => (clone $openIssues)->where('severity', 'error')->count(),
            'warning' => (clone $openIssues)->where('severity', 'warning')->count(),
            'info' => (clone $openIssues)->where('severity', 'info')->count(),
        ];

        $penalty = ($issues['critical'] * 20) + ($issues['error'] * 10) + ($issues['warning'] * 4) + $issues['info'];
        $publicRecipes = Recipe::query()->where('status', 'published')->where('verification_status', 'verified');
        $publicPosts = BlogPost::query()->publiclyVisible();

        return [
            'score' => max(0, 100 - min(100, $penalty)),
            'indexable' => [
                'recipes' => (clone $publicRecipes)->count(),
                'articles' => (clone $publicPosts)->count(),
                'images' => Media::query()->where('visibility', 'public')->where('verification_status', 'verified')->count(),
            ],
            'coverage' => [
                'recipes_with_public_media' => (clone $publicRecipes)->whereHas('media', fn ($query) => $query->where('visibility', 'public')->where('verification_status', 'verified'))->count(),
                'articles_with_related_recipes' => (clone $publicPosts)->has('recipes')->count(),
                'articles_with_explicit_metadata' => (clone $publicPosts)->whereNotNull('meta_title')->whereNotNull('meta_description')->count(),
            ],
            'issues' => $issues,
            'topics' => [
                'clusters' => ContentTopicCluster::query()->count(),
                'topics' => KeywordTopic::query()->count(),
                'approved_topics' => KeywordTopic::query()->where('status', 'approved')->count(),
            ],
            'last_audit_at' => SeoIssue::query()->max('last_detected_at'),
        ];
    }
}
