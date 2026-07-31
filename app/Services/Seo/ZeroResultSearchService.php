<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\ZeroResultSearch;
use Illuminate\Support\Str;

final class ZeroResultSearchService
{
    public function record(string $query, int $resultCount): void
    {
        $normalized = trim((string) Str::of($query)->lower()->replaceMatches('/[^\pL\pN]+/u', ' ')->squish());
        if ($normalized === '' || $resultCount > 0 || mb_strlen($normalized) > 180) {
            return;
        }
        $record = ZeroResultSearch::query()->firstOrNew(['normalized_query' => $normalized]);
        $record->locale = (string) config('seo.locale', 'tr');
        $record->search_count = ((int) $record->search_count) + 1;
        $record->last_searched_at = now()->toDateTimeString();
        $record->save();
    }
}
