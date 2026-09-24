<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Blog sayfasında tek H1 vardır: yazının başlığı.
 *
 * 14 Eylül 2026'da yayındaki 248 yazının 177'sinin gövdesi Markdown "# Başlık"
 * satırıyla başlıyordu ve sayfada ikinci bir H1 basılıyordu.
 */
final class BlogBodyHasNoSecondH1Test extends TestCase
{
    use RefreshDatabase;

    private function yazi(string $govde): BlogPost
    {
        return BlogPost::query()->create([
            'title' => 'Fırın Dereceleri Rehberi',
            'slug' => 'firin-dereceleri-'.uniqid(),
            'excerpt' => 'Özet.',
            'body' => $govde,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_govde_basliklari_bir_kademe_iner(): void
    {
        $html = $this->yazi("# Fırın Dereceleri\n\nGiriş.\n\n## Kek\n\nMetin.\n\n###### En alt\n\nSon.")->renderedBody();

        $this->assertStringNotContainsString('<h1', $html);
        $this->assertStringContainsString('<h2>Fırın Dereceleri</h2>', $html);
        $this->assertStringContainsString('<h3>Kek</h3>', $html);
        // H6 zaten en alt kademe; H7 diye bir öğe yok.
        $this->assertStringContainsString('<h6>En alt</h6>', $html);
    }

    public function test_zaten_dogru_yazilmis_govde_kaydirilmaz(): void
    {
        // "##" ile başlayan gövde doğrudur. Onu da kaydırmak ara başlıkları
        // H3'e indirir ve sayfa H1'den H3'e atlardı.
        $html = $this->yazi("## Ara başlık\n\nMetin.\n\n### Alt başlık\n\nSon.")->renderedBody();

        $this->assertStringContainsString('<h2>Ara başlık</h2>', $html);
        $this->assertStringContainsString('<h3>Alt başlık</h3>', $html);
    }

    public function test_sayfada_tek_h1_basilir(): void
    {
        $yazi = $this->yazi("# Fırın Dereceleri\n\n".str_repeat('Uzun paragraf metni. ', 40));

        $sayfa = $this->get('/blog/'.$yazi->slug)->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/i', (string) $sayfa), 'Sayfada birden fazla H1 var.');
    }

    public function test_gövdeye_gomulu_html_hala_temizlenir(): void
    {
        // Başlık kaydırma işaretleme güvenliğini gevşetmemeli.
        $html = $this->yazi("# Başlık\n\n<script>alert(1)</script>\n\n<h1>Gizli</h1>")->renderedBody();

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<h1', $html);
    }
}
