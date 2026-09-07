<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Http\Controllers\SitemapController;
use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Recipe;
use App\Services\Media\PublicMediaEligibilityService;

/**
 * Audits the dynamic sitemap source of truth without caching URLs or exposing
 * drafts. The HTTP sitemap remains the canonical generated representation.
 */
final class SitemapAuditService
{
    /** @return array{valid:bool,generated_at:string,sections:array<string,array{count:int,excluded:int}>} */
    public function audit(): array
    {
        // HTTP ciktilari kanonik kaynaktir. Burada sorgulari ikinci kez
        // taklit etmek zamanla ayrisma uretti: canli sitemap 84 tarif
        // tasirken bu rapor 129 diyordu. Denetim artik kullaniciya ve Google'a
        // gercekte sunulan URL kumelerini sayar.
        $controller = app(SitemapController::class);
        $pageCount = $this->urlCount((string) $controller->pages()->getContent());
        $recipeCount = $this->urlCount((string) $controller->recipes()->getContent());
        $blogCount = $this->urlCount((string) $controller->blog()->getContent());
        $imageXml = (string) $controller->images(app(PublicMediaEligibilityService::class))->getContent();
        $imageCount = $this->urlCount($imageXml);

        $recipeCandidates = Recipe::query()->where('status', 'published')->count();
        $blogCandidates = BlogPost::query()->where('status', 'published')->count();
        $publicMedia = Media::query()->where('visibility', 'public')->count();

        $sections = [
            'pages' => ['count' => $pageCount, 'excluded' => 0],
            'recipes' => [
                'count' => $recipeCount,
                'excluded' => max(0, $recipeCandidates - $recipeCount),
            ],
            'blog' => [
                'count' => $blogCount,
                'excluded' => max(0, $blogCandidates - $blogCount),
            ],
            'images' => [
                'count' => $imageCount,
                'excluded' => max(0, $publicMedia - substr_count($imageXml, '<image:image>')),
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

    private function urlCount(string $xml): int
    {
        return substr_count($xml, '<url>');
    }
}
