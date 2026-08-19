<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdSlotIdsAreRealTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_slot_carries_a_real_id_not_a_placeholder(): void
    {
        // Yer tutucu kimlikle ("12345678...") istek atmak, incelemedeki bir
        // AdSense hesabının reddedilmesinin bilinen yollarından biri.
        foreach (config('monetization.slots') as $ad => $yuva) {
            $kimlik = (string) $yuva['id'];

            $this->assertMatchesRegularExpression('/^\d{6,}$/', $kimlik, $ad.' yuvası geçersiz.');
            $this->assertStringStartsNotWith('12345678', $kimlik, $ad.' yuvası hâlâ yer tutucu.');
        }
    }

    public function test_the_in_feed_slot_has_the_layout_key_its_format_requires(): void
    {
        // Feed biçimi düzen anahtarı olmadan basılırsa birim ya hiç dolmaz ya
        // da bozuk görünür.
        $this->assertNotSame('', (string) config('monetization.slots.in_feed.layout_key'));
    }

    public function test_no_ad_is_printed_without_advertising_consent(): void
    {
        config()->set('integrations.google.adsense.enabled', true);
        config()->set('integrations.google.adsense.client_id', 'ca-pub-1614195335767159');
        config()->set('integrations.google.adsense.certified_cmp_enabled', true);
        config()->set('integrations.google.adsense.cmp_provider', 'custom');

        $this->get(route('recipes.index'))
            ->assertOk()
            ->assertDontSee('adsbygoogle', false);
    }

    public function test_slots_are_placed_in_the_feed_when_consent_is_granted(): void
    {
        config()->set('integrations.google.adsense.enabled', true);
        config()->set('integrations.google.adsense.client_id', 'ca-pub-1614195335767159');
        config()->set('integrations.google.adsense.certified_cmp_enabled', true);
        config()->set('integrations.google.adsense.cmp_provider', 'google');

        $icerik = (string) $this->get(route('blog.index'))->assertOk()->getContent();

        // Reklam hiç yerleştirilmemişse yuva tanımlı olsa bile kimse görmez;
        // in_feed ve display uzun süre yapılandırmada durup hiçbir sayfada
        // kullanılmıyordu.
        $this->assertStringContainsString('adsbygoogle', $icerik);
    }
}
