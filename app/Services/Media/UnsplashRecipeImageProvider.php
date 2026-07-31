<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Recipe;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Official-API-only Unsplash candidate adapter. It stores no raw payload and
 * never downloads, approves, or publishes an image during search.
 */
final class UnsplashRecipeImageProvider
{
    /** @return array<string, mixed> */
    public function health(): array
    {
        $settings = $this->settings();
        $configured = filled($settings['access_key'] ?? null);

        return [
            'name' => 'unsplash',
            'configured' => $configured,
            'enabled' => (bool) ($settings['enabled'] ?? false),
            'image_capability' => ! $configured ? 'not_configured' : (! $settings['enabled'] ? 'disabled' : 'configured'),
            'model' => null,
            'base_url' => (string) $settings['endpoint'],
            'search_adapter' => 'laravel_http_official_rest',
            'rate_limit_parser' => true,
            'attribution_builder' => true,
            'download_tracking_builder' => true,
            'utm_builder' => true,
            'human_review_guard' => true,
            'safe_message' => ! $configured ? 'Unsplash access key yapılandırılmadı.' : (! $settings['enabled'] ? 'Unsplash editoryal aday araması kapalı.' : 'Unsplash yalnız insan incelemeli hotlink adayları için hazır.'),
        ];
    }

    /** @return array{status:string,error_code:?string,safe_message:string,candidates:list<array<string,mixed>>,rate_limit:array<string,int|null>} */
    public function search(Recipe $recipe): array
    {
        $health = $this->health();
        if (! $health['configured'] || ! $health['enabled']) {
            return $this->result('disabled', 'UNSPLASH_PROVIDER_DISABLED', 'Unsplash aday araması etkin değil.');
        }

        $settings = $this->settings();
        try {
            $response = Http::withHeaders(['Authorization' => 'Client-ID '.$settings['access_key']])
                ->acceptJson()->timeout(12)
                ->get(rtrim((string) $settings['endpoint'], '/').'/search/photos', [
                    'query' => $this->queryFor($recipe),
                    'orientation' => 'landscape',
                    'per_page' => (int) $settings['max_results'],
                ]);
        } catch (ConnectionException) {
            return $this->result('provider_unavailable', 'UNSPLASH_PROVIDER_UNAVAILABLE', 'Unsplash aday araması güvenli zaman aşımı sınırını geçti.');
        }

        $rateLimit = $this->rateLimit($response->headers());
        if (! $response->successful()) {
            $code = match ($response->status()) {
                401 => 'UNSPLASH_INVALID_KEY', 403 => 'UNSPLASH_PERMISSION_DENIED', 404 => 'UNSPLASH_NOT_FOUND',
                429 => 'UNSPLASH_RATE_LIMITED', default => 'UNSPLASH_PROVIDER_UNAVAILABLE',
            };
            $message = match ($response->status()) {
                401 => 'Unsplash access key doğrulanamadı.', 403 => 'Unsplash erişim izni bu istek için yeterli değil.',
                429 => 'Unsplash hız sınırı uyguladı; daha sonra yeniden deneyin.', default => 'Unsplash aday araması şu anda kullanılamıyor.',
            };

            return $this->result('provider_error', $code, $message, [], $rateLimit);
        }
        $photos = $response->json('results');
        if (! is_array($photos)) {
            return $this->result('provider_error', 'UNSPLASH_INVALID_RESPONSE', 'Unsplash aday yanıtı güvenli biçimde işlenemedi.', [], $rateLimit);
        }

        $candidates = collect($photos)->filter(static fn (mixed $photo): bool => is_array($photo))->map(fn (array $photo): ?array => $this->map($photo, $recipe))
            ->filter()->take((int) $settings['max_results'])->values()->all();

        return $this->result('ok', null, $candidates === [] ? 'Unsplash bu tarif için güvenli aday döndürmedi.' : 'Unsplash adayları yalnız insan incelemesi için hazır.', $candidates, $rateLimit);
    }

    /** @return array{status:string,error_code:?string,safe_message:string,rate_limit:array<string,int|null>} */
    public function trackDownload(string $downloadLocation): array
    {
        if (! $this->isSafeApiUrl($downloadLocation) || ! $this->health()['configured']) {
            return ['status' => 'failed', 'error_code' => 'UNSPLASH_TRACKING_UNAVAILABLE', 'safe_message' => 'Unsplash download tracking güvenli biçimde başlatılamadı.', 'rate_limit' => ['limit' => null, 'remaining' => null, 'retry_after' => null]];
        }
        try {
            $response = Http::withHeaders(['Authorization' => 'Client-ID '.$this->settings()['access_key']])->acceptJson()->timeout(10)->get($downloadLocation);
        } catch (ConnectionException) {
            return ['status' => 'failed', 'error_code' => 'UNSPLASH_TRACKING_UNAVAILABLE', 'safe_message' => 'Unsplash download tracking zaman aşımına uğradı.', 'rate_limit' => ['limit' => null, 'remaining' => null, 'retry_after' => null]];
        }
        $rateLimit = $this->rateLimit($response->headers());

        return $response->successful()
            ? ['status' => 'tracked', 'error_code' => null, 'safe_message' => 'Download tracking kaydedildi.', 'rate_limit' => $rateLimit]
            : ['status' => 'failed', 'error_code' => $response->status() === 429 ? 'UNSPLASH_RATE_LIMITED' : 'UNSPLASH_TRACKING_FAILED', 'safe_message' => 'Download tracking güvenli biçimde tamamlanamadı.', 'rate_limit' => $rateLimit];
    }

