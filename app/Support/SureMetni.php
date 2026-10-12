<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Dakika cinsinden süre ekranda saatle okunur: "6 sa 31 dk", "2 sa".
 *
 * 12 Ekim 2026'da canlıda dondurma bekleyen semifreddo "⏱ 391 dk",
 * adım düğmesi "360 dk" basıyordu; okuyan saati kafadan hesaplıyordu.
 */
final class SureMetni
{
    public static function dakika(int $dakika, ?string $dil = null): string
    {
        $dk = __('recipe.min', [], $dil);

        if ($dakika < 60) {
            return $dakika.' '.$dk;
        }

        $saat = intdiv($dakika, 60).' '.__('recipe.hour', [], $dil);

        return $dakika % 60 === 0 ? $saat : $saat.' '.($dakika % 60).' '.$dk;
    }
}
