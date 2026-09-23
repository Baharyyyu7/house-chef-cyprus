<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dil seçici, sayfanın gerçekten var olduğu dilleri gösterir.
 *
 * 23 Eylül 2026'da ölçüldü: yalnız Türkçe ve İngilizcesi olan bir yazının
 * seçicisinde beş dil daha bağlantı olarak duruyordu ve hepsi 404 veriyordu.
 * hreflang kümesi ve site haritası bu ölçütü zaten uyguluyordu; seçici
 * uygulamıyordu, yani üç yer üç farklı şey söylüyordu.
 */
final class LocaleSwitcherOnlyOffersExistingPagesTest extends TestCase
{
    use RefreshDatabase;

    private function yazi(): BlogPost
    {
        $post = BlogPost::query()->create([
            'title' => 'Hellim Rehberi',
            'slug' => 'hellim-rehberi',
            'excerpt' => 'Özet.',
            'body' => str_repeat('Bu yazının gövdesi kapıdan geçecek uzunlukta yazıldı. ', 60),
            'status' => 'published',
            'published_at' => now()->subDay(),
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
            'editorial_status' => 'approved',
        ]);

        foreach (['title' => 'Halloumi Guide', 'excerpt' => 'Summary.', 'body' => str_repeat('The body of this post is long enough to pass the gate. ', 60)] as $alan => $deger) {
            $post->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }

        return $post->fresh();
    }

    public function test_cevirisi_olmayan_dile_baglanti_verilmez(): void
    {
        $post = $this->yazi();

        $icerik = (string) $this->get(route('blog.show', $post))->assertOk()->getContent();

        $this->assertStringContainsString('/en/blog/'.$post->slug, $icerik);
        foreach (['de', 'ru', 'pl', 'pt', 'fa'] as $kod) {
            $this->assertStringNotContainsString('/'.$kod.'/blog/'.$post->slug, $icerik, $kod.' çevirisi yok ama bağlantı basılmış');
        }
    }

    public function test_icerik_disi_sayfada_butun_diller_durur(): void
    {
        // Liste ve hub sayfalarının her dilde karşılığı var; orada kısıt yok.
        $icerik = (string) $this->get(route('blog.index'))->assertOk()->getContent();

        $this->assertStringContainsString('/en/blog', $icerik);
        $this->assertStringContainsString('/de/blog', $icerik);
    }
}
