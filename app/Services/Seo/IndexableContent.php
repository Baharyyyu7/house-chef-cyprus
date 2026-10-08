<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * "Bu kayıt bu dilde indekslenebilir mi?" - site haritası, llms.txt ve RSS
 * aynı süzgeci kullanır.
 *
 * Süzgeç önceden yalnız SitemapController içindeydi. 8 Ekim 2026
 * denetimi: llms.txt ve RSS noindex bir nüsha tarifi ve iki 404 adresi
 * yayıyordu; /en/feed Türkçe adres veriyordu. Bir yüzey "indeksleme"
 * derken diğerinin aynı adresi yapay zekâ korpusuna koyması çelişkidir.
 */
final class IndexableContent
{
    public function __construct(
        private readonly SeoMetadataService $seo,
        private readonly BlogIndexability $blog,
        private readonly RecipeTranslationGate $kapi,
    ) {
    }

    /** @return Builder<Recipe> */
    public function tarifler(?string $locale = null): Builder
    {
        $sorgu = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if (! $this->varsayilan($locale)) {
            $sorgu->translatedInto($locale);
        }

        return $this->noindexSuz($sorgu);
    }

    /** @return Builder<BlogPost> */
    public function yazilar(?string $locale = null): Builder
    {
        $sorgu = BlogPost::query()->publiclyVisible();

        if (! $this->varsayilan($locale)) {
            $sorgu->translatedInto($locale);
        }

        return $this->noindexSuz($sorgu);
    }

    /**
     * DB ön süzgeci yalnız '%noindex%' arar; 'none' takma adı ve büyük/küçük
     * harf varyantları robotsIzinVerir() içinde çözülür.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $sorgu
     * @return Builder<TModel>
     */
    public function noindexSuz(Builder $sorgu): Builder
    {
        if (! config('seo.indexing_enabled')) {
            return $sorgu->whereRaw('1 = 0');
        }

        return $sorgu->whereDoesntHave('seoMetadata', fn ($q) => $q->where('robots', 'like', '%noindex%'));
    }

    /** Kaydın kendi robots alanı efektif olarak indekse izin veriyor mu? */
    public function robotsIzinVerir(Recipe|BlogPost $kayit): bool
    {
        $robots = $kayit->relationLoaded('seoMetadata')
            ? $kayit->getRelation('seoMetadata')?->robots
            : $kayit->seoMetadata()->value('robots');

        return ! str_contains($this->seo->robots(is_string($robots) ? $robots : null), 'noindex');
    }

    /**
     * Kayıt bu dilde indekslenebilir mi?
     *
     * Yazının eşiği gövdenin o dildeki çevirisine uygulanır; tarifin yabancı
     * dildeki adımları kalıp çeviriyse sayfa o dilde noindex'tir.
     */
    public function dildeIndekslenebilir(Model $kayit, ?string $locale = null): bool
    {
        $locale ??= $this->varsayilanDil();

        if ($kayit instanceof Recipe) {
            return $this->varsayilan($locale) || $this->kapi->hazir($kayit, $locale);
        }

        if (! $kayit instanceof BlogPost) {
            return true;
        }

        $onceki = app()->getLocale();
        app()->setLocale($locale);

        try {
            return $this->blog->indekslenebilir($kayit);
        } finally {
            app()->setLocale($onceki);
        }
    }

    /** Robots, çeviri ve dil eşiği birlikte. */
    public function izinVerir(Recipe|BlogPost $kayit, ?string $locale = null): bool
    {
        $locale ??= $this->varsayilanDil();

        if (! $this->robotsIzinVerir($kayit)) {
            return false;
        }

        if (! $this->varsayilan($locale) && ! $kayit->hasTranslation($locale)) {
            return false;
        }

        return $this->dildeIndekslenebilir($kayit, $locale);
    }

    private function varsayilan(?string $locale): bool
    {
        return $locale === null || $locale === $this->varsayilanDil();
    }

    private function varsayilanDil(): string
    {
        return (string) config('locales.default', 'tr');
    }
}
