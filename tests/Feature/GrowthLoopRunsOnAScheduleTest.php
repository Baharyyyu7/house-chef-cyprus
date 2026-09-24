<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

/**
 * Büyüme döngüsünün parçaları gerçekten zamanlanmıştır.
 *
 * Yazılmış ama çağrılmayan bir komut çalışmayan bir komuttur ve bunu kimse
 * fark etmez: hata vermez, test kırmaz, sadece hiç olmaz. `seo:indexnow-submit`
 * tam olarak bu durumdaydı - açıklamasında "anında bildirir" yazıyordu ve
 * zamanlayıcıda hiç yoktu.
 */
final class GrowthLoopRunsOnAScheduleTest extends TestCase
{
    /** @return list<string> */
    private function zamanlanmisIsler(): array
    {
        return array_values(array_filter(array_map(
            static fn ($olay): ?string => $olay->description ?: ($olay->getSummaryForDisplay() ?: null),
            app(Schedule::class)->events()
        )));
    }

    /** @return list<string> */
    private function isAdlari(): array
    {
        return array_values(array_filter(array_map(
            static fn ($olay): ?string => $olay->description,
            app(Schedule::class)->events()
        )));
    }

    public function test_indexnow_zamanlanmistir(): void
    {
        $this->assertContains(
            'seo_indexnow_submit',
            $this->isAdlari(),
            'IndexNow zamanlayıcıda yok; arama motorlarına hiçbir zaman haber gitmez.'
        );
    }

    public function test_dil_buyume_olcumu_zamanlanmistir(): void
    {
        $this->assertContains(
            'seo_locale_growth',
            $this->isAdlari(),
            'Dil başına büyüme ölçülmüyorsa büyüme motoru dilleri göremez.'
        );
    }

    public function test_olcum_brifingden_once_calisir(): void
    {
        // Brifing taze tabloyu okumalı; ters sırada her gün bir gün eski
        // tabloyu raporlardı ve bu hiçbir yerde hata olarak görünmezdi.
        $olaylar = collect(app(Schedule::class)->events())
            ->keyBy(fn ($olay): string => (string) $olay->description);

        $olcum = $olaylar->get('seo_locale_growth');
        $brifing = $olaylar->get('seo_morning_brief');

        $this->assertNotNull($olcum);
        $this->assertNotNull($brifing);

        $this->assertLessThan(
            $this->gununDakikasi($brifing->expression),
            $this->gununDakikasi($olcum->expression),
            'Ölçüm sabah brifingden sonra çalışıyor; brifing bir gün eski tabloyu okur.'
        );
    }

    /**
     * Cron ifadesinin gün içindeki dakikası.
     *
     * İfadeleri metin olarak karşılaştırmak yanlış: "53 6" ile "5 7"
     * kıyaslandığında metin sıralaması 07:05'i 06:53'ten önce sayar.
     * Saatler bir gün değiştiğinde test sessizce yanlış cevap verirdi.
     */
    private function gununDakikasi(string $ifade): int
    {
        [$dakika, $saat] = array_pad(explode(' ', $ifade), 2, '0');

        return ((int) $saat) * 60 + (int) $dakika;
    }
}
