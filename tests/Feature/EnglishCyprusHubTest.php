<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * İngilizce Kıbrıs hub'ı İngilizce arama davranışına göre başlık taşır.
 *
 * "Kıbrıs Mutfağı Recipes" ne kullanıcıyla ne sorguyla eşleşiyordu:
 * İngilizce SERP "Cypriot/Cyprus" terimleriyle arıyor. TR dalı değişmez;
 * TR para kelimeler regresyon bekçisindedir.
 */
final class EnglishCyprusHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_hub_uses_cypriot_terms(): void
    {
        $this->ingilizceMutfak();

        // EN kanonik yol yerelleşmiş segmenttedir.
        $icerik = (string) $this->get('/en/cuisines/kibris-mutfagi')->assertOk()->getContent();

        $this->assertStringContainsString('Cypriot Recipes – Traditional Cyprus Food | House Chef Cyprus', $icerik);
        $this->assertStringContainsString('>Cypriot Recipes<', $icerik);
        $this->assertStringContainsString('halloumi', $icerik);
    }

    public function test_english_hub_has_its_own_faq(): void
    {
        $this->ingilizceMutfak();

        $icerik = (string) $this->get('/en/cuisines/kibris-mutfagi')->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"FAQPage"', $icerik);
        $this->assertStringContainsString('Is sheftalia made with peaches?', $icerik);
        $this->assertStringNotContainsString('Şeftali kebabında şeftali var mı?', $icerik);
    }

    public function test_turkish_hub_is_untouched(): void
    {
        $mutfak = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);
        for ($i = 0; $i < 5; $i++) {
            Recipe::query()->create($this->tarifAlanlari($mutfak->id, 'Kıbrıs Tarifi '.$i));
        }

        $icerik = (string) $this->get('/mutfaklar/kibris-mutfagi')->assertOk()->getContent();

        $this->assertStringContainsString('Kıbrıs Yemekleri ve Yemek Tarifleri | House Chef Cyprus', $icerik);
        $this->assertStringContainsString('Şeftali kebabında şeftali var mı?', $icerik);
        $this->assertStringNotContainsString('Cypriot Recipes – Traditional Cyprus Food', $icerik);
    }

    private function ingilizceMutfak(): void
    {
        $mutfak = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);
        for ($i = 0; $i < 5; $i++) {
            $tarif = Recipe::query()->create($this->tarifAlanlari($mutfak->id, 'Kıbrıs Tarifi '.$i));
            foreach (['title' => 'Cyprus recipe '.$tarif->id, 'excerpt' => 'Summary.', 'description' => 'Description.'] as $alan => $deger) {
                $tarif->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
            }
        }
    }

    /** @return array<string, mixed> */
    private function tarifAlanlari(int $mutfakId, string $baslik): array
    {
        return [
            'cuisine_id' => $mutfakId,
            'title' => $baslik,
            'slug' => 'tarif-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'servings' => 4,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ];
    }
}
