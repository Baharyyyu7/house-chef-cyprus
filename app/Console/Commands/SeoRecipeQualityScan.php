<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Services\Integrity\IntegrityReportLog;
use App\Services\Seo\RecipeQualityGateService;
use App\Services\Seo\SeoMetadataService;
use Illuminate\Console\Command;

/**
 * Şablon ya da eksik tarifleri arama dizininden çıkarır.
 *
 * Tarifin `seo_metadata.robots` alanı "noindex,follow" olur: sayfa yayında ve
 * okunabilir kalır, site haritasından düşer (SitemapController noindex olanı
 * eler). Hiçbir içerik silinmez.
 *
 * Komutun yalnız reddetme yetkisi vardır. Düzelen bir tarif kendiliğinden
 * indekse geri dönmez; bu insan kararıdır ve rapor satırı olarak bildirilir.
 */
final class SeoRecipeQualityScan extends Command
{
    protected $signature = 'seo:recipe-quality-scan {--apply : Durumları yaz. Varsayılan yalnız rapordur.}';

    protected $description = 'Yayındaki tariflerde şablon metni ve eksik tarifi ölçer, bulduğunu indeks dışına alır.';

    public function handle(RecipeQualityGateService $kapi, SeoMetadataService $meta, IntegrityReportLog $log): int
    {
        $olcumler = $kapi->olc();
        $yaz = (bool) $this->option('apply');
        $sablon = [];
        $eksik = [];

        $tarifler = Recipe::query()
            ->where('status', 'published')
            ->with('seoMetadata')
            ->get();

        foreach ($tarifler as $tarif) {
            $sebep = null;

            if ($kapi->sablonMu($olcumler[$tarif->id] ?? null)) {
                $sebep = 'sablon';
                $sablon[] = $tarif->slug;
            } elseif ($kapi->eksikMi($tarif)) {
                $sebep = 'eksik';
                $eksik[] = $tarif->slug;
            }

            if ($sebep === null || ! $yaz) {
                continue;
            }

            if (! str_contains((string) $tarif->seoMetadata?->robots, 'noindex')) {
                $meta->update($tarif, ['robots' => 'noindex,follow']);
            }
        }

        $toplam = count($sablon) + count($eksik);

        $this->components->info($tarifler->count().' tarif ölçüldü; '.count($sablon).' şablon, '.count($eksik).' eksik tarif bulundu.');

        foreach (array_slice(array_merge($sablon, $eksik), 0, 10) as $slug) {
            $this->line('  · '.$slug);
        }

        if (! $yaz) {
            $this->line('Yazmak için: php artisan seo:recipe-quality-scan --apply');

            return self::SUCCESS;
        }

        $log->record(
            'recipe_quality',
            $toplam === 0 ? 'ok' : 'attention',
            $toplam,
            ['measured' => $tarifler->count(), 'template' => array_slice($sablon, 0, 200), 'incomplete' => array_slice($eksik, 0, 200)]
        );

        return self::SUCCESS;
    }
}
