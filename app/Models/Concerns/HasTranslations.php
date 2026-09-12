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
     * Otomatik çeviri erişimcisi.
     *
     * Çevrilebilir bir alan ($recipe->title gibi) doğrudan okunduğunda,
     * eğer varsayılan dil dışındaysak ve çevirisi varsa çevrilmiş değeri
     * döndürür. Çeviri yoksa veya varsayılan dildeysek Eloquent'in orijinal
     * özniteliğine düşer.
     */
    public function getAttribute($key)
    {
        if (in_array($key, $this->translatableFields(), true)) {
            if (app()->bound('request') && request()->is('yonetim', 'yonetim/*')) {
                return parent::getAttribute($key);
            }

            $locale = app()->getLocale();
            if ($locale !== (string) config('locales.default', 'tr')) {
                $translated = $this->translated($key, $locale);
                if ($translated !== null) {
                    return $translated;
                }
            }
        }

        return parent::getAttribute($key);
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
            return parent::getAttribute($field);
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
            // Kaynakta boş olan alan çeviride istenmez. Türkçesi boş bir
            // başlığın İngilizcesini şart koşmak, çevirisi tamam olan kaydı
            // asla açılmayacak bir eksikle kilitler. Ancak alan veritabanından
            // yüklenmemişse (ör. kısmi SELECT) boş sayılamaz.
            if (array_key_exists($alan, $this->attributes) && trim((string) parent::getAttribute($alan)) === '') {
                continue;
            }

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
            // hasTranslation() ile aynı tanım: kaynakta boş olan alan
            // aranmaz. İkisi ayrışırsa hreflang listede olmayan bir sayfayı
            // gösterir ve küme tamamen geçersiz olur.
            $query->where(function (Builder $dis) use ($locale, $alan): void {
                $dis->whereHas('translations', function (Builder $inner) use ($locale, $alan): void {
                    $inner->where('locale', $locale)->where('field', $alan)->whereNotNull('value')->whereRaw('TRIM(value) <> ?', ['']);
                })->orWhereNull($alan)->orWhereRaw('TRIM('.$alan.') = ?', ['']);
            });
        }

        return $query->with(['translations' => fn ($translations) => $translations->where('locale', $locale)]);
    }
}
