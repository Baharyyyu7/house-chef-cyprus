<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Yasal merkez ve politika sayfalarının İngilizce metni.
 *
 * Kaynak Türkçe metin config/legal-center.php'de. İngilizce karşılığı
 * lang/en/legal_center.php'de, bölüm kimliğiyle eşleşir ve paragraf sayısı
 * Türkçe kaynakla aynı olmak zorunda (tests/Feature/EnglishLegalPagesTest).
 * Çeviri yalnız İngilizce sayfada okunur; diğer dillerde null döner.
 */
final class LegalCenterCopy
{
    /** @return array<string, mixed>|null */
    public static function ceviri(string $anahtar): ?array
    {
        if (app()->getLocale() !== 'en') {
            return null;
        }

        $deger = trans('legal_center.'.$anahtar);

        return is_array($deger) ? $deger : null;
    }

    /**
     * @param  array{id: string, title: string, paragraphs: array<int, string>}  $bolum
     * @return array{id: string, title: string, paragraphs: array<int, string>}
     */
    public static function bolum(array $bolum): array
    {
        $ceviri = self::ceviri('sections.'.$bolum['id']);

        return $ceviri === null
            ? $bolum
            : ['id' => $bolum['id'], 'title' => $ceviri['title'], 'paragraphs' => $ceviri['paragraphs']];
    }

    /**
     * @param  array{baslik: string, ozet: string, bolumler: list<string>}  $tanim
     * @return array{baslik: string, ozet: string, bolumler: list<string>}
     */
    public static function sayfa(string $anahtar, array $tanim): array
    {
        $ceviri = self::ceviri('pages.'.$anahtar);

        return $ceviri === null
            ? $tanim
            : array_merge($tanim, ['baslik' => $ceviri['baslik'], 'ozet' => $ceviri['ozet']]);
    }
}
