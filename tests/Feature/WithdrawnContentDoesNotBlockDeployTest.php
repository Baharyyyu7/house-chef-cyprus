<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WithdrawnContentDoesNotBlockDeployTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_for_a_withdrawn_recipe_is_skipped_not_failed(): void
    {
        // Manifest sabit, yayın durumu değişken. Yayından çekilmiş bir tarifin
        // manifestte duran görseli komutu hataya düşürüyordu ve deploy
        // scriptinde bu adım deploy'u durduruyordu. Sonuç: onaylı görseli olan
        // hiçbir tarif yayından alınamıyordu.
        $tarif = $this->tarif('draft');

        $sonuc = $this->calistir($tarif);

        $this->assertSame(0, $sonuc['failed'], 'Yayından çekilmiş içerik deploy\'u durdurmamalı.');
        $this->assertSame(1, $sonuc['withdrawn']);
        $this->assertSame('SKIPPED', $sonuc['results'][0]['status']);
        $this->assertSame('MEDIA_CONTENT_WITHDRAWN', $sonuc['results'][0]['reason']['code']);
    }

    public function test_media_for_content_that_no_longer_exists_is_skipped(): void
    {
        // İçerik tamamen silinmişse içe aktarılacak bir şey yok - bu hata
        // değil, boş işlem. Kapı bunu hata sayınca üretimde deploy kalıcı
        // olarak bloke oldu: manifest sabit, katalog değişken.
        // Ayrı kod veriliyor, böylece bayat manifest satırı görünür kalıyor.
        $sonuc = $this->calistir(null);

        $this->assertSame(0, $sonuc['failed']);
        $this->assertSame('SKIPPED', $sonuc['results'][0]['status']);
        $this->assertSame('MEDIA_CONTENT_MISSING', $sonuc['results'][0]['reason']['code']);
    }

    public function test_a_soft_deleted_recipe_counts_as_withdrawn(): void
    {
        // Soft-delete edilmiş kayıt hâlâ vardır, yalnız yayından çekilmiştir.
        // Sorgu silinmişleri dışladığı için bunlar "hiç yok" sayılıp
        // deploy'u durduruyordu.
        $tarif = $this->tarif('published');
        $tarif->delete();

        $sonuc = $this->calistir($tarif);

        $this->assertSame(0, $sonuc['failed']);
        $this->assertSame('MEDIA_CONTENT_WITHDRAWN', $sonuc['results'][0]['reason']['code']);
    }

    public function test_a_real_defect_still_fails_the_deploy(): void
    {
        // Daralma gerçek kapıyı zayıflatmamalı: kaydı olan ama kaynak dosyası
        // okunamayan satır hâlâ hatadır.
        $tarif = $this->tarif('published');

        $sonuc = $this->calistir($tarif);

        $this->assertSame(1, $sonuc['failed']);
        $this->assertSame('FAILED', $sonuc['results'][0]['status']);
    }

    public function test_an_unverified_recipe_is_skipped_rather_than_imported(): void
    {
        // Kayıt var ama doğrulanmamış: içe aktarılmıyor, ama deploy'u da
        // durdurmuyor. Kapının koruduğu şey "doğrulanmamış içeriğe medya
        // basılmaması"; bunu atlamak da sağlıyor.
        $tarif = $this->tarif('published', 'pending');

        $sonuc = $this->calistir($tarif);

        $this->assertSame(0, $sonuc['failed']);
        $this->assertSame(1, $sonuc['withdrawn']);
        // İçe aktarma yine yapılmadı; değişen tek şey deploy'u durdurup
        // durdurmadığı. Güvenlik özelliği korunuyor.
        $this->assertNotSame('IMPORTED', $sonuc['results'][0]['status']);
    }

    public function test_withdrawn_content_never_gets_media_imported(): void
    {
        // Asıl güvenlik özelliği: atlanan kayıt için hiçbir medya yazılmamalı.
        $tarif = $this->tarif('draft');

        $this->calistir($tarif);

        $this->assertSame(0, $tarif->media()->count());
    }

    /** @return array<string, mixed> */
    private function calistir(?Recipe $tarif): array
    {
        $manifest = [[
            'content_type' => 'recipe',
            'public_id' => $tarif?->public_id ?? '01OLMAYANKAYIT0000000000',
            'source_filename' => 'recipe--'.($tarif?->slug ?? 'olmayan-tarif').'.jpg',
        ]];

        $yol = storage_path('app/test-manifest-'.uniqid().'.json');
        file_put_contents($yol, json_encode($manifest));

        \Illuminate\Support\Facades\Artisan::call('media:import-approved', [
            '--dry-run' => true, '--manifest' => $yol, '--json' => true,
        ]);

        $cikti = json_decode(trim(\Illuminate\Support\Facades\Artisan::output()), true);
        @unlink($yol);

        return is_array($cikti) ? $cikti : [];
    }

    private function tarif(string $durum, string $dogrulama = 'verified'): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Çekilmiş Tarif',
            'slug' => 'cekilmis-tarif-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => $durum,
            'verification_status' => $dogrulama,
            'published_at' => now(),
        ]);
    }
}
