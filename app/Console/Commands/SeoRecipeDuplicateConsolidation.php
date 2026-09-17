<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Services\Seo\RecipeQualityGateService;
use App\Services\Seo\SeoMetadataService;
use Illuminate\Console\Command;

/**
 * Aynı yemeğin şablon kopyasını, gerçek tarifin arkasına yerleştirir.
 *
 * 17 Eylül 2026'da ölçüldü: kalite kapısına takılan 759 tarifin 177'sinin
 * aynı adlı, kapıdan geçen bir eşi de yayındaydı. Bu kopyaları yeniden
 * yazmanın anlamı yok; sayfa zaten var ve iyisi duruyor. Kopya kanonik
 * olarak eşini gösterir, böylece bağlantı ve alaka sinyalleri tek adreste
 * toplanır.
 *
 * Hiçbir içerik silinmez, hiçbir adres kırılmaz. Kopya okunabilir kalır;
 * yalnız arama motoruna "asıl adres şu" der.
 */
final class SeoRecipeDuplicateConsolidation extends Command
{
    protected $signature = 'seo:recipe-duplicate-consolidation {--apply : Değişiklikleri yaz. Varsayılan yalnız rapordur.}';

    protected $description = 'Şablon tarif kopyalarını aynı adlı gerçek tarife kanonik olarak bağlar.';

    public function handle(RecipeQualityGateService $kapi, SeoMetadataService $meta): int
    {
        $yaz = (bool) $this->option('apply');
        $olcumler = $kapi->olc();
        $tarifler = Recipe::query()->where('status', 'published')->with(['seoMetadata', 'steps'])->get();

        $iyiler = [];
        $kopyalar = [];

        foreach ($tarifler as $tarif) {
            $ad = $this->yemekAdi($tarif);

            if ($kapi->sablonMu($olcumler[$tarif->id] ?? null) || $kapi->eksikMi($tarif)) {
                $kopyalar[] = [$tarif, $ad];

                continue;
            }

            // Aynı ada birden çok iyi tarif varsa en eski yayın kalır: kopya
            // en uzun süredir yayında olan adrese bağlanmalı.
            $mevcut = $iyiler[$ad] ?? null;

            if ($mevcut === null || ($tarif->published_at !== null && $mevcut->published_at !== null && $tarif->published_at->lt($mevcut->published_at))) {
                $iyiler[$ad] = $tarif;
            }
        }

        $baglanan = [];

        foreach ($kopyalar as [$kopya, $ad]) {
            $es = $iyiler[$ad] ?? null;

            if ($es === null || $es->getKey() === $kopya->getKey()) {
                continue;
            }

            $hedef = route('recipes.show', $es);

            if ((string) $kopya->seoMetadata?->canonical_override === $hedef) {
                continue;
            }

            $baglanan[] = $kopya->slug.' -> '.$es->slug;

            if ($yaz) {
                $meta->update($kopya, ['canonical_override' => $hedef, 'robots' => 'noindex,follow']);
            }
        }

        $this->components->info(count($baglanan).' kopya tarif gerçek eşine bağlanacak ('.count($iyiler).' gerçek tarif, '.count($kopyalar).' kapıya takılan).');

        foreach (array_slice($baglanan, 0, 8) as $satir) {
            $this->line('  · '.$satir);
        }

        if (! $yaz) {
            $this->line('Yazmak için: php artisan seo:recipe-duplicate-consolidation --apply');
        }

        return self::SUCCESS;
    }

    /** Parantezli ek ve baştaki/sondaki boşluk yemeğin adını değiştirmez. */
    private function yemekAdi(Recipe $tarif): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s*\(.*$/u', '', (string) $tarif->title)));
    }
}
