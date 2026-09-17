<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class AffiliateRedirectController extends Controller
{
    public function redirect(Request $request, string $item): RedirectResponse
    {
        $catalog = config('affiliate.items', []);

        if (! isset($catalog[$item])) {
            return redirect()->route('recipes.index');
        }

        $product = $catalog[$item];
        $partnerName = (string) config('affiliate.default_partner', 'amazon');
        $partner = config("affiliate.partners.{$partnerName}", []);

        $query = urlencode((string) ($product['search_query'] ?? $product['name']));
        $tag = (string) ($partner['tag'] ?? '');
        $baseUrl = (string) ($partner['base_search_url'] ?? 'https://www.amazon.com/s?k=');

        $destination = $baseUrl . $query;
        if ($tag !== '') {
            $destination .= '&tag=' . urlencode($tag);
        }

        // UTM Etiketleri
        $utm = (array) config('affiliate.utm', []);
        if (! empty($utm['source'])) {
            $destination .= '&utm_source=' . urlencode((string) $utm['source']);
            $destination .= '&utm_medium=' . urlencode((string) ($utm['medium'] ?? 'affiliate'));
            $destination .= '&utm_campaign=' . urlencode((string) ($utm['campaign'] ?? 'culinary_gear'));
            $destination .= '&utm_content=' . urlencode($item);
        }

        // Log click metric (anonymized)
        Log::info('affiliate_outbound_click', [
            'item' => $item,
            'partner' => $partnerName,
            'referer' => $request->header('referer'),
            'ip_country' => $request->header('cf-ipcountry') ?? 'unknown',
            'time' => now()->toIso8601String(),
        ]);

        return redirect()->away($destination, 302, [
            'X-Robots-Tag' => 'noindex, nofollow',
            'Referrer-Policy' => 'no-referrer-when-downgrade',
        ]);
    }
}
