<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\RecipeRating;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

/**
 * Üyeliksiz 5 yıldız oylama: tarif başına ziyaretçi başına tek oy.
 *
 * Kimlik uzun ömürlü `hc_voter` çerezi + IP karmasıdır; ham IP saklanmaz.
 * İkinci oy yeni kayıt değil güncellemedir, toplam şişmez. Oy yoksa şemada
 * aggregateRating basılmaz; sahte 5.0 ilan edilmez.
 */
final class RecipeRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_oy_kaydedilir_ve_secmen_cerezi_basilir(): void
    {
        $tarif = $this->tarif();

        $yanit = $this->postJson(route('recipes.rate', $tarif), ['rating' => 5])
            ->assertOk()
            ->assertJson(['average' => 5.0, 'count' => 1, 'user_rating' => 5]);

        $yanit->assertCookie('hc_voter');
        $this->assertDatabaseHas('recipe_ratings', ['recipe_id' => $tarif->id, 'rating' => 5]);
    }

    public function test_ikinci_oy_guncellemedir_yeni_kayit_degil(): void
    {
        $tarif = $this->tarif();

        // İlk oy çerezi başlatır; yanıtın şifreli çerez değeri ikinci
        // istekte aynen geri gönderilir (gerçek tarayıcı davranışı).
        $ilk = $this->postJson(route('recipes.rate', $tarif), ['rating' => 2])->assertOk();
        $sifreli = $ilk->getCookie('hc_voter', false)->getValue();

        // postJson çerezleri yalnız withCredentials ile gönderir.
        $yanit = $this->withUnencryptedCookie('hc_voter', $sifreli)->withCredentials()->postJson(route('recipes.rate', $tarif), ['rating' => 4])
            ->assertOk()
            ->assertJson(['average' => 4.0, 'count' => 1, 'user_rating' => 4]);

        $this->assertSame(1, RecipeRating::query()->where('recipe_id', $tarif->id)->count());
    }

    public function test_aralik_disi_oy_reddedilir(): void
    {
        $tarif = $this->tarif();

        $this->postJson(route('recipes.rate', $tarif), ['rating' => 0])->assertStatus(422);
        $this->postJson(route('recipes.rate', $tarif), ['rating' => 6])->assertStatus(422);
        $this->postJson(route('recipes.rate', $tarif), [])->assertStatus(422);
        $this->assertSame(0, RecipeRating::query()->count());
    }

    public function test_yayinda_olmayan_tarif_oylanamaz(): void
    {
        $tarif = $this->tarif();
        $tarif->update(['status' => 'draft']);

        $this->postJson(route('recipes.rate', $tarif), ['rating' => 5])->assertNotFound();
    }

    public function test_ham_ip_saklanmaz(): void
    {
        $tarif = $this->tarif();

        $this->postJson(route('recipes.rate', $tarif), ['rating' => 3])->assertOk();

        $kayit = RecipeRating::query()->firstOrFail();
        $this->assertSame(64, strlen($kayit->ip_hash));
        $this->assertStringNotContainsString('127.0.0.1', $kayit->ip_hash);
    }

    public function test_oylanan_tarifin_semasi_yildiz_tasir(): void
    {
        $tarif = $this->tarif();
        $this->makeRecipePubliclyEligible($tarif);
        foreach ([5, 3, 4, 4, 4] as $i => $puan) {
            RecipeRating::query()->create(['recipe_id' => $tarif->id, 'rating' => $puan, 'voter_key' => 'secmen-'.$i, 'ip_hash' => 'ag-'.$i]);
        }

        $dugum = $this->recipeNode($tarif);

        $this->assertSame('AggregateRating', $dugum['aggregateRating']['@type'] ?? null);
        $this->assertSame('4.0', $dugum['aggregateRating']['ratingValue'] ?? null);
        $this->assertSame('5', (string) ($dugum['aggregateRating']['reviewCount'] ?? null));
        $this->assertSame('5', (string) ($dugum['aggregateRating']['bestRating'] ?? null));
    }

    public function test_bes_farkli_agdan_az_oy_semaya_yildiz_basmaz(): void
    {
        // Beş oy var ama ikisi aynı ağdan: dört farklı seçmen, Google'a
        // tarifin genel değerlendirmesi diye bildirilmez.
        $tarif = $this->tarif();
        $this->makeRecipePubliclyEligible($tarif);
        foreach (['ag-1', 'ag-2', 'ag-3', 'ag-4', 'ag-4'] as $i => $ag) {
            RecipeRating::query()->create(['recipe_id' => $tarif->id, 'rating' => 5, 'voter_key' => 'secmen-'.$i, 'ip_hash' => $ag]);
        }

        $this->assertArrayNotHasKey('aggregateRating', $this->recipeNode($tarif));
    }

    public function test_cerezi_silip_ayni_agdan_oylayan_yeni_secmen_sayilmaz(): void
    {
        $tarif = $this->tarif();

        $this->postJson(route('recipes.rate', $tarif), ['rating' => 1])->assertOk();
        // Çerezsiz ikinci istek: aynı IP, yeni uuid üretilir ama kayıt eklenmez.
        $this->postJson(route('recipes.rate', $tarif), ['rating' => 5])
            ->assertOk()
            ->assertJson(['average' => 5.0, 'count' => 1, 'user_rating' => 5]);

        $this->assertSame(1, RecipeRating::query()->where('recipe_id', $tarif->id)->count());
    }

    public function test_farkli_agdan_gelen_oy_ayri_sayilir(): void
    {
        $tarif = $this->tarif();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])->postJson(route('recipes.rate', $tarif), ['rating' => 2])->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20'])->postJson(route('recipes.rate', $tarif), ['rating' => 4])
            ->assertOk()
            ->assertJson(['average' => 3.0, 'count' => 2]);
    }

    public function test_secmen_cerezi_yalniz_https_ile_gider(): void
    {
        $tarif = $this->tarif();

        $cerez = $this->postJson(route('recipes.rate', $tarif), ['rating' => 3])->getCookie('hc_voter', false);

        $this->assertNotNull($cerez);
        $this->assertTrue($cerez->isSecure());
        $this->assertTrue($cerez->isHttpOnly());
    }

    public function test_oylama_ucu_csrf_korumasindan_muaf_degil(): void
    {
        // Test ortamında CSRF ara katmanı kendini atlar; gerçek davranışı
        // görmek için o kısayol kapatılmış bir kopyası doğrudan çalıştırılır.
        $tarif = $this->tarif();
        $ara = new class(app(), app('encrypter')) extends PreventRequestForgery
        {
            protected function runningUnitTests()
            {
                return false;
            }
        };
        $istek = Request::create(route('recipes.rate', $tarif, false), 'POST', ['rating' => 5]);
        $istek->setLaravelSession(app('session.store'));

        $this->expectException(TokenMismatchException::class);
        $ara->handle($istek, fn () => response('gecti'));
    }

    public function test_oysuz_tarifin_semasinda_yildiz_yoktur(): void
    {
        $tarif = $this->tarif();
        $this->makeRecipePubliclyEligible($tarif);

        $dugum = $this->recipeNode($tarif);

        $this->assertArrayNotHasKey('aggregateRating', $dugum);
    }

    private function tarif(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Yayla Çorbası',
            'slug' => 'yayla-corbasi-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Yeterince uzun bir açıklama metni burada duruyor.',
            'servings' => 4,
            'prep_minutes' => 10,
            'cook_minutes' => 20,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }

    /** @return array<string, mixed> */
    private function recipeNode(Recipe $tarif): array
    {
        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $icerik, $eslesme);

        foreach ($eslesme[1] as $ham) {
            $veri = json_decode(trim($ham), true);

            foreach ($veri['@graph'] ?? [$veri] as $dugum) {
                if (($dugum['@type'] ?? null) === 'Recipe') {
                    return $dugum;
                }
            }
        }

        $this->fail('Sayfada Recipe işaretlemesi bulunamadı.');
    }
}
