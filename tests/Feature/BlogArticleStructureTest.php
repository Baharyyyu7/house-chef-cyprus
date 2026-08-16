<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gövde nl2br ile basıldığı sürece uzun bir yazı ara başlıksız tek blok metin
 * oluyordu; ne okunuyor ne de arama motoruna yapı veriyordu. FAQ alanı da
 * doluydu ama sayfada hiç gösterilmiyordu.
 */
final class BlogArticleStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_markdown_structure_is_rendered(): void
    {
        $post = $this->publish("## Ara başlık\n\nParagraf metni.\n\n- birinci madde\n- ikinci madde");

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee('<h2>Ara başlık</h2>', false)
            ->assertSee('<li>birinci madde</li>', false);
    }

    public function test_raw_html_in_the_body_is_stripped(): void
    {
        $post = $this->publish("Zararsız metin.\n\n<script>alert('x')</script>\n\n<img src=x onerror=alert(1)>");

        $response = $this->get(route('blog.show', $post))->assertOk();

        $response->assertDontSee('<script>alert', false);
        $response->assertDontSee('onerror=alert', false);
    }

    public function test_faq_blocks_appear_on_the_page_and_in_the_schema(): void
    {
        $post = $this->publish('Gövde.', [
            ['question' => 'Hellim neden erimez?', 'answer' => 'Peynir altı suyunda haşlandığı için protein yapısı ısıda çözülmez.'],
        ]);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee('Sık sorulan sorular', false)
            ->assertSee('Hellim neden erimez?', false)
            ->assertSee('FAQPage', false);
    }

    public function test_a_post_without_faq_blocks_omits_the_section(): void
    {
        // Footer'da da "Sık sorulan sorular" bağlantısı var; kontrol bölüme özel olmalı.
        $this->get(route('blog.show', $this->publish('Yalnız gövde.')))
            ->assertOk()
            ->assertDontSee('id="sss-basligi"', false)
            ->assertDontSee('FAQPage', false);
    }

    /** @param list<array{question:string,answer:string}> $faq */
    private function publish(string $body, array $faq = []): BlogPost
    {
        return BlogPost::query()->create([
            'author_id' => User::factory()->create()->id,
            'title' => 'Yapı Testi Yazısı',
            'slug' => 'yapi-testi-yazisi-'.uniqid(),
            'excerpt' => 'Kısa özet.',
            'body' => $body,
            'faq_blocks' => $faq,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }
}
