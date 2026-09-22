<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Console\Commands\SeoHealRedirects;
use App\Models\BlogPost;
use App\Models\SlugRedirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aynı hedefe yığılan yönlendirme, eşleştirmenin bozulduğunun işaretidir.
 *
 * 22 Eylül 2026'da ölçüldü: 1.467 yönlendirme kaydının 1.357'si dokuz hedefe
 * yığılmıştı. Tek bir mercimek çorbası tarifine 999 ayrı adres yönleniyordu;
 * köz patlıcan sayfası kuru bakliyat yazısına gidiyordu. Yanlış yönlendirme
 * okuru da arama motorunu da 404'ten daha çok yanıltır.
 */
final class RedirectsDoNotPileOnOneTargetTest extends TestCase
{
    use RefreshDatabase;

    public function test_bir_hedefe_sinirdan_fazla_adres_baglanmaz(): void
    {
        $hedef = BlogPost::query()->create([
            'title' => 'Hedef Yazı', 'slug' => 'hedef-yazi', 'excerpt' => 'Ö', 'body' => '<p>G</p>',
            'status' => 'published', 'published_at' => now()->subDay(),
            'fact_check_status' => 'passed', 'originality_status' => 'passed', 'editorial_status' => 'approved',
        ]);

        for ($i = 1; $i <= SeoHealRedirects::HEDEF_BASINA_AZAMI; $i++) {
            SlugRedirect::query()->create([
                'model_type' => $hedef->getMorphClass(),
                'old_slug' => 'eski-adres-'.$i,
                'model_id' => $hedef->id,
            ]);
        }

        $taslak = BlogPost::query()->create([
            'title' => 'Hedef Yazı', 'slug' => 'hedef-yazi-ABC12', 'excerpt' => 'Ö', 'body' => '<p>G</p>',
            'status' => 'draft',
        ]);

        $this->artisan('seo:heal-redirects --apply')->assertSuccessful();

        $this->assertDatabaseMissing('slug_redirects', ['old_slug' => $taslak->slug]);
        $this->assertSame(
            SeoHealRedirects::HEDEF_BASINA_AZAMI,
            SlugRedirect::query()->where('model_id', $hedef->id)->count()
        );
    }
}
