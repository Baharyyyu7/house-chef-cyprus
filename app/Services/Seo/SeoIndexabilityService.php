<?php

declare(strict_types=1);

namespace App\Services\Seo;

use Illuminate\Http\Request;

/**
 * Keeps query-driven discovery views from creating crawlable URL variants.
 *
 * The canonical URL is deliberately route-based (without query parameters).
 * Search, filters, pagination and serving-size controls remain usable for
 * visitors while being noindex,follow for crawlers.
 */
final class SeoIndexabilityService
{
    /** @return array{canonical:string,robots:string} */
    public function forRequest(Request $request, string $canonical): array
    {
        return [
            'canonical' => $canonical,
            'robots' => $request->query() === [] ? 'index,follow' : 'noindex,follow',
        ];
    }
}
