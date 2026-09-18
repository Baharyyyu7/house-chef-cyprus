<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Seo\SeoMetadataService;
use Tests\TestCase;

/**
 * Başlık, marka eki uğruna ortasından kesilmez.
 *
 * 18 Eylül 2026'da arama sonucundaki başlık "Fırın Kaç Dakikada Isınır? Ön
 * Isıtma Süreleri… · House Chef Cyprus" diye görünüyordu: sorguyu karşılayan
 * kelimeler silinip yerine marka konmuştu.
 */
final class TitleIsNotCutMidPhraseTest extends TestCase
{
    public function test_marka_sigmazsa_ek_birakilir_baslik_kesilmez(): void
    {
        $baslik = 'Fırın Kaç Dakikada Isınır? Ön Isıtma Süreleri Tablosu';

        $sonuc = app(SeoMetadataService::class)->formatTitleWithBrand($baslik);

        $this->assertSame($baslik, $sonuc);
        $this->assertStringNotContainsString('…', $sonuc);
    }

    public function test_kisa_baslik_marka_ekini_alir(): void
    {
        $this->assertSame('Menemen · House Chef Cyprus', app(SeoMetadataService::class)->formatTitleWithBrand('Menemen'));
    }

    public function test_tek_basina_da_sigmayan_baslik_kelime_sinirinda_kisalir(): void
    {
        $uzun = str_repeat('Kıbrıs mutfağı rehberi ', 6);

        $sonuc = app(SeoMetadataService::class)->formatTitleWithBrand($uzun);

        $this->assertLessThanOrEqual(68, mb_strlen($sonuc));
        $this->assertStringNotContainsString('House Chef Cyprus', $sonuc);
    }
}
