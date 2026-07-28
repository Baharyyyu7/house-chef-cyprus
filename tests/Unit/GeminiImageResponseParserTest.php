<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Media\GeminiImageResponseParser;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class GeminiImageResponseParserTest extends TestCase
{
    public function test_it_parses_an_inline_prediction_image(): void
    {
        $result = $this->parse(['predictions' => [$this->image()]]);

        $this->assertSame('image_received', $result['status']);
        $this->assertCount(1, $result['images']);
        $this->assertSame('image/png', $result['images'][0]['mime']);
    }

    public function test_it_parses_an_image_part_without_mistaking_text_for_an_image(): void
    {
        $result = $this->parse([
            'candidates' => [[
                'content' => ['parts' => [
                    ['text' => 'safe caption'],
                    ['inlineData' => ['data' => $this->image()['bytesBase64Encoded'], 'mimeType' => 'image/png']],
                ]],
            ]],
        ]);

        $this->assertSame('image_received', $result['status']);
        $this->assertSame(1, $result['diagnostic']['image_part_count']);
        $this->assertSame(1, $result['diagnostic']['text_part_count']);
    }

    public function test_text_only_empty_and_file_uri_responses_are_not_counted_as_images(): void
    {
        foreach ([['candidates' => [['content' => ['parts' => [['text' => 'only text']]]]]], ['predictions' => []], ['candidates' => [['content' => ['parts' => [['fileData' => ['fileUri' => 'https://safe.example/file']]]]]]]] as $payload) {
            $result = $this->parse($payload);
            $this->assertSame('provider_completed_without_image', $result['status']);
            $this->assertSame(0, $result['diagnostic']['image_part_count']);
        }
    }

    public function test_moderation_http_and_malformed_responses_have_safe_statuses(): void
    {
        $moderated = $this->parse(['predictions' => [['raiFilteredReason' => 'blocked']]]);
        $forbidden = app(GeminiImageResponseParser::class)->parse(new Response(new PsrResponse(403, ['Content-Type' => 'application/json'], '{}')), 12, 34);
        $malformed = app(GeminiImageResponseParser::class)->parse(new Response(new PsrResponse(200, ['Content-Type' => 'text/plain'], '{not-json}')), 12, 34);

        $this->assertSame('provider_blocked', $moderated['status']);
        $this->assertSame('IMAGE_PROVIDER_MODERATION_BLOCKED', $moderated['error_code']);
        $this->assertSame('IMAGE_PROVIDER_PERMISSION_DENIED', $forbidden['error_code']);
        $this->assertSame('parser_failed', $malformed['status']);
    }

    public function test_malformed_or_oversized_image_payload_is_rejected(): void
    {
        config()->set('ai_images.max_response_payload_bytes', 64);
        $malformed = $this->parse(['predictions' => [['bytesBase64Encoded' => '%%%not-base64%%%', 'mimeType' => 'image/png']]]);
        $oversized = $this->parse(['predictions' => [$this->image()]]);

        $this->assertSame('parser_failed', $malformed['status']);
        $this->assertSame('parser_failed', $oversized['status']);
        $this->assertSame('IMAGE_PROVIDER_INVALID_IMAGE_PAYLOAD', $oversized['error_code']);
    }

    /** @return array<string,mixed> */
    private function parse(array $payload): array
    {
        return $this->response($payload);
    }

    /** @return array<string,mixed> */
    private function response(array|string $payload, int $status = 200, bool $json = true): array
    {
        Http::fake(['https://provider.test/*' => Http::response($payload, $status, $json ? ['Content-Type' => 'application/json', 'X-Goog-Request-Id' => 'safe-request-id'] : ['Content-Type' => 'text/plain'])]);
        $response = Http::get('https://provider.test/predict');

        return app(GeminiImageResponseParser::class)->parse($response, 12, 34);
    }

    /** @return array{bytesBase64Encoded:string,mimeType:string} */
    private function image(): array
    {
        return ['bytesBase64Encoded' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6h3IAAAAASUVORK5CYII=', 'mimeType' => 'image/png'];
    }
}
