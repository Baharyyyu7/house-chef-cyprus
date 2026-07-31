<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SeoIssue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class SeoAuditService
{
    /** @return array{created:int,updated:int,resolved:int,open:int,score:int,issues:list<array<string,mixed>>} */
    public function run(string $scope = 'all'): array
    {
        $detected = [];
        if (in_array($scope, ['all', 'technical', 'content'], true)) {
            $this->inspectBlogs($detected);
        }
        if (in_array($scope, ['all', 'technical', 'schema'], true)) {
            $this->inspectRecipes($detected);
        }

        $created = 0;
        $updated = 0;
        foreach ($detected as $issue) {
            $existing = SeoIssue::query()->where('fingerprint', $issue['fingerprint'])->first();
            SeoIssue::query()->updateOrCreate(['fingerprint' => $issue['fingerprint']], $issue + ['first_detected_at' => $existing?->first_detected_at ?: now(), 'last_detected_at' => now(), 'resolved_at' => null]);
            $existing ? $updated++ : $created++;
        }

        $fingerprints = collect($detected)->pluck('fingerprint')->all();
        $resolved = SeoIssue::query()->whereNull('resolved_at')->when($scope !== 'all', fn ($query) => $query->where('category', $scope))->when($fingerprints !== [], fn ($query) => $query->whereNotIn('fingerprint', $fingerprints))->update(['resolved_at' => now()]);
        $open = SeoIssue::query()->whereNull('resolved_at')->count();
        $weighted = SeoIssue::query()->whereNull('resolved_at')->get()->sum(fn (SeoIssue $issue): int => match ($issue->severity) {
            'critical' => 20, 'error' => 10, 'warning' => 4, default => 1
        });

        return ['created' => $created, 'updated' => $updated, 'resolved' => $resolved, 'open' => $open, 'score' => max(0, 100 - min(100, $weighted)), 'issues' => $detected];
    }

    /** @param list<array<string,mixed>> $detected */
    private function inspectBlogs(array &$detected): void
    {
        BlogPost::query()->whereIn('status', ['published', 'scheduled', 'approved'])->each(function (BlogPost $post) use (&$detected): void {
            if (blank($post->meta_title) || blank($post->meta_description)) {
                $this->add($detected, 'content', 'warning', $post, route('blog.show', $post), 'Blog SEO başlığı veya açıklaması eksik.', 'blog_missing_metadata');
            }
            if ($post->status === 'published' && ($post->editorial_status !== 'approved' || $post->fact_check_status !== 'passed' || $post->originality_status !== 'passed')) {
                $this->add($detected, 'content', 'error', $post, route('blog.show', $post), 'Yayınlanan blog yazısı editoryal kalite kapısından geçmemiş.', 'blog_publish_guard');
            }
        });
        BlogPost::query()->selectRaw('meta_title, COUNT(*) as aggregate')->whereNotNull('meta_title')->groupBy('meta_title')->having('aggregate', '>', 1)->each(function (BlogPost $post) use (&$detected): void {
            $detected[] = $this->issue('technical', 'warning', null, null, null, 'Aynı SEO başlığı birden fazla blog yazısında kullanılıyor.', 'duplicate_blog_title:'.$post->meta_title, ['field' => 'meta_title']);
        });
    }

    /** @param list<array<string,mixed>> $detected */
    private function inspectRecipes(array &$detected): void
    {
        Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->each(function (Recipe $recipe) use (&$detected): void {
            if ($recipe->media()->where('visibility', 'public')->count() === 0) {
                $this->add($detected, 'schema', 'warning', $recipe, route('recipes.show', $recipe), 'Tarif için doğrulanmış public görsel bulunmuyor; image schema gönderilmemeli.', 'recipe_missing_public_image');
            }
        });
    }

    /** @param list<array<string,mixed>> $detected */
    private function add(array &$detected, string $category, string $severity, Model $entity, string $url, string $message, string $rule): void
    {
        $detected[] = $this->issue($category, $severity, $entity::class, (string) $entity->getAttribute('public_id'), $url, $message, $rule.':'.$entity->getKey());
    }

    /** @return array<string,mixed> */
    private function issue(string $category, string $severity, ?string $entityType, ?string $entityPublicId, ?string $url, string $message, string $fingerprint, array $metadata = []): array
    {
        return ['fingerprint' => hash('sha256', $fingerprint), 'category' => $category, 'entity_type' => $entityType, 'entity_public_id' => $entityPublicId, 'url' => $url, 'severity' => $severity, 'message' => Str::limit($message, 255, ''), 'safe_metadata' => $metadata, 'last_detected_at' => now()];
    }
}
