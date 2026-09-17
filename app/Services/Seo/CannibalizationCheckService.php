<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SeoMetadata;

final class CannibalizationCheckService
{
    /**
     * Aynı arama niyeti için yarışan sayfaları tespit eder.
     *
     * Kaynaklar:
     * 1. Blog yazılarında aynı primary_keyword
     * 2. Yayında olan içeriklerde aynı primary_query (seo_metadata)
     * 3. Yayında olan içeriklerde aynı focus_topic (seo_metadata)
     *
     * Sonuç salt-okunurdur; editoryal karar insana aittir.
     *
     * @return array{
     *     detected: int,
     *     candidates: list<array{
     *         keyword: string,
     *         normalized: string,
     *         source: string,
     *         severity: string,
     *         page_count: int,
     *         pages: list<array{type: string, title: string, public_id: string, url: string, status: string}>
     *     }>
     * }
     */
    public function detect(): array
    {
        $candidates = [];

        $this->groupedBlogKeywords($candidates);
        $this->groupedMetadataField($candidates, 'primary_query');
        $this->groupedMetadataField($candidates, 'focus_topic');

        usort($candidates, fn (array $a, array $b): int => mb_strlen($b['keyword']) <=> mb_strlen($a['keyword']));

        return ['detected' => count($candidates), 'candidates' => $candidates];
    }

    /** @param list<array<string,mixed>> $candidates */
    private function groupedBlogKeywords(array &$candidates): void
    {
        BlogPost::query()
            ->whereIn('status', ['published', 'scheduled', 'approved'])
            ->whereNotNull('primary_keyword')
            ->where('primary_keyword', '!=', '')
            ->get(['id', 'public_id', 'title', 'slug', 'status', 'primary_keyword'])
            ->groupBy(fn (BlogPost $post): string => self::normalize((string) $post->primary_keyword))
            ->each(function ($posts, string $key) use (&$candidates): void {
                if ($posts->count() < 2) {
                    return;
                }

                $pages = $posts->map(fn (BlogPost $post): array => [
                    'type' => 'blog',
                    'title' => (string) $post->title,
                    'public_id' => (string) $post->public_id,
                    'url' => route('blog.show', $post),
                    'status' => (string) $post->status,
                ])->values()->all();

                $candidates[] = [
                    'keyword' => (string) $posts->first()->primary_keyword,
                    'normalized' => $key,
                    'source' => 'primary_keyword',
                    'severity' => $posts->count() >= 3 ? 'high' : 'medium',
                    'page_count' => $posts->count(),
                    'pages' => $pages,
                ];
            });
    }

    /** @param list<array<string,mixed>> $candidates */
    private function groupedMetadataField(array &$candidates, string $field): void
    {
        SeoMetadata::query()
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->where(function ($query): void {
                $query->whereHasMorph('seoable', [BlogPost::class], fn ($q) => $q->whereIn('status', ['published', 'scheduled', 'approved']))
                    ->orWhereHasMorph('seoable', [Recipe::class], fn ($q) => $q->where('status', 'published')->where('verification_status', 'verified'));
            })
            ->get(['id', 'seoable_type', 'seoable_id', $field])
            ->groupBy(fn (SeoMetadata $metadata): string => self::normalize((string) $metadata->getAttribute($field)))
            ->each(function ($metadataList, string $key) use (&$candidates, $field): void {
                if ($metadataList->count() < 2) {
                    return;
                }

                $pages = $metadataList->map(function (SeoMetadata $metadata): ?array {
                    $entity = $metadata->seoable;
                    if (! $entity instanceof Recipe && ! $entity instanceof BlogPost) {
                        return null;
                    }

                    return [
                        'type' => $entity instanceof Recipe ? 'recipe' : 'blog',
                        'title' => (string) $entity->getAttribute('title'),
                        'public_id' => (string) $entity->getAttribute('public_id'),
                        'url' => $entity instanceof Recipe ? route('recipes.show', $entity) : route('blog.show', $entity),
                        'status' => $entity instanceof Recipe ? (string) $entity->status : (string) $entity->status,
                    ];
                })->filter()->values()->all();

                if (count($pages) < 2) {
                    return;
                }

                $candidates[] = [
                    'keyword' => (string) $metadataList->first()->getAttribute($field),
                    'normalized' => $key,
                    'source' => $field,
                    'severity' => count($pages) >= 3 ? 'high' : 'medium',
                    'page_count' => count($pages),
                    'pages' => $pages,
                ];
            });
    }

    public static function normalize(string $value): string
    {
        $transliteration = [
            'Ç' => 'c', 'ç' => 'c', 'Ğ' => 'g', 'ğ' => 'g',
            'I' => 'i', 'İ' => 'i', 'ı' => 'i',
            'Ö' => 'o', 'ö' => 'o', 'Ş' => 's', 'ş' => 's', 'Ü' => 'u', 'ü' => 'u',
        ];
        $value = strtr($value, $transliteration);
        $value = strtolower($value);

        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $value));
    }
}
