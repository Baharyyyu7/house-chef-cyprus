<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomeBlogCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bölüm yalnız yayındaki yazı varsa render oluyor; yazı eklemezsek
        // testler boşluğa geçer ve hiçbir şey doğrulamaz.
        BlogPost::query()->create([
            'title' => 'Kıbrıs mutfağında zeytinyağı',
            'slug' => 'kibris-mutfaginda-zeytinyagi',
            'excerpt' => 'Yerel zeytinyağının sofradaki yeri.',
            'body' => str_repeat('Zeytinyağı üzerine uzun bir not. ', 100),
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_the_section_actually_rendered(): void
    {
        // Diğer testlerin boşluğa geçmediğini garanti eder.
        $this->get(route('home'))->assertOk()->assertSee('Kıbrıs mutfağında zeytinyağı');
    }

    public function test_blog_cards_lift_like_recipe_cards_do(): void
    {
        // Blog kartları tarif kartlarıyla aynı tabanı (hc-v3-card)
        // paylaşıyordu ama --interactive almıyordu: yan yana duran iki kart
        // imleç altında farklı davranıyordu.
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('hc-v3-card hc-v3-card--interactive', false);
    }

    public function test_a_blog_card_is_one_stop_for_the_keyboard_not_three(): void
    {
        // Kart başına aynı hedefe üç bağlantı vardı (görsel, başlık, "oku"),
        // yani üç kartlık bölümü klavyeyle geçmek dokuz durak sürüyordu.
        // Görsel bağlantısı artık odak sırasının dışında.
        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('tabindex="-1" aria-hidden="true"', $icerik);
    }

    public function test_blog_and_recipe_cards_share_one_image_ratio(): void
    {
        // Anasayfada yan yana duran kartlar 16/9 ve 4/3 karışıyordu.
        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('aspect-[16/9]', $icerik);
    }
}
