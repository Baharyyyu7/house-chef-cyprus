<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\ZeroResultSearch;
use Illuminate\Support\Str;

final class ZeroResultSearchService
{
    public function record(string $query, int $resultCount): void
    {
        $normalized = $this->normalize($query);
        if ($normalized === '' || mb_strlen($normalized) > 180) {
            return;
        }

        // Terim artık sonuç dönüyorsa kayıt kapanmalı. Eskiden yalnız sıfır
        // sonuçta yazılıyordu ve satır olduğu yerde kalıyordu: katalog
        // büyüdükten sonra bile terim "karşılanmamış talep" görünüyor,
        // içerik görevi üretmeye devam ediyordu.
        if ($resultCount > 0) {
            $this->resolve($normalized, $resultCount);

            return;
        }

        $record = ZeroResultSearch::query()->firstOrNew(['normalized_query' => $normalized]);
        // Yeniden sıfır sonuç dönüyorsa talep gerçekten karşılanmamış:
        // önceki kapanış geçersiz.
        $record->resolved_at = null;
        $record->resolved_result_count = null;
        $record->locale = (string) config('seo.locale', 'tr');
        $record->search_count = ((int) $record->search_count) + 1;
        $record->last_searched_at = now()->toDateTimeString();
        $record->save();
    }

    /** Kayıtlı bir terim artık sonuç dönüyorsa kapatılır; satır silinmiyor. */
    public function resolve(string $normalizedQuery, int $resultCount): void
    {
        // Talep sinyali silinmiyor: "bu terim arandı" bilgisi, karşılanmış
        // olsa da içerik kararları için değerli.
        ZeroResultSearch::query()
            ->where('normalized_query', $normalizedQuery)
            ->whereNull('resolved_at')
            ->update(['resolved_at' => now(), 'resolved_result_count' => $resultCount]);
    }

    public function normalize(string $query): string
    {
        return trim((string) Str::of($query)->lower()->replaceMatches('/[^\pL\pN]+/u', ' ')->squish());
    }
}
