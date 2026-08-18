<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gizlilik metni reklamı hiç anlatmıyordu ve bu doğruydu: reklam yoktu.
 * Metnin kendi ilkesi de bunu söylüyor — "Kullanılmayan analiz, reklam veya
 * takip sistemleri bu metinde kullanılıyormuş gibi gösterilmez."
 *
 * Reklam açıldığı anda aynı metin yanlışa döner: Google veri alır, üçüncü
 * taraf çerezleri yazılır ve politika bunların hiçbirini anlatmaz. Google
 * yayıncıdan bunu açıkça anlatmasını istiyor; anlatmayan bir politika,
 * incelemedeki bir hesabın reddedilme sebeplerinden biridir.
 *
 * Bölüm bu yüzden reklamın durumuna bağlı. İki yönü de sabitliyoruz.
 */
final class PrivacyPolicyCoversAdvertisingWhenAdsRunTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_policy_explains_advertising_once_ads_are_switched_on(): void
    {
        $this->reklamDurumu(true);

        $yanit = $this->get(route('legal.center'))->assertOk();

        $yanit->assertSee('Reklamlar ve Üçüncü Taraflar');
        // Google'ın istediği asıl açıklama: önceki ziyaretlere dayalı çerez.
        $yanit->assertSee('önceki ziyaretlere dayanarak reklam sunmak amacıyla çerez kullanabilir', false);
        // Kullanıcının çıkış yolu gösterilmeli.
        $yanit->assertSee('adssettings.google.com', false);
        $yanit->assertSee('policies.google.com/technologies/ads', false);
    }

    public function test_it_does_not_claim_advertising_that_is_not_running(): void
    {
        $this->reklamDurumu(false);

        $this->get(route('legal.center'))
            ->assertOk()
            ->assertDontSee('Reklamlar ve Üçüncü Taraflar')
            ->assertDontSee('adssettings.google.com', false);
    }

    private function reklamDurumu(bool $acik): void
    {
        putenv('GOOGLE_ADSENSE_ENABLED='.($acik ? 'true' : 'false'));
        $_ENV['GOOGLE_ADSENSE_ENABLED'] = $acik ? 'true' : 'false';

        // Yapılandırma dosyası env'i okuma anında değerlendirir.
        config()->set('legal-center', require base_path('config/legal-center.php'));
    }
}
