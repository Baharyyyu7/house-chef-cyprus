<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /blog ilk sayfasında karuseldeki sekiz yazı ızgarada ikinci kez
 * basılıyordu; her kartta "Mutfak notu" etiketi ve kategori üç kez geçiyordu;
 * özet kelimenin ortasında kesiliyordu.
 */
final class BlogListShowsEachPostOnceTest extends TestCase
{
    use RefreshDatabase;

    private function yazi(int $i, string $ozet = 'Özet.'): BlogPost
    {
        return BlogPost::query()->create([
            'title' => 'Yazı başlığı '.$i,
            'slug' => 'yazi-'.$i,
            'excerpt' => $ozet,
            'body' => str_repeat('Bu yazının gövdesi kapıdan geçecek uzunlukta yazıldı. ', 60),
            'status' => 'published',
            'published_at' => now()->subDays($i),
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
            'editorial_status' => 'approved',
        ]);
    }

    public function test_karusel_yazisi_izgarada_tekrar_etmez_ve_hicbiri_dusmez(): void
    {
        $yazilar = collect(range(1, 10))->map(fn (int $i): BlogPost => $this->yazi($i));

        $icerik = (string) $this->get(route('blog.index'))->assertOk()->getContent();

        foreach ($yazilar as $yazi) {
            $this->assertStringContainsString(route('blog.show', $yazi), $icerik);
            // Karusel kartı + ızgara kartı (görsel, başlık, düğme) = en fazla 3 bağlantı
            // tek yerden; iki yerde birden basılan yazı 4+ olurdu.
            $this->assertLessThanOrEqual(3, substr_count($icerik, 'href="'.route('blog.show', $yazi).'"'), $yazi->slug.' iki kez basılmış');
        }
        // Kategorisiz yazıda "Mutfak notu" kart başına bir kez (görsel rozeti):
        // 6 karusel + 4 ızgara kartı.
        $this->assertSame(10, substr_count($icerik, 'Mutfak notu'));
    }

    public function test_ozet_kelime_sinirinda_kisalir(): void
    {
        $this->yazi(1, str_repeat('Hellim ızgarada altın rengini alana kadar pişirilir ', 6).'sonunda.');

        $icerik = (string) $this->get(route('blog.index'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/pişirilir…|alana…|kadar…|ızgarada…|rengini…|Hellim…|altın…/u', $icerik);
    }
}
