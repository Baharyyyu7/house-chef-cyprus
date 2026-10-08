<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Models\AudienceCountryDay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Ülke sayacının önbellekteki tamponu.
 *
 * Önceden her GET isteği, yanıt dönmeden önce audience_country_days'teki
 * aynı satıra upsert + increment yapıyordu: her ziyaret iki yazma ve aynı
 * günün aynı ülkesi için tek bir sıcak satır kilidi. Artık istek yalnız
 * önbellekteki sayacı artırıyor (yanıt gönderildikten sonra, terminate'te);
 * birikmiş sayılar en fazla dakikada bir, ülke başına tek yazmayla tabloya
 * aktarılıyor. Aktarım hem dakikalık zamanlanmış görevden hem de aralık
 * dolduğunda ilk istekten tetikleniyor; ikisi aynı anda koşsa da kilit
 * tek aktarıma izin veriyor.
 */
final class AudienceCountryBuffer
{
    private const PREFIX = 'audience:buffer:';

    public function record(string $code, ?Carbon $an = null): void
    {
        $gun = ($an ?? now())->toDateString();

        Cache::increment(self::key($gun, $code));

        $aralik = (int) config('production.audience_flush_seconds', 60);

        // Aralık 0 ise (testler) her kayıt hemen aktarılır.
        if ($aralik <= 0 || Cache::add(self::PREFIX.'flush-window', 1, $aralik)) {
            $this->flush();
        }
    }

    /** @return int Tabloya aktarılan toplam isabet */
    public function flush(): int
    {
        $kilit = Cache::lock(self::PREFIX.'flush-lock', 30);

        if (! $kilit->get()) {
            return 0;
        }

        try {
            $toplam = 0;
            // Gece yarısını geçen tampon dünün satırına yazılmalı.
            foreach ([now()->subDay()->toDateString(), now()->toDateString()] as $gun) {
                $toplam += $this->flushDay($gun);
            }

            return $toplam;
        } finally {
            $kilit->release();
        }
    }

    private function flushDay(string $gun): int
    {
        // Çözücü yalnız iki harfli kodları kabul ediyor; bütün olası
        // anahtarlar tek sorguda okunur (veritabanı önbelleğinde whereIn).
        $anahtarlar = [];
        foreach (range('A', 'Z') as $a) {
            foreach (range('A', 'Z') as $b) {
                $anahtarlar[self::key($gun, $a.$b)] = $a.$b;
            }
        }

        $toplam = 0;

        foreach (Cache::many(array_keys($anahtarlar)) as $anahtar => $sayi) {
            $sayi = (int) $sayi;

            if ($sayi <= 0) {
                continue;
            }

            try {
                $this->write($gun, $anahtarlar[$anahtar], $sayi);
            } catch (Throwable) {
                // Yazılamayan sayı tamponda kalır, bir sonraki aktarımda denenir.
                continue;
            }

            // Silmek yerine aktarılan kadar düşülür: arada gelen isabetler kaybolmaz.
            Cache::decrement($anahtar, $sayi);
            $toplam += $sayi;
        }

        return $toplam;
    }

    private function write(string $gun, string $code, int $sayi): void
    {
        AudienceCountryDay::query()->upsert(
            [['day' => $gun, 'country_code' => $code, 'hits' => 0, 'created_at' => now(), 'updated_at' => now()]],
            ['day', 'country_code'],
            []
        );

        AudienceCountryDay::query()
            ->where('day', $gun)
            ->where('country_code', $code)
            ->increment('hits', $sayi);
    }

    private static function key(string $gun, string $code): string
    {
        return self::PREFIX.$gun.':'.$code;
    }
}