    public function queryFor(Recipe $recipe): string
    {
        $ingredients = $recipe->ingredients()->orderBy('recipe_ingredients.sort_order')->limit(3)->pluck('ingredients.name')->all();
        $terms = array_filter([Str::ascii($recipe->title), ...$ingredients, $recipe->category?->name, 'food']);

        return trim(implode(' ', array_unique(array_map(static fn (mixed $value): string => Str::ascii((string) $value), $terms))));
    }

    /** @param array<string,mixed> $photo @return array<string,mixed>|null */
    private function map(array $photo, Recipe $recipe): ?array
    {
        $id = $photo['id'] ?? null;
        $regular = data_get($photo, 'urls.regular');
        $small = data_get($photo, 'urls.small');
        $thumb = data_get($photo, 'urls.thumb');
        $page = data_get($photo, 'links.html');
        $download = data_get($photo, 'links.download_location');
        $profile = data_get($photo, 'user.links.html');
        $name = trim((string) data_get($photo, 'user.name', 'Unsplash'));
        $width = (int) ($photo['width'] ?? 0);
        $height = (int) ($photo['height'] ?? 0);
        if (! is_scalar($id) || ! $this->isSafeImageUrl($regular) || ! $this->isSafeImageUrl($small) || ! $this->isSafeImageUrl($thumb) || ! $this->isSafeWebUrl($page) || ! $this->isSafeApiUrl($download) || ! $this->isSafeWebUrl($profile) || $width < 1000 || $height < 600) {
            return null;
        }
        $utm = ['utm_source' => (string) $this->settings()['app_name'], 'utm_medium' => 'referral'];
        $profileUrl = $this->withUtm($profile, $utm);
        $pageUrl = $this->withUtm($page, $utm);
        $alt = trim((string) ($photo['alt_description'] ?? $photo['description'] ?? ''));

        return [
            'external_photo_id' => (string) $id, 'description' => $this->nullableText($photo['description'] ?? null), 'alt_description' => $this->nullableText($photo['alt_description'] ?? null),
            'photographer' => $name, 'photographer_url' => $profileUrl, 'external_page_url' => $pageUrl, 'source_url' => $regular, 'small_url' => $small, 'thumb_url' => $thumb,
            'download_location' => $download, 'width' => $width, 'height' => $height, 'orientation' => (string) ($photo['orientation'] ?? ''), 'color' => $this->nullableText($photo['color'] ?? null),
            'blur_hash' => $this->nullableText($photo['blur_hash'] ?? null), 'alt_text' => $alt !== '' ? $alt : $recipe->title.' için Unsplash aday görseli',
            'attribution_text' => 'Fotoğraf: '.$name.' / Unsplash', 'utm_source' => $utm['utm_source'], 'utm_medium' => $utm['utm_medium'],
        ];
    }

    /** @param array<string,list<string>> $headers @return array{limit:int|null,remaining:int|null,retry_after:int|null} */
    public function rateLimit(array $headers): array
    {
        $first = static fn (string $key): ?string => $headers[$key][0] ?? $headers[strtolower($key)][0] ?? null;

        return ['limit' => ($value = $first('X-Ratelimit-Limit')) !== null && ctype_digit($value) ? (int) $value : null, 'remaining' => ($value = $first('X-Ratelimit-Remaining')) !== null && ctype_digit($value) ? (int) $value : null, 'retry_after' => ($value = $first('Retry-After')) !== null && ctype_digit($value) ? (int) $value : null];
    }

    private function isSafeImageUrl(mixed $url): bool
    {
        return $this->safeUrl($url, ['images.unsplash.com']);
    }

    private function isSafeApiUrl(mixed $url): bool
    {
        return $this->safeUrl($url, ['api.unsplash.com']);
    }

    private function isSafeWebUrl(mixed $url): bool
    {
        return $this->safeUrl($url, ['unsplash.com', 'images.unsplash.com']);
    }

    private function safeUrl(mixed $url, array $hosts): bool
    {
        $parts = is_string($url) ? parse_url($url) : false;

        return is_array($parts) && ($parts['scheme'] ?? null) === 'https' && in_array(Str::lower((string) ($parts['host'] ?? '')), $hosts, true) && ! isset($parts['user'], $parts['pass']) && (! isset($parts['port']) || (int) $parts['port'] === 443);
    }

    /** @param array<string,string> $utm */
    private function withUtm(string $url, array $utm): string
    {
        return $url.(str_contains($url, '?') ? '&' : '?').http_build_query($utm);
    }

    private function nullableText(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return $value === '' ? null : Str::limit($value, 1000, '');
    }

    /** @return array<string,mixed> */
    private function settings(): array
    {
        return (array) config('ai_images.providers.unsplash');
    }

    /** @param list<array<string,mixed>> $candidates @param array<string,int|null> $rateLimit @return array{status:string,error_code:?string,safe_message:string,candidates:list<array<string,mixed>>,rate_limit:array<string,int|null>} */
    private function result(string $status, ?string $errorCode, string $safeMessage, array $candidates = [], array $rateLimit = ['limit' => null, 'remaining' => null, 'retry_after' => null]): array
    {
        return ['status' => $status, 'error_code' => $errorCode, 'safe_message' => $safeMessage, 'candidates' => $candidates, 'rate_limit' => $rateLimit];
    }
}
