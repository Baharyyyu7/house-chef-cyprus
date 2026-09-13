<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Integrity\IntegrityReportLog;
use App\Services\Seo\BacklinkGrowthService;
use App\Services\Seo\BacklinkMarketService;
use App\Services\Seo\LocaleGrowthService;
use Illuminate\Console\Command;

/**
 * Dil dil büyüme raporu.
 *
 * Her gün çalışır ve her açık dil için tek satırlık bir teşhis bırakır.
 * Amacı tabloyu güzel göstermek değil, bir dilin sessizce durmasını
 * imkânsızlaştırmak: açık ama içeriği olmayan, ya da içeriği olup hiç
 * gösterim almayan bir dil toplamların içinde kaybolur, burada kaybolmaz.
 */
final class SeoLocaleGrowth extends Command
{
    protected $signature = 'seo:locale-growth
        {--days=28 : Kaç günlük arama verisine bakılacak}
        {--json : Makine okunabilir çıktı}';

    protected $description = 'Büyümeyi dil dil ölçer ve her dilin önündeki asıl engeli yazar.';

    public function handle(
        LocaleGrowthService $growth,
        BacklinkMarketService $pazarlar,
        BacklinkGrowthService $backlinks,
        IntegrityReportLog $log
    ): int
    {
        $gun = max(1, (int) $this->option('days'));
        $tablo = $growth->tablo($gun);

        // Backlink adayları pazarına göre bölünür. "Hazır" olmayan aday
        // kayıp değil, sıradaki iş: o pazarın dilinde o sayfayı çevirmek.
        // Çeviri emeğini hangi sayfaya harcamak gerektiğini söyleyen sinyal
        // bu; adayı olan ama sayfası olmayan pazar en acil olandır.
        $dagilim = $pazarlar->pazarDagilimi($backlinks->prospects());

        foreach ($tablo as $dil => $satir) {
            $tablo[$dil]['backlink_adaylari'] = $dagilim[$dil]['total'] ?? 0;
            $tablo[$dil]['backlink_hazir'] = $dagilim[$dil]['ready'] ?? 0;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($tablo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->table(
            ['Dil', 'Tarif', 'Yazı', 'Tıklama', 'Gösterim', 'CTR %', 'Sıra', 'Aday', 'Hazır', 'Darboğaz'],
            array_map(static fn (array $s): array => [
                $s['locale'],
                $s['recipes'],
                $s['posts'],
                $s['clicks'],
                $s['impressions'],
                $s['ctr'],
                $s['position'] ?? '-',
                $s['backlink_adaylari'],
                $s['backlink_hazir'],
                $s['darbogaz'],
            ], $tablo)
        );

        // Büyüyen dil sayısı tek başına bir başarı ölçüsü değil ama tek
        // sayıyla izlenebilen tek şey; ayrıntı payload'da duruyor.
        $buyuyen = count(array_filter($tablo, static fn (array $s): bool => $s['darbogaz'] === 'büyüyor'));
        $takilan = count($tablo) - $buyuyen;

        $log->record(
            'locale_growth',
            $takilan === 0 ? 'ok' : 'attention',
            $takilan,
            ['days' => $gun, 'locales' => $tablo]
        );

        return self::SUCCESS;
    }
}
