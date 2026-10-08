<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Yazı tarihi sayfanın dilinde basılır.
 *
 * 8 Ekim 2026 denetimi: format('d F Y') her dilde İngilizce ay adı
 * basıyordu; Türkçe sayfada "25 September 2026" görünüyordu.
 */
final class BlogDateSpeaksPageLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_yayin_tarihi_sayfanin_dilinde_basilir(): void
    {
        $yazi = BlogPost::query()->create([
            'title' => 'Hellim rehberi', 'slug' => 'hellim-rehberi', 'excerpt' => 'Özet.', 'body' => 'Gövde metni.',
            'status' => 'published', 'published_at' => '2026-10-05 10:00:00',
        ]);
        foreach (['title' => 'Halloumi-Leitfaden', 'excerpt' => 'Zusammenfassung.', 'body' => 'Textkörper.'] as $alan => $deger) {
            $yazi->translations()->create(['locale' => 'de', 'field' => $alan, 'value' => $deger]);
        }

        $this->get('/blog/hellim-rehberi')->assertOk()->assertSee('05 Ekim 2026')->assertDontSee('05 October 2026');
        $this->get('/de/blog/hellim-rehberi')->assertOk()->assertSee('05 Oktober 2026')->assertDontSee('05 October 2026');
    }
}
