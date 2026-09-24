<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\Integrity\IntegrityReportLog;
use App\Services\Seo\BlogOriginalityService;
use Illuminate\Console\Command;

/**
 * Yayındaki blog yazılarında birebir tekrar eden paragrafları bulur.
 *
 * Tekrar eden yazının `originality_status` alanı "duplicate" olur. Mevcut
 * indekslenebilirlik kapısı (BlogIndexability) bunu okur: yazı yayında ve
 * okunabilir kalır ama noindex olur ve site haritasından çıkar. Hiçbir içerik
 * silinmez.
 *
 * Tekrarı düzelmiş bir yazı kendiliğinden "passed" olmaz, "pending" olur:
 * özgünlük onayı bir insan kararıdır ve bu komut yalnız reddetme yetkisi taşır.
 */
final class SeoOriginalityScan extends Command
{
    protected $signature = 'seo:originality-scan {--apply : Durumları yaz. Varsayılan yalnız rapordur.}';

    protected $description = 'Blog yazılarında birebir tekrar eden paragrafları ölçer ve tekrar içerikleri indeksten çıkarır.';

    public function handle(BlogOriginalityService $servis, IntegrityReportLog $log): int
    {
        $olcumler = $servis->olc();
        $yaz = (bool) $this->option('apply');
        $tekrar = [];
        $duzelen = [];

        foreach (BlogPost::query()->publiclyVisible()->get(['id', 'slug', 'originality_status']) as $yazi) {
            $olcum = $olcumler[$yazi->id] ?? null;

            if ($olcum === null) {
                continue;
            }

            if ($servis->tekrarMi($olcum)) {
                $tekrar[] = ['slug' => $yazi->slug, 'share' => $olcum['share']];

                if ($yaz && $yazi->originality_status !== 'duplicate') {
                    $yazi->forceFill(['originality_status' => 'duplicate'])->save();
                }

                continue;
            }

            if ($yazi->originality_status === 'duplicate') {
                $duzelen[] = $yazi->slug;

                if ($yaz) {
                    $yazi->forceFill(['originality_status' => 'pending'])->save();
                }
            }
        }

        $this->components->info(count($olcumler).' yazı ölçüldü; '.count($tekrar).' yazı tekrar içeriyor, '.count($duzelen).' yazının tekrarı düzelmiş.');

        foreach (array_slice($tekrar, 0, 10) as $satir) {
            $this->line('  · '.$satir['slug'].' (%'.round($satir['share'] * 100).' tekrar)');
        }

        if (! $yaz) {
            $this->line('Yazmak için: php artisan seo:originality-scan --apply');

            return self::SUCCESS;
        }

        $log->record(
            'blog_originality',
            $tekrar === [] ? 'ok' : 'attention',
            count($tekrar),
            ['measured' => count($olcumler), 'duplicates' => array_slice($tekrar, 0, 200), 'recovered' => $duzelen]
        );

        return self::SUCCESS;
    }
}
