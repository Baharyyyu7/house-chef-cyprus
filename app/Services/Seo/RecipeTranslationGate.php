<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\ContentTranslation;
use App\Models\Recipe;
use App\Models\RecipeStep;

/**
 * Bir tarifin yabancı dildeki adımları gerçekten çevrilmiş mi.
 *
 * 24 Eylül 2026'da ölçüldü: altı yabancı dilin her birinde 588 adım
 * çevirisinin 588'i aynı kalıptı. İngilizcede "In step 1, carefully
 * proceed with the culinary preparation: Ensure appropriate heat
 * control..." ve arkasına Türkçe asıl talimat; Portekizcede yalnız kalıp,
 * talimat hiç yok. İndekse açık ~97 tarif, altı dil: ~580 sayfa. Türkçe
 * kalite kapısı bunu göremiyordu çünkü Türkçe adımlar gerçekti.
 *
 * Bu, siteye 16 Ağustos 2026'da zarar veren ölçekli şablon içerik
 * kalıbının ta kendisi. Sayfa yayında kalır, bağlantılar kırılmaz; yalnız
 * o dilde dizine kapanır ve site haritasından çıkar. Adımlar gerçekten
 * çevrildiğinde kural kendiliğinden gevşer.
 */
final class RecipeTranslationGate
{
    /** Adımların bu oranı aynı açılışı paylaşıyorsa çeviri kalıptır. */
    public const KALIP_ORANI = 0.6;

    /** Karşılaştırılan açılış uzunluğu; sayılar # yapılır. */
    public const ACILIS = 40;

    /** @var array<string, string|null> */
    private array $sonuclar = [];

    /**
     * Neden hazır değil: "sablon", "eksik" ya da hazırsa null.
     */
    public function engel(Recipe $tarif, string $dil): ?string
    {
        if ($dil === (string) config('locales.default', 'tr')) {
            return null;
        }

        $anahtar = $tarif->getKey().':'.$dil;

        if (array_key_exists($anahtar, $this->sonuclar)) {
            return $this->sonuclar[$anahtar];
        }

        $adimlar = RecipeStep::query()->where('recipe_id', $tarif->getKey())->pluck('id');

        if ($adimlar->isEmpty()) {
            return $this->sonuclar[$anahtar] = null;
        }

        $ceviriler = ContentTranslation::query()
            ->where('translatable_type', (new RecipeStep)->getMorphClass())
            ->whereIn('translatable_id', $adimlar)
            ->where('locale', $dil)
            ->where('field', 'instruction')
            ->pluck('value')
            ->map(static fn ($deger): string => trim((string) $deger))
            ->filter(static fn (string $deger): bool => $deger !== '')
            ->values();

        if ($ceviriler->count() < $adimlar->count()) {
            return $this->sonuclar[$anahtar] = 'eksik';
        }

        return $this->sonuclar[$anahtar] = $this->kalipMi($ceviriler->all()) ? 'sablon' : null;
    }

    public function hazir(Recipe $tarif, string $dil): bool
    {
        return $this->engel($tarif, $dil) === null;
    }

    /** @param list<string> $adimlar */
    public function kalipMi(array $adimlar): bool
    {
        if (count($adimlar) < 2) {
            return false;
        }

        $acilislar = array_count_values(array_map(
            static fn (string $a): string => mb_substr((string) preg_replace('/\d+/u', '#', mb_strtolower($a)), 0, self::ACILIS),
            $adimlar
        ));

        return max($acilislar) >= 2 && max($acilislar) / count($adimlar) >= self::KALIP_ORANI;
    }
}
