<?php

declare(strict_types=1);

namespace App\Services\Integrity;

use App\Models\Recipe;
use Illuminate\Support\Collection;

/**
 * Bir tarifin çeviriye hazır olup olmadığını söyler.
 *
 * Site dört dil için hazır ama şu an hiç çeviri yok ve dillerin dördü de
 * kapalı - bu doğru bir durum, çünkü çevirisi olmayan kayıt o dilde var
 * olmuyor.
 *
 * Tehlike sıralamada: Türkçe tarafı düzeltmeden çevirmek hatayı dille
 * çarpar. Yayındaki tariflerin yarısına yakınının onaylı görseli yok;
 * bunları dört dile çevirmek yüzlerce zayıf sayfa demek ve bu site zaten
 * bir kez ölçekli zayıf içerik yüzünden ceza yedi.
 *
 * Bu yüzden çeviri kuyruğuna yalnız kendi dilinde tam olan tarif girer.
 */
final class TranslationReadiness
{
    /** @return array<string, mixed> */
    public function report(int $limit = 40): array
    {
        $yayindakiler = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->with(['media', 'recipeIngredients.ingredient', 'steps', 'cuisine'])
            ->get();

        $hazir = $yayindakiler->filter(fn (Recipe $t): bool => $this->engeller($t) === []);
        $engelli = $yayindakiler->reject(fn (Recipe $t): bool => $this->engeller($t) === []);

        return [
            'checked_at' => now()->toIso8601String(),
            'published' => $yayindakiler->count(),
            'ready' => $hazir->count(),
            'blocked' => $engelli->count(),
            'blockers' => $this->engelDagilimi($engelli),
            'queue' => $hazir->take(max(1, $limit))->map(fn (Recipe $t): array => [
                'slug' => $t->slug,
                'title' => $t->title,
                'cuisine' => $t->cuisine?->name,
            ])->values()->all(),
        ];
    }

    /** @return list<string> */
    public function engeller(Recipe $tarif): array
    {
        $engeller = [];

        if ($tarif->media->where('media_status', 'approved_real_media')->isEmpty()) {
            $engeller[] = 'onayli_gorsel_yok';
        }

        if (mb_strlen(trim((string) $tarif->description)) < 120) {
            $engeller[] = 'aciklama_ince';
        }

        if ($tarif->recipeIngredients->isEmpty()) {
            $engeller[] = 'malzeme_yok';
        }

        if ($tarif->recipeIngredients->contains(fn ($s): bool => ($s->ingredient->name ?? '') === 'Ana Malzeme')) {
            $engeller[] = 'yer_tutucu_malzeme';
        }

        if ($tarif->steps->isEmpty()) {
            $engeller[] = 'adim_yok';
        }

        return $engeller;
    }

    /** @param Collection<int, Recipe> $engelli @return array<string, int> */
    private function engelDagilimi(Collection $engelli): array
    {
        $sayim = [];

        foreach ($engelli as $tarif) {
            foreach ($this->engeller($tarif) as $engel) {
                $sayim[$engel] = ($sayim[$engel] ?? 0) + 1;
            }
        }

        arsort($sayim);

        return $sayim;
    }
}
