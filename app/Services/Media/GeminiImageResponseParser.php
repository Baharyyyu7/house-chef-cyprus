<?php

declare(strict_types=1);

namespace App\Services\Media;

use Illuminate\Http\Client\Response;

/** Converts provider responses into safe diagnostics without retaining raw payloads. */
final class GeminiImageResponseParser
{
    /** @return array{images:list<array{bytes:string,mime:string}>,diagnostic:array<string,mixed>,status:string,error_code:?string,error_message:?string} */
    public function parse(Response $response, int $requestBytes, int $latencyMs): array
    {
        $body = $response->body();
        $json = $this->json($body);
        $predictions = is_array($json['predictions'] ?? null) ? $json['predictions'] : [];
        $candidates = is_array($json['candidates'] ?? null) ? $json['candidates'] : [];
        $parts = $this->parts($candidates);
        $rawCandidates = array_merge($predictions, $candidates);
        $moderation = $this->moderation($rawCandidates);
        $finishReason = $this->finishReason($rawCandidates);
        $diagnostic = [
            'response_status' => $response->status(),
            'provider_request_id' => $this->requestId($response),
            'response_content_type' => $response->header('Content-Type'),
            'response_body_present' => $body !== '',
            'candidate_count' => count($rawCandidates),
            'part_count' => count($parts),
            'image_part_count' => 0,
            'text_part_count' => count(array_filter($parts, fn (array $part): bool => filled($part['text'] ?? null))),
            'finish_reason' => $finishReason,
            'moderation_status' => $moderation,
            'request_bytes' => $requestBytes,
            'response_bytes' => strlen($body),
            'provider_latency_ms' => $latencyMs,
            'billing_status' => 'unknown',
            'parser_result' => null,
        ];

        if (! $response->successful()) {
            [$status, $code, $message] = $this->httpFailure($response->status());
            $diagnostic['parser_result'] = 'http_failure';

            return ['images' => [], 'diagnostic' => $diagnostic, 'status' => $status, 'error_code' => $code, 'error_message' => $message];
        }
        if ($json === null) {
            $diagnostic['parser_result'] = 'malformed_response';

            return ['images' => [], 'diagnostic' => $diagnostic, 'status' => 'parser_failed', 'error_code' => 'IMAGE_PROVIDER_MALFORMED_RESPONSE', 'error_message' => 'Görsel sağlayıcısı beklenen yanıt biçimini döndürmedi.'];
        }

        $images = [];
        $invalidImage = false;
        foreach (array_merge($predictions, $parts) as $candidate) {
            $image = $this->image($candidate);
            if ($image === 'invalid') {
                $invalidImage = true;
            } elseif (is_array($image)) {
                $images[] = $image;
            }
        }
        $diagnostic['image_part_count'] = count($images);
        if ($images !== []) {
            $diagnostic['parser_result'] = 'image_received';

            return ['images' => $images, 'diagnostic' => $diagnostic, 'status' => 'image_received', 'error_code' => null, 'error_message' => null];
        }
        if ($moderation !== null) {
            $diagnostic['parser_result'] = 'provider_blocked';

            return ['images' => [], 'diagnostic' => $diagnostic, 'status' => 'provider_blocked', 'error_code' => 'IMAGE_PROVIDER_MODERATION_BLOCKED', 'error_message' => 'Görsel sağlayıcısı güvenlik politikası nedeniyle görsel döndürmedi.'];
        }
        if ($invalidImage) {
            $diagnostic['parser_result'] = 'invalid_image_payload';

            return ['images' => [], 'diagnostic' => $diagnostic, 'status' => 'parser_failed', 'error_code' => 'IMAGE_PROVIDER_INVALID_IMAGE_PAYLOAD', 'error_message' => 'Görsel sağlayıcısının döndürdüğü görsel doğrulanamadı.'];
        }

        $diagnostic['parser_result'] = 'completed_without_image';

        return ['images' => [], 'diagnostic' => $diagnostic, 'status' => 'provider_completed_without_image', 'error_code' => 'IMAGE_PROVIDER_COMPLETED_WITHOUT_IMAGE', 'error_message' => 'Sağlayıcı isteği tamamladı ancak kullanılabilir görsel döndürmedi.'];
    }

    /** @return array<string,mixed>|null */
    private function json(string $body): ?array
    {
        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : null;
        } catch (\JsonException) {
            return null;
        }
    }

    /** @return list<array<string,mixed>> */
    private function parts(array $candidates): array
    {
        return collect($candidates)->flatMap(function (mixed $candidate): array {
            if (! is_array($candidate)) {
                return [];
            }

            return is_array(data_get($candidate, 'content.parts')) ? data_get($candidate, 'content.parts') : [];
        })->filter(fn (mixed $part): bool => is_array($part))->values()->all();
    }

    /** @return array{bytes:string,mime:string}|'invalid'|null */
    private function image(array $candidate): array|string|null
    {
        $encoded = $candidate['bytesBase64Encoded'] ?? data_get($candidate, 'inlineData.data') ?? data_get($candidate, 'inline_data.data') ?? null;
        if (! is_string($encoded) || $encoded === '') {
            return null;
        }
        $bytes = base64_decode($encoded, true);
        $mime = (string) ($candidate['mimeType'] ?? data_get($candidate, 'inlineData.mimeType') ?? data_get($candidate, 'inline_data.mime_type') ?? '');
        if ($bytes === false || strlen($bytes) < 64 || strlen($bytes) > (int) config('ai_images.max_response_payload_bytes') || ! in_array($mime, ['image/png', 'image/jpeg', 'image/webp'], true)) {
            return 'invalid';
        }

        return ['bytes' => $bytes, 'mime' => $mime];
    }

    private function moderation(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (is_array($candidate) && filled($candidate['raiFilteredReason'] ?? $candidate['safetyRating'] ?? null)) {
                return 'blocked';
            }
        }

        return null;
    }

    private function finishReason(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (is_array($candidate) && is_string($candidate['finishReason'] ?? null)) {
                return $candidate['finishReason'];
            }
        }

        return null;
    }

    private function requestId(Response $response): ?string
    {
        foreach (['x-request-id', 'x-goog-request-id', 'x-cloud-trace-context'] as $header) {
            $value = $response->header($header);
            if ($value !== '') {
                return substr($value, 0, 128);
            }
        }

        return null;
    }

    /** @return array{string,string,string} */
    private function httpFailure(int $status): array
    {
        return match ($status) {
            401, 403 => ['provider_blocked', 'IMAGE_PROVIDER_PERMISSION_DENIED', 'Görsel sağlayıcısı bu model için erişim izni vermedi.'],
            404 => ['provider_failed', 'IMAGE_PROVIDER_MODEL_UNAVAILABLE', 'Görsel sağlayıcı modeli veya endpointi kullanılamıyor.'],
            429 => ['provider_blocked', 'IMAGE_PROVIDER_QUOTA_EXCEEDED', 'Görsel sağlayıcısı kota veya hız limiti uyguladı.'],
            400, 413, 415, 422 => ['provider_failed', 'IMAGE_PROVIDER_REQUEST_REJECTED', 'Görsel sağlayıcısı isteği kabul etmedi.'],
            default => ['provider_failed', 'IMAGE_PROVIDER_UNAVAILABLE', 'Görsel sağlayıcısı isteği güvenli biçimde tamamlanamadı.'],
        };
    }
}
