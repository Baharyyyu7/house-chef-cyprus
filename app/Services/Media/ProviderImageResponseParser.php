<?php

declare(strict_types=1);

namespace App\Services\Media;

use Illuminate\Http\Client\Response;

/** Parses direct binary or JSON/base64 image responses without retaining their bodies. */
final class ProviderImageResponseParser
{
    public function parse(Response $response, int $requestBytes, int $latencyMs, string $provider): ProviderImageResult
    {
        $body = $response->body();
        $contentType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
        $diagnostic = [
            'response_status' => $response->status(), 'content_type' => $contentType ?: null,
            'response_body_present' => $body !== '', 'payload_bytes' => strlen($body), 'request_bytes' => $requestBytes,
            'latency_ms' => $latencyMs, 'billing_status' => 'unknown', 'parser_result' => null,
        ];
        if (! $response->successful()) {
            [$status, $code, $message] = $this->failureFor($response->status(), $provider);
            $diagnostic['parser_result'] = 'http_failure';

            return new ProviderImageResult($status, $code, $message, diagnostic: $diagnostic);
        }
        if (in_array($contentType, ['image/png', 'image/jpeg', 'image/webp'], true)) {
            return $this->image($body, $contentType, $diagnostic);
        }
        $json = $response->json();
        $encoded = is_array($json) ? ($json['image'] ?? data_get($json, 'artifacts.0.base64') ?? data_get($json, 'data.0.b64_json')) : null;
        if (is_string($encoded) && $encoded !== '') {
            $bytes = base64_decode($encoded, true);
            $mime = (string) ($json['mime_type'] ?? data_get($json, 'artifacts.0.mime_type') ?? 'image/webp');

            return $bytes === false
                ? new ProviderImageResult('parser_failed', 'IMAGE_PROVIDER_INVALID_IMAGE_PAYLOAD', 'Görsel sağlayıcısının döndürdüğü görsel doğrulanamadı.', diagnostic: array_merge($diagnostic, ['parser_result' => 'invalid_base64']))
                : $this->image($bytes, $mime, $diagnostic);
        }

        return new ProviderImageResult('provider_completed_without_image', 'IMAGE_PROVIDER_COMPLETED_WITHOUT_IMAGE', 'Sağlayıcı isteği tamamladı ancak kullanılabilir görsel döndürmedi.', diagnostic: array_merge($diagnostic, ['parser_result' => 'completed_without_image']));
    }

    /** @param array<string,mixed> $diagnostic */
    private function image(string $bytes, string $mime, array $diagnostic): ProviderImageResult
    {
        if (strlen($bytes) < 64 || strlen($bytes) > (int) config('ai_images.max_response_payload_bytes') || ! in_array($mime, ['image/png', 'image/jpeg', 'image/webp'], true) || @getimagesizefromstring($bytes) === false) {
            return new ProviderImageResult('media_validation_failed', 'IMAGE_PROVIDER_INVALID_IMAGE_PAYLOAD', 'Görsel doğrulama kurallarını geçemedi.', diagnostic: array_merge($diagnostic, ['parser_result' => 'media_validation_failed']));
        }

        return new ProviderImageResult('image_received', null, null, $bytes, $mime, diagnostic: array_merge($diagnostic, ['parser_result' => 'image_received']));
    }

    /** @return array{string,string,string} */
    private function failureFor(int $status, string $provider): array
    {
        return match ($status) {
            401 => ['provider_blocked', 'IMAGE_PROVIDER_UNAUTHORIZED', 'Görsel sağlayıcısı kimlik doğrulamasını kabul etmedi.'],
            402 => ['provider_blocked', 'IMAGE_PROVIDER_INSUFFICIENT_CREDITS', 'Görsel sağlayıcısında yeterli kredi bulunmuyor.'],
            403 => ['provider_blocked', $provider === 'stability' ? 'IMAGE_PROVIDER_MODERATION_BLOCKED' : 'IMAGE_PROVIDER_PERMISSION_DENIED', 'Görsel sağlayıcısı isteği engelledi.'],
            413 => ['provider_failed', 'IMAGE_PROVIDER_PAYLOAD_TOO_LARGE', 'Görsel sağlayıcısı isteği veya yanıt boyutunu kabul etmedi.'],
            422 => ['provider_failed', 'IMAGE_PROVIDER_REQUEST_REJECTED', 'Görsel sağlayıcısı isteği kabul etmedi.'],
            429 => ['provider_blocked', 'IMAGE_PROVIDER_RATE_LIMITED', 'Görsel sağlayıcısı hız limiti uyguladı.'],
            default => ['provider_failed', 'IMAGE_PROVIDER_UNAVAILABLE', 'Görsel sağlayıcısı isteği güvenli biçimde tamamlanamadı.'],
        };
    }
}
