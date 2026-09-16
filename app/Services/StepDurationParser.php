<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Pişirme adımı metninden süre çıkarır ("15 dakika", "45 dk", "1 saat 20 dk").
 *
 * duration_seconds alanı boş olan adımlarda zamanlayıcı butonu göstermek için
 * kullanılır. Aralık ifadelerinde ("10-12 dakika") üst sınır alınır; 1 ile
 * 720 dakika arası dışındaki sonuçlar elenir.
 */
final class StepDurationParser
{
    public function minutes(?string $text): ?int
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $metin = mb_strtolower($text, 'UTF-8');
        $toplam = 0;
        $bulundu = false;

        if (preg_match_all('/(\d+)\s*(?:-\s*(\d+)\s*)?(saat|hour|hr)/iu', $metin, $eslesmeler, PREG_SET_ORDER) === false) {
            return null;
        }
        foreach ($eslesmeler as $eslesme) {
            $bulundu = true;
            $toplam += ((int) ($eslesme[2] !== '' ? $eslesme[2] : $eslesme[1])) * 60;
        }

        if (preg_match_all('/(\d+)\s*(?:-\s*(\d+)\s*)?(dakika|dak|dk|mins?|minutes?)/iu', $metin, $eslesmeler, PREG_SET_ORDER) === false) {
            return null;
        }
        foreach ($eslesmeler as $eslesme) {
            $bulundu = true;
            $toplam += (int) ($eslesme[2] !== '' ? $eslesme[2] : $eslesme[1]);
        }

        if (! $bulundu && preg_match_all('/(\d+)\s*(?:-\s*(\d+)\s*)?(saniye|sn|secs?|seconds?)/iu', $metin, $eslesmeler, PREG_SET_ORDER) !== false) {
            foreach ($eslesmeler as $eslesme) {
                $saniye = (int) ($eslesme[2] !== '' ? $eslesme[2] : $eslesme[1]);
                if ($saniye >= 60) {
                    $bulundu = true;
                    $toplam += (int) ceil($saniye / 60);
                }
            }
        }

        if (! $bulundu || $toplam < 1 || $toplam > 720) {
            return null;
        }

        return $toplam;
    }
}
