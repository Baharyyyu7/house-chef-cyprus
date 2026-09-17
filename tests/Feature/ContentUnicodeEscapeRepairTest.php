<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Console\Commands\ContentFixUnicodeEscapes;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Metne sızmış \uXXXX dizileri çözülür.
 *
 * 17 Eylül 2026'da blog listesinde başlık "Kıbrıs Mutfağı"
 * diye görünüyordu; içerik iki kez JSON'a kodlanmış, çözülmeden kaydedilmişti.
 */
final class ContentUnicodeEscapeRepairTest extends TestCase
{
    use RefreshDatabase;

    private function yazi(string $baslik, string $govde): BlogPost
    {
        return BlogPost::query()->create([
            'title' => $baslik,
            'slug' => 'kacis-'.uniqid(),
            'excerpt' => 'Kısa özet.',
            'body' => $govde,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_kacis_dizileri_cozulur(): void
    {
        $yazi = $this->yazi('Kıbrıs Mutfağı', 'Hellim üretimi şu şekildedir.');

        $this->artisan('content:fix-unicode-escapes --apply')->assertSuccessful();

        $yazi->refresh();
        $this->assertSame('Kıbrıs Mutfağı', $yazi->title);
        $this->assertSame('Kısa özet.', $yazi->excerpt);
        $this->assertSame('Hellim üretimi şu şekildedir.', $yazi->body);
    }

    public function test_rapor_modu_hicbir_seyi_yazmaz(): void
    {
        $yazi = $this->yazi('Kıbrıs', 'Metin.');

        $this->artisan('content:fix-unicode-escapes')->assertSuccessful();

        $this->assertSame('Kıbrıs', $yazi->fresh()->title);
    }

    public function test_temiz_metne_dokunulmaz(): void
    {
        $yazi = BlogPost::query()->create([
            'title' => 'Kıbrıs Mutfağı', 'slug' => 'temiz-'.uniqid(), 'excerpt' => 'Özet.',
            'body' => 'Bu metin temiz ve \\ ters bölü ile u harfi yan yana bile olsa bozulmamalı.',
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);

        $this->artisan('content:fix-unicode-escapes --apply')->assertSuccessful();

        $this->assertSame('Kıbrıs Mutfağı', $yazi->fresh()->title);
        $this->assertStringContainsString('ters bölü', (string) $yazi->fresh()->body);
    }

    public function test_vekil_cifti_tek_karaktere_doner(): void
    {
        // Emoji iki yarım kodla yazılır; ayrı ayrı çözülürse bozuk çıkar.
        $this->assertSame('🍋', app(ContentFixUnicodeEscapes::class)->coz('🍋'));
    }
}
