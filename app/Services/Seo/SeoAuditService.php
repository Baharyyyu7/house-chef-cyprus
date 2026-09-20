<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SeoIssue;
use App\Models\SeoMetadata;
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
        if (in_array($scope, ['all', 'technical'], true)) {
            $this->inspectMetadata($detected);
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
            // Yayın kapısı her koşulda denetlenir: kapıdan geçmeden yayına
            // çıkmış bir yazı, noindex olsa bile bir yayın disiplini kusurudur.
            if ($post->status === 'published' && (! in_array($post->editorial_status, ['approved', 'verified'], true) || ! in_array($post->fact_check_status, ['passed', 'verified'], true) || ! in_array($post->originality_status, ['passed', 'clean', 'original'], true))) {
                $this->add($detected, 'content', 'error', $post, route('blog.show', $post), 'Yayınlanan blog yazısı editoryal kalite kapısından geçmemiş.', 'blog_publish_guard');
            }

            // Geri kalan ölçütler sayfanın arama sonucundaki halini denetler;
            // indekse kapalı sayfada karşılığı yok. 20 Eylül 2026'da yinelenen
            // başlıklı 26 nüsha kanonikle eşine bağlanıp noindex yapılınca,
            // alınan başlıkları denetim eksik alan olarak sayıyordu.
            if ($this->kendisiKapali($post)) {
                return;
            }

            if (blank($post->meta_title) || blank($post->meta_description)) {
                $this->add($detected, 'content', 'warning', $post, route('blog.show', $post), 'Blog SEO başlığı veya açıklaması eksik.', 'blog_missing_metadata');
            }
            if ($post->status === 'published' && in_array($post->editorial_status, ['approved', 'verified'], true) && in_array($post->fact_check_status, ['passed', 'verified'], true) && in_array($post->originality_status, ['passed', 'clean', 'original'], true) && mb_strlen(trim((string) $post->body)) < (int) config('seo.audit.minimum_article_characters', 300)) {
                $this->add($detected, 'content', 'warning', $post, route('blog.show', $post), 'Yayınlanan blog yazısı, ince içerik incelemesi için yeterli metin uzunluğuna ulaşmıyor.', 'blog_thin_content');
            }
            if ($post->status === 'published' && in_array($post->editorial_status, ['approved', 'verified'], true) && in_array($post->fact_check_status, ['passed', 'verified'], true) && in_array($post->originality_status, ['passed', 'clean', 'original'], true) && $post->recipes()->count() === 0) {
                $this->add($detected, 'content', 'info', $post, route('blog.show', $post), 'Blog yazısında ilgili doğrulanmış tarif bağlantısı bulunmuyor.', 'blog_missing_internal_recipe_link');
            }
        });
        // Gruplanmış sorgu chunk'lanmaz: each() sorguya birincil anahtara göre
        // sıralama ve limit/offset ekler, GROUP BY ile birleşince uydurma
        // satırlar üretir. 20 Eylül 2026'da ölçüldü: gerçekte tek bir
        // yinelenen başlık varken denetim 26 uyarı açıyordu ve her koşuda
        // farklılarını açıp kapatıyordu.
        BlogPost::query()->whereIn('status', ['published', 'scheduled', 'approved'])->selectRaw('meta_title, COUNT(*) as aggregate')->whereNotNull('meta_title')->where('meta_title', '!=', '')->groupBy('meta_title')->having('aggregate', '>', 1)->get()->each(function (BlogPost $post) use (&$detected): void {
            $detected[] = $this->issue('technical', 'warning', null, null, null, 'Aynı SEO başlığı birden fazla blog yazısında kullanılıyor.', 'duplicate_blog_title:'.$post->meta_title, ['field' => 'meta_title']);
        });
    }

    /** @param list<array<string,mixed>> $detected */
    private function inspectRecipes(array &$detected): void
    {
        Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->with('seoMetadata')->each(function (Recipe $recipe) use (&$detected): void {
            // Kalite kapısının kapattığı tarif arama sonucunda görünmüyor:
            // ne açıklamasının kısalığı ne görselinin eksikliği SEO kusuru.
            // Görsel eksiği ayrıca media:gap-queue muhafızının işi ve orada
            // kendi listesiyle duruyor.
            if ($this->kendisiKapali($recipe)) {
                return;
            }

            if ($recipe->media()->where('visibility', 'public')->count() === 0) {
                $this->add($detected, 'schema', 'warning', $recipe, route('recipes.show', $recipe), 'Tarif için doğrulanmış public görsel bulunmuyor; image schema gönderilmemeli.', 'recipe_missing_public_image');
            }
            if (mb_strlen(trim((string) ($recipe->description ?: $recipe->excerpt))) < (int) config('seo.audit.minimum_recipe_description_characters', 120)) {
                $this->add($detected, 'content', 'warning', $recipe, route('recipes.show', $recipe), 'Tarif açıklaması, kullanıcıya yeterli bağlam sağlamak için ince içerik incelemesine ihtiyaç duyuyor.', 'recipe_thin_description');
            }
            $recipe->media()->where('visibility', 'public')->where('verification_status', 'verified')->where(fn ($query) => $query->whereNull('alt_text')->orWhere('alt_text', ''))->each(function ($media) use (&$detected, $recipe): void {
                $this->add($detected, 'schema', 'warning', $recipe, route('recipes.show', $recipe), 'Public tarif görseli açıklayıcı alternatif metin içermiyor.', 'recipe_media_missing_alt:'.$media->getKey());
            });
        });
    }

    /** @param list<array<string,mixed>> $detected */
    private function inspectMetadata(array &$detected): void
    {
        SeoMetadata::query()->whereNotNull('canonical_override')->where('canonical_override', '!=', '')->each(function (SeoMetadata $metadata) use (&$detected): void {
            $entity = $metadata->seoable;
            if (! $entity instanceof Recipe && ! $entity instanceof BlogPost) {
                return;
            }
            $expected = $entity instanceof Recipe ? route('recipes.show', $entity) : route('blog.show', $entity);
            if (rtrim((string) $metadata->canonical_override, '/') === rtrim($expected, '/')) {
                return;
            }

            // Kendisi indekse kapalı bir sayfanın kanoniği sonucu değiştirmez:
            // kaybedecek sinyali yoktur. 20 Eylül 2026'da tarif kalite kapısı
            // 223 ölçüsüz tarifi kapatınca bu uyarı 171'e fırlamıştı ve
            // hepsinin kaynağı da kapalıydı.
            if ($this->kendisiKapali($entity)) {
                return;
            }

            // Kanoniğin kendi adresini göstermemesi tek başına kusur değil:
            // kopya tarifler ve taslak inceleme nüshaları bilerek gerçek eşine
            // bağlanır. 20 Eylül 2026'da ölçüldü, 227 teknik uyarının 191'i bu
            // bilinçli birleştirmeydi ve gerçek kusurları görünmez ediyordu.
            // Kusur, kanoniğin gittiği yerin kendisinin kapalı olmasıdır.
            $sebep = $this->kanonikHedefKusuru((string) $metadata->canonical_override);

            if ($sebep !== null) {
                $this->add($detected, 'technical', 'warning', $entity, $expected, 'Canonical override kapalı bir adresi gösteriyor ('.$sebep.').', 'canonical_conflict');
            }
        });

        $publishedMetadata = fn () => SeoMetadata::query()->where(function ($q) {
            $q->whereHasMorph('seoable', [BlogPost::class], fn ($b) => $b->whereIn('status', ['published', 'scheduled', 'approved']))
              ->orWhereHasMorph('seoable', [Recipe::class], fn ($r) => $r->where('status', 'published')->where('verification_status', 'verified'));
        });

        $publishedMetadata()->selectRaw('meta_title, COUNT(*) as aggregate')->whereNotNull('meta_title')->where('meta_title', '!=', '')->groupBy('meta_title')->having('aggregate', '>', 1)->get()->each(function (SeoMetadata $metadata) use (&$detected): void {
            $detected[] = $this->issue('technical', 'warning', null, null, null, 'Aynı SEO başlığı birden fazla içerikte kullanılıyor.', 'duplicate_metadata_title:'.$metadata->meta_title, ['field' => 'meta_title']);
        });
        $publishedMetadata()->selectRaw('meta_description, COUNT(*) as aggregate')->whereNotNull('meta_description')->where('meta_description', '!=', '')->groupBy('meta_description')->having('aggregate', '>', 1)->get()->each(function (SeoMetadata $metadata) use (&$detected): void {
            $detected[] = $this->issue('technical', 'warning', null, null, null, 'Aynı SEO açıklaması birden fazla içerikte kullanılıyor.', 'duplicate_metadata_description:'.$metadata->meta_description, ['field' => 'meta_description']);
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

    /**
     * Kanoniğin gösterdiği adres indekslenebilir mi?
     *
     * Aynı sitede yayında ve indekse açık bir sayfayı gösteren kanonik
     * birleştirmedir; sinyal kaybolmaz, eşine taşınır. Kusur sayılan üç hal:
     * adres başka bir alan adına gidiyor, karşılığı olan kayıt yok ya da
     * hedef sayfa kapalı.
     */
    private function kanonikHedefKusuru(string $adres): ?string
    {
        if (! str_starts_with(rtrim($adres, '/').'/', rtrim((string) config('app.url'), '/').'/')) {
            return 'site dışı';
        }

        $parcalar = explode('/', trim((string) parse_url($adres, PHP_URL_PATH), '/'));
        [$bolum, $slug] = [$parcalar[0] ?? '', $parcalar[1] ?? ''];

        if ($slug === '') {
            return null;
        }

        if ($bolum === 'blog') {
            $hedef = BlogPost::query()->where('slug', $slug)->with('seoMetadata')->first();

            if ($hedef === null) {
                return 'kayıt yok';
            }

            if ($hedef->status !== 'published') {
                return 'yayında değil';
            }

            return str_contains(app(BlogIndexability::class)->robots($hedef), 'noindex') ? 'noindex' : null;
        }

        $hedef = Recipe::query()->where('slug', $slug)->with('seoMetadata')->first();

        if ($hedef === null) {
            return 'kayıt yok';
        }

        if ($hedef->status !== 'published') {
            return 'yayında değil';
        }

        return str_contains((string) $hedef->seoMetadata?->robots, 'noindex') ? 'noindex' : null;
    }

    /** Sayfanın kendisi indekse kapalı mı? */
    private function kendisiKapali(Model $entity): bool
    {
        if ($entity instanceof BlogPost) {
            return str_contains(app(BlogIndexability::class)->robots($entity), 'noindex');
        }

        return $entity instanceof Recipe && str_contains((string) $entity->seoMetadata?->robots, 'noindex');
    }
}
