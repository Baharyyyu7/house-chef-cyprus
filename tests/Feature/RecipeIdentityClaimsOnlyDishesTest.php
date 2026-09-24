<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tarif düğümündeki sameAs bir KİMLİK iddiasıdır.
 *
 * "Bu sayfa o varlığın ta kendisidir" der. about içindeki sameAs ise "bu sayfa
 * o varlık hakkındadır" der. İkisi aynı şey değil.
 *
 * Şeftali kebabı tarifi gerçekten Sheftalia'dır - kimlik doğru. Hellim tost
 * ise Halloumi DEĞİLDİR; hellim onun malzemesidir. Oraya kimlik iddiası
 * koymak tarif sayfasını peynirin ansiklopedi maddesi ilan eder ve Google'a
 * yanlış varlık bildirir.
 *
 * Bu ayrım yalnız bir veri bayrağıyla duruyor (`is_dish`). Bayrak yeni bir
 * kayda yanlış konursa hiçbir şey kırılmaz, sessizce yanlış iddia edilir -
 * o yüzden test var.
 */
final class RecipeIdentityClaimsOnlyDishesTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function dugum(string $slug, string $baslik): array
    {
        $tarif = (new Recipe(['title' => $baslik, 'slug' => $slug]))
            ->setRelation('steps', collect())
            ->setRelation('cuisine', null)
            ->setRelation('category', null);

        return app(SeoSchemaService::class)->recipe($tarif, [], 'https://example.com/x.jpg')['@graph'][0];
    }

    public function test_yemegin_kendisi_olan_tarif_kimlik_iddia_eder(): void
    {
        $dugum = $this->dugum('kibris-seftali-kebabi', 'Şeftali Kebabı');

        $this->assertContains('https://en.wikipedia.org/wiki/Sheftalia', $dugum['sameAs'] ?? []);
    }

    public function test_malzemeye_isaret_eden_tarif_kimlik_iddia_etmez(): void
    {
        foreach ([
            'hellim-tost' => 'Hellim Tost',
            'hellimli-borek' => 'Hellimli Börek',
            'kibris-kolokas' => 'Kolokas',
            'magarina-bulli' => 'Magarina Bulli',
        ] as $slug => $baslik) {
            $dugum = $this->dugum($slug, $baslik);

            $this->assertArrayNotHasKey(
                'sameAs',
                $dugum,
                $slug.' tarifi, malzemesinin ansiklopedi maddesi olduğunu iddia etti.'
            );
        }
    }

    public function test_baglanti_kimlik_basilmasa_da_kaybolmaz(): void
    {
        // Kimlik iddiası yanlış olduğunda bağlantıyı atmıyoruz, doğru yere
        // koyuyoruz: "bu sayfa Halloumi hakkındadır" doğru bir cümledir.
        $dugum = $this->dugum('hellim-tost', 'Hellim Tost');

        $this->assertSame('Halloumi', $dugum['about']['name'] ?? null);
        $this->assertSame('https://en.wikipedia.org/wiki/Halloumi', $dugum['about']['sameAs'] ?? null);
    }
}
