<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Ana sayfa hero'su video taşımaz.
 *
 * Video 2026-09-25'te kullanıcının kararıyla kaldırıldı: 6 MB'lık bir mp4
 * ilk boyayı bekletiyordu ve %42 opaklıkta basıldığı için altındaki yemek
 * zaten seçilmiyordu. Yerine gerçek veriyle oynayan eşleştirme vitrini geldi.
 *
 * Bu dosya eskiden videonun varlığını doğruluyordu. Bir sonraki gün başka
 * bir oturum bu testleri geçirmek için videoyu yeni hero'nun içine geri
 * koydu; ürün, eskimiş testlere uysun diye değiştirilmiş oldu. Testler
 * artık kararın kendisini koruyor: dosya diskte dursa bile ana sayfa onu
 * yüklemez.
 */
final class HeroVideoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_the_home_page_never_loads_the_hero_video_even_when_the_file_exists(): void
    {
        Storage::disk('public')->put('videos/hero-720p-20260916.mp4', 'video');
        Storage::disk('public')->put('videos/hero-quality-20260908.mp4', 'video');
        Storage::disk('public')->put('videos/hero-loop.mp4', 'video');
        Storage::disk('public')->put('videos/hero-poster.webp', 'afis');

        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('<video', $icerik, 'Ana sayfa yeniden bir video basıyor.');
        $this->assertStringNotContainsString('data-hero-video', $icerik);
        $this->assertStringNotContainsString('.mp4', $icerik, 'Ana sayfa bir mp4 dosyasına başvuruyor.');
    }

    public function test_the_web_root_serves_hero_video_with_video_mime_and_cache_rules(): void
    {
        // Sunucu yapılandırması başka sayfalardaki videolar için hâlâ geçerli.
        $htaccess = (string) file_get_contents(public_path('.htaccess'));

        $this->assertStringContainsString('AddType video/webm .webm', $htaccess);
        $this->assertStringContainsString('ExpiresByType video/mp4 "access plus 30 days"', $htaccess);
        $this->assertStringContainsString('ExpiresByType video/webm "access plus 30 days"', $htaccess);
        $this->assertMatchesRegularExpression('/FilesMatch "[^\"]*mp4\|webm[^\"]*"/', $htaccess);
    }
}
