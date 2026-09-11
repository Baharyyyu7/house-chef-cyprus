<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\SeoBacklinkCandidate;
use App\Services\Integrity\IntegrityReportLog;
use DOMDocument;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

final class BacklinkVerificationService
{
    public const LABELS = ['unchecked' => 'Henüz kontrol edilmedi', 'present' => 'Bağlantı bulundu', 'not_found' => 'Bağlantı bulunamadı', 'redirect' => 'Yönlendirme incelenmeli', 'blocked' => 'Erişim engelli', 'error' => 'Kontrol tamamlanamadı', 'rate_limited' => 'Hız sınırı; ertelendi'];

    private const MAX_BYTES = 2097152;

    public function __construct(private readonly PublicSourceResolver $resolver, private readonly OrganicGrowthService $growth) {}

    public function run(int $limit = 3): array
    {
        $lock = Cache::lock('seo:backlinks:verify', 120);
        if (! $lock->get()) {
            return ['status' => 'busy', 'checked' => 0];
        }
        try {
            $result = ['status' => 'success', 'checked' => 0, 'present' => 0, 'deferred' => 0, 'outreach_sent' => 0];
            $started = microtime(true);
            $hosts = [];
            $candidates = SeoBacklinkCandidate::query()->where('status', '<>', 'rejected')
                ->where(fn ($query) => $query->whereNull('next_check_at')->orWhere('next_check_at', '<=', now()))
                ->orderBy('checked_at')->orderBy('id')->limit(100)->get();
            foreach ($candidates as $candidate) {
                if ($result['checked'] >= max(1, min(3, $limit))) {
                    break;
                }
                if (microtime(true) - $started > 20) {
                    $result['status'] = 'incomplete';
                    break;
                }
                $hostKey = 'seo:backlink:host:'.hash('sha256', $candidate->source_host);
                if (isset($hosts[$candidate->source_host]) || Cache::has($hostKey)) {
                    $candidate->update(['next_check_at' => now()->addDay()]);
                    $result['deferred']++;

                    continue;
                }
                $hosts[$candidate->source_host] = true;
                $check = $this->check($candidate);
                $delay = $check['retry_after_seconds'] ?? 604800;
                unset($check['retry_after_seconds']);
                $candidate->update([...$check, 'checked_at' => now(), 'next_check_at' => now()->addSeconds($delay)]);
                $result['checked']++;
                $result['present'] += (int) ($check['check_status'] === 'present');
                Cache::put($hostKey, true, now()->addSeconds(max(86400, $check['check_status'] === 'rate_limited' ? $delay : 86400)));
                if ($check['check_status'] === 'rate_limited') {
                    $result['status'] = 'incomplete';
                    break;
                }
            }
            app(IntegrityReportLog::class)->record('seo:backlinks-verify', $result['status'], $result['present'], $result);

            return $result;
        } finally {
            $lock->release();
        }
    }

    private function check(SeoBacklinkCandidate $candidate): array
    {
        $result = ['check_status' => 'blocked', 'last_http_status' => null, 'link_rel' => null];
        $pair = $this->growth->candidateUrls($candidate->source_url, $candidate->target_url);
        if ($pair === null) {
            return $result;
        }
        try {
            $addresses = $this->resolver->resolve($pair['source_host']);
            if ($addresses === []) {
                return $result;
            }
            $parts = parse_url($pair['source_url']);
            $port = $parts['port'] ?? ($parts['scheme'] === 'https' ? 443 : 80);
            $address = str_contains($addresses[0], ':') ? '['.$addresses[0].']' : $addresses[0];
            $response = Http::withUserAgent('HouseChefLinkCheck/1.0 (+'.config('app.url').')')->accept('text/html')->withHeaders(['Accept-Encoding' => 'identity'])
                ->connectTimeout(3)->timeout(6)->withoutRedirecting()->withOptions([
                    'proxy' => '', 'decode_content' => false, 'curl' => [CURLOPT_RESOLVE => [$pair['source_host'].':'.$port.':'.$address]],
                    'on_headers' => static function (ResponseInterface $response): void {
                        if ((int) $response->getHeaderLine('Content-Length') > self::MAX_BYTES) {
                            throw new RuntimeException('SOURCE_TOO_LARGE');
                        }
                    },
                    'progress' => static function ($total, $downloaded): void {
                        if ($downloaded > self::MAX_BYTES) {
                            throw new RuntimeException('SOURCE_TOO_LARGE');
                        }
                    },
                ])->get($pair['source_url']);
            $result['last_http_status'] = $response->status();
            if ($response->status() === 429 || $response->status() === 503) {
                $retry = $response->header('Retry-After');
                $seconds = is_numeric($retry) ? (int) $retry : (int) strtotime($retry) - time();

                return [...$result, 'check_status' => 'rate_limited', 'retry_after_seconds' => max(86400, $seconds)];
            }
            if ($response->redirect()) {
                return [...$result, 'check_status' => 'redirect'];
            }
            if (! $response->successful()) {
                return $result;
            }
            if (! in_array(strtolower($response->header('Content-Encoding')), ['', 'identity'], true) || ! str_contains(strtolower($response->header('Content-Type')), 'text/html') || strlen($response->body()) > self::MAX_BYTES) {
                return [...$result, 'check_status' => 'error'];
            }
            $dom = new DOMDocument;
            $previous = libxml_use_internal_errors(true);
            try {
                $dom->loadHTML($response->body(), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
            $base = new Uri($pair['source_url']);
            $baseElement = $dom->getElementsByTagName('base')->item(0);
            if ($baseElement !== null && $baseElement->hasAttribute('href')) {
                $base = UriResolver::resolve($base, new Uri($baseElement->getAttribute('href')));
            }
            foreach ($dom->getElementsByTagName('a') as $anchor) {
                $href = trim($anchor->getAttribute('href'));
                if ($href === '') {
                    continue;
                }
                try {
                    $target = (string) UriResolver::resolve($base, new Uri($href));
                    $match = $this->growth->candidateUrls($pair['source_url'], $target);
                } catch (Throwable) {
                    continue;
                }
                if ($match !== null && $match['target_url'] === $pair['target_url']) {
                    $rel = preg_split('/\s+/', strtolower(trim($anchor->getAttribute('rel'))));
                    $rel = array_values(array_intersect($rel, ['nofollow', 'sponsored', 'ugc', 'noopener', 'noreferrer', 'external']));

                    return [...$result, 'check_status' => 'present', 'link_rel' => implode(' ', array_unique($rel))];
                }
            }

            return [...$result, 'check_status' => 'not_found'];
        } catch (Throwable) {
            return [...$result, 'check_status' => 'error'];
        }
    }
}
