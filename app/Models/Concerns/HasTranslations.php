<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\ContentTranslation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Çevrilebilir içerik.
 *
 * Tek kural belirleyici: **çevirisi olmayan kayıt o dilin sitesinde yoktur.**
 *
 * Sessizce Türkçeye düşmek kolay olurdu ama arama motoru tarafında en pahalı
 * hata bu: /en/ altında Türkçe gövde servis etmek, Google'ın sayfayı Türkçe
 * sayması ve aynı içeriğin ikinci bir kopyası olarak görmesi demek. Üstelik
 * yardımcı içerik sistemi site geneli çalışıyor; çok sayıda zayıf dil sayfası
 * güçlü Türkçe sayfaların sıralamasını da aşağı çeker.
 *
 * Bu yüzden çevirisi olmayan kayıt listelerde, sitemap'te ve hreflang
 * kümesinde görünmez; adresi 404 verir.
 */
trait HasTranslations
{
    /** @return list<string> */
    abstract public function translatableFields(): array;

    /** @return MorphMany<ContentTranslation, $this> */
    public function translations(): MorphMany
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }

    /**
     * Bir alanın istenen dildeki değeri.
     *
     * Varsayılan dilde kaydın kendi sütunu kaynaktır; çeviri tablosuna
     * bakılmaz. Diğer dillerde çeviri yoksa null döner - çağıran taraf
     * eksikliği görmeli, sessizce Türkçe almalı değil.
     */
    public function translated(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        if ($locale === (string) config('locales.default', 'tr')) {
            return $this->getAttribute($field);
        }

        $ceviri = $this->relationLoaded('translations')
            ? $this->translations->firstWhere(fn (ContentTranslation $t): bool => $t->locale === $locale && $t->field === $field)
            : $this->translations()->where('locale', $locale)->where('field', $field)->first();

        $deger = $ceviri?->value;

        return is_string($deger) && trim($deger) !== '' ? $deger : null;
    }

    /**
     * Kayıt bu dilde yayınlanabilir mi?
     *
     * Kısmi çeviri yeterli değil: başlığı çevrilmiş ama gövdesi Türkçe kalmış
     * bir sayfa, tam çevrilmemiş sayfanın ta kendisidir.
     */
    public function hasTranslation(?string $locale = null): bool
    {
        $locale ??= app()->getLocale();

        if ($locale === (string) config('locales.default', 'tr')) {
            return true;
        }

        foreach ($this->translatableFields() as $alan) {
            if ($this->translated($alan, $locale) === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Yalnız bu dilde tam çevirisi olan kayıtlar.
     *
     * Listeleme, sitemap ve hreflang üretimi bu kapsamı kullanır; üçünün
     * aynı tanımı paylaşması gerekiyor, yoksa hreflang listede olmayan bir
     * sayfayı gösterir ve küme geçersiz olur.
     */
    public function scopeTranslatedInto(Builder $query, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();

        if ($locale === (string) config('locales.default', 'tr')) {
            return $query;
        }

        foreach ($this->translatableFields() as $alan) {
            $query->whereHas('translations', function (Builder $inner) use ($locale, $alan): void {
                $inner->where('locale', $locale)->where('field', $alan)->whereNotNull('value')->whereRaw('TRIM(value) <> ?', ['']);
            });
        }

        return $query->with(['translations' => fn ($translations) => $translations->where('locale', $locale)]);
    }
}
