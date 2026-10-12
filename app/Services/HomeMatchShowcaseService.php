<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Support\SureMetni;
use Illuminate\Support\Facades\DB;

/**
 * Ana sayfa hero'sunda kendi kendine oynayan eşleştirme gösterisi.
 *
 * Gösterideki her sayı ve isim gerçek: dolaba düşen malzemeler bir
 * tarifin zorunlu malzemeleri, eşleşme yüzdesi o tarifin kapsanma oranı,
 * "tam yapılabilir" sayısı o dolapla eksiksiz pişirilebilecek tariflerin
 * sayısı. Kural IngredientMatchingService'in kuralıyla aynı: zorunlu
 * malzeme = is_optional değil ve is_required.
 *
 * Gösteri daralan bir huni değil, büyüyen bir liste. Motor bir dolap
 * eşleştiricisi; malzeme ekledikçe pişirilebilecek tarif artar. Tersini
 * göstermek ürünü yanlış anlatırdı.
 */
final class HomeMatchShowcaseService
{
    /** Gösteride tek bir tarifin kaç zorunlu malzemesi olabilir. */
    private const EN_AZ = 3;

    private const EN_COK = 5;

    /**
     * @return list<array{recipe: array{title: string, url: string, minutes: int, servings: int}, steps: list<array{ingredient: string, percent: int, can_cook: int}>, total: int}>
     */
    public function senaryolar(int $adet = 3): array
    {
        $tarifler = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->translatedInto()
            ->get(['id', 'title', 'slug', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings']);

        if ($tarifler->isEmpty()) {
            return [];
        }

        $gerekli = DB::table('recipe_ingredients')
            ->whereIn('recipe_id', $tarifler->pluck('id'))
            ->where('is_optional', false)
            ->where('is_required', true)
            ->whereNotNull('ingredient_id')
            ->orderBy('sort_order')
            ->get(['recipe_id', 'ingredient_id'])
            ->groupBy('recipe_id')
            ->map(fn ($satirlar) => $satirlar->pluck('ingredient_id')->unique()->values()->all());

        $varsayilan = app()->getLocale() === config('locales.default', 'tr');

        // Varsayılan dil dışında adı çevrilmemiş malzeme gösteriye girmez:
        // "Fresh Makarna" gibi yarı çevrilmiş bir ad vitrinde durmamalı.
        $adlar = Ingredient::query()
            ->whereIn('id', $gerekli->flatten()->unique()->all())
            ->get()
            ->mapWithKeys(function (Ingredient $m) use ($varsayilan): array {
                $ceviri = $varsayilan ? $m->name : $m->translated('name');

                return [$m->id => (string) ($ceviri ?? '')];
            });

        // Aynı zorunlu malzeme kümesini paylaşan tarifler şablon üretiminden
        // kalma: pirinç, bulgur, makarna ve undan yapılmış bir "fasulye
        // salatası" ile bir "mantarlı risotto" aynı kümeyi taşıyordu. Küme
        // benzersiz değilse tarif gösteriye girmez.
        $kumeSayisi = $gerekli->map(function (array $liste): string {
            sort($liste);

            return implode(',', $liste);
        })->countBy();

        $adaylar = [];

        foreach ($tarifler as $tarif) {
            $liste = $gerekli->get($tarif->id, []);

            if (count($liste) < self::EN_AZ || count($liste) > self::EN_COK) {
                continue;
            }

            $anahtar = $liste;
            sort($anahtar);

            if (($kumeSayisi[implode(',', $anahtar)] ?? 0) > 1) {
                continue;
            }

            if ((int) $tarif->totalMinutes() <= 0) {
                continue;
            }

            $baslik = (string) ($varsayilan ? $tarif->title : $tarif->translated('title'));

            // Çevrilmemiş başlık: Türkçe metni içinde taşıyan ya da şablon
            // kuyruğuyla biten başlık gösteriye girmez.
            if ($baslik === '' || (! $varsayilan && (str_contains($baslik, (string) $tarif->title) || str_contains($baslik, 'Authentic Mediterranean')))) {
                continue;
            }

            $dolap = [];
            $adimlar = [];

            foreach ($liste as $i => $malzemeId) {
                $dolap[] = $malzemeId;

                $adimlar[] = [
                    'ingredient' => (string) ($adlar[$malzemeId] ?? ''),
                    'percent' => (int) round((($i + 1) / count($liste)) * 100),
                    'can_cook' => $this->pisirilebilir($gerekli, $dolap),
                ];
            }

            // Benzersiz küme şartıyla son adımda pişirilebilir tarif çoğu
            // zaman yalnız tarifin kendisi oluyor; "en az iki" şartı her
            // şeyi eliyordu. Gösterinin dramı sayıda değil, ölçerin %100'e
            // dolmasında. Sayı görünümde yalnız birden büyükse basılıyor.
            if (in_array('', array_column($adimlar, 'ingredient'), true)) {
                continue;
            }

            $son = end($adimlar)['can_cook'];

            $adaylar[] = [
                'recipe' => [
                    'title' => $baslik,
                    'url' => route('recipes.show', $tarif),
                    'minutes' => (int) $tarif->totalMinutes(),
                    'sure' => SureMetni::dakika((int) $tarif->totalMinutes()),
                    'servings' => (int) $tarif->servings,
                ],
                'steps' => $adimlar,
                'total' => $tarifler->count(),
                'skor' => $son,
            ];
        }

        usort($adaylar, fn (array $a, array $b): int => [count($b['steps']), $b['skor']] <=> [count($a['steps']), $a['skor']]);

        return array_map(
            static function (array $s): array {
                unset($s['skor']);

                return $s;
            },
            array_slice($adaylar, 0, $adet)
        );
    }

    /**
     * Bu dolapla eksiksiz pişirilebilecek tarif sayısı.
     *
     * @param  \Illuminate\Support\Collection<int, list<int>>  $gerekli
     * @param  list<int>  $dolap
     */
    private function pisirilebilir($gerekli, array $dolap): int
    {
        $sayi = 0;

        foreach ($gerekli as $liste) {
            if ($liste !== [] && array_diff($liste, $dolap) === []) {
                $sayi++;
            }
        }

        return $sayi;
    }
}
