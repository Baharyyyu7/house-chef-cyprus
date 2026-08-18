<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AudienceCountryDay;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Ziyaretçinin ülkesini günlük toplama yazar.
 *
 * Ülke bilgisi yalnız önümüzdeki CDN/sunucu katmanının verdiği başlıktan
 * okunur; IP adresine bakılmaz ve hiçbir yere yazılmaz. Başlık yoksa hiçbir
 * şey kaydedilmez - uydurma veri üretmektense harita boş kalır.
 */
final class RecordAudienceCountry
{
    /**
     * Farklı barındırıcılar aynı bilgiyi farklı adla gönderiyor.
     */
    private const HEADERS = [
        'CF-IPCountry',
        'X-Geo-Country',
        'X-Country-Code',
        'X-Hcdn-Country',
        'GeoIP-Country-Code',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && ! $request->ajax()) {
            $this->kaydet($request);
        }

        return $response;
    }

    private function kaydet(Request $request): void
    {
        $code = null;

        foreach (self::HEADERS as $header) {
            $value = strtoupper(trim((string) $request->header($header)));

            if (preg_match('/^[A-Z]{2}$/', $value) === 1 && $value !== 'XX') {
                $code = $value;
                break;
            }
        }

        if ($code === null) {
            return;
        }

        try {
            // Önce satırın var olduğundan emin ol (varsa dokunma), sonra artır:
            // iki adım da tek isabette bir artışa karşılık gelir.
            AudienceCountryDay::query()->upsert(
                [['day' => now()->toDateString(), 'country_code' => $code, 'hits' => 0, 'created_at' => now(), 'updated_at' => now()]],
                ['day', 'country_code'],
                []
            );

            AudienceCountryDay::query()
                ->where('day', now()->toDateString())
                ->where('country_code', $code)
                ->increment('hits');
        } catch (Throwable) {
            // Sayaç sayfayı düşürmez.
        }
    }
}
