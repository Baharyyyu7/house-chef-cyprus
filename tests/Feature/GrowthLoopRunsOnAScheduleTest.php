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

    public function test_sabah_brifingleri_tek_operasyon_ozetine_indirildi(): void
    {
        // Kullanıcı kararı (8 Ekim denetimi): altı "sabah özeti" görevinin
        // çıktısı hiçbir yere gitmiyordu. Ölçüm-brifing sırasını savunan eski
        // test bu yüzden çevrildi; yerine sorunları e-postayla bildiren tek
        // günlük özet var.
        $adlar = $this->isAdlari();

        $this->assertNotContains('seo_morning_brief', $adlar);
        $this->assertContains('seo_locale_growth', $adlar);
        $this->assertContains('operations_digest', $adlar);
    }
}
