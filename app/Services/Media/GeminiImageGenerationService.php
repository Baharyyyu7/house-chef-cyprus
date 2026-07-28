<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\AiImageGeneration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Executes the explicitly approved, tightly budgeted Imagen pilot. */
final class GeminiImageGenerationService
{
    public function __construct(private readonly AiImagePilotService $pilot) {}

    /** @return array{planned:int,generated:int,failed:int,blocked:int,reserved_cost_cents:int,safe_message:string} */
    public function execute(): array
    {
        $this->pilot->synchronize('gemini');
        $plan = $this->pilot->costPreview();
        if (! $this->pilot->generationAllowed()) {
            return ['planned' => 0, 'generated' => 0, 'failed' => 0, 'blocked' => $plan['variation_count'], 'reserved_cost_cents' => 0, 'safe_message' => 'Pilot üretim koruması etkin: bütçe, sağlayıcı veya tarif doğruluğu koşulu sağlanmadı.'];
        }

        $settings = (array) config('ai_images.providers.gemini', []);
        $budget = (int) config('ai_images.daily_budget_cents');
        $price = (int) config('ai_images.pilot_cost_estimate_cents_per_image');
        $generated = 0;
        $failed = 0;
        $reserved = 0;
        $groups = AiImageGeneration::query()->with('recipe.ingredients')->where('provider', 'gemini')->where('status', 'planned')->orderBy('recipe_id')->orderBy('variation_number')->get()->groupBy('recipe_id');

        foreach ($groups as $records) {
            /** @var Collection<int,AiImageGeneration> $records */
            $records = $records->take(2)->values();
            $count = $records->count();
            if ($count === 0 || $this->spentAndReserved() + $reserved + ($count * $price) > $budget) {
                $records->each(fn (AiImageGeneration $record) => $record->update(['status' => 'blocked_budget', 'error_code' => 'PILOT_BUDGET_HARD_STOP', 'error_message' => 'Pilot bütçe limiti aşılamaz.']));

                continue;
            }

            // Reserve before the request. A timeout is never retried automatically.
            // `actual_cost_cents` remains null until a usable output exists; API
            // invoice data is not exposed by the response and must not be guessed.
            $records->each(fn (AiImageGeneration $record) => $record->update(['status' => 'requesting', 'requested_at' => now()]));
            $reserved += $count * $price;
            $response = Http::withHeaders(['x-goog-api-key' => (string) $settings['key']])
                ->acceptJson()->timeout(60)->post(rtrim((string) $settings['endpoint'], '/').'/models/'.rawurlencode((string) $settings['model']).':predict', [
                    'instances' => [['prompt' => (string) $records->first()->prompt]],
                    'parameters' => ['sampleCount' => $count, 'aspectRatio' => '4:3', 'personGeneration' => 'dont_allow'],
                ]);

            if (! $response->successful()) {
                $code = match ($response->status()) {
                    402 => 'IMAGE_PROVIDER_PAYMENT_REQUIRED',
                    403 => 'IMAGE_PROVIDER_FORBIDDEN',
                    429 => 'IMAGE_PROVIDER_RATE_LIMITED',
                    default => 'IMAGE_PROVIDER_UNAVAILABLE',
                };
                $records->each(fn (AiImageGeneration $record) => $record->update(['status' => 'provider_failed', 'error_code' => $code, 'error_message' => 'Görsel sağlayıcısı isteği güvenli biçimde tamamlanamadı.']));
                $failed += $count;

                continue;
            }

            $predictions = collect((array) $response->json('predictions', []));
            foreach ($records as $index => $record) {
                $image = $this->imagePayload((array) ($predictions->get($index) ?? []));
                if ($image === null) {
                    $record->update(['status' => 'provider_failed', 'error_code' => 'IMAGE_PROVIDER_INVALID_RESPONSE', 'error_message' => 'Görsel sağlayıcısı kullanılabilir bir görsel çıktısı döndürmedi.']);
                    $failed++;

                    continue;
                }
                $this->storePrivateResult($record, $image['bytes'], $image['mime'], $price);
                $generated++;
            }
        }

        return ['planned' => $plan['variation_count'], 'generated' => $generated, 'failed' => $failed, 'blocked' => $plan['variation_count'] - $generated - $failed, 'reserved_cost_cents' => $reserved, 'safe_message' => 'İstekler private depolamaya yazıldı; hiçbir çıktı otomatik onaylanmadı veya yayınlanmadı.'];
    }

    private function spentAndReserved(): int
    {
        return (int) AiImageGeneration::query()->where('provider', 'gemini')->whereNotNull('actual_cost_cents')->sum('actual_cost_cents');
    }

    /** @return array{bytes:string,mime:string}|null */
    private function imagePayload(array $prediction): ?array
    {
        $encoded = $prediction['bytesBase64Encoded'] ?? data_get($prediction, 'image.imageBytes');
        if (! is_string($encoded) || $encoded === '') {
            return null;
        }
        $bytes = base64_decode($encoded, true);
        if ($bytes === false || strlen($bytes) < 64) {
            return null;
        }

        return ['bytes' => $bytes, 'mime' => (string) ($prediction['mimeType'] ?? data_get($prediction, 'image.mimeType') ?? 'image/png')];
    }

    private function storePrivateResult(AiImageGeneration $record, string $bytes, string $mime, int $recordedCostCents): void
    {
        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default => 'png',
        };
        $path = 'media/ai-pilot/'.$record->recipe->public_id.'/variation-'.$record->variation_number.'.'.$extension;
        Storage::disk('local')->put($path, $bytes);
        $dimensions = @getimagesizefromstring($bytes) ?: [null, null];

        DB::transaction(function () use ($record, $path, $bytes, $mime, $dimensions, $recordedCostCents): void {
            $media = $record->recipe->media()->create([
                'public_id' => (string) Str::ulid(),
                'kind' => 'image', 'disk' => 'local', 'path' => $path, 'mime_type' => $mime, 'size' => strlen($bytes),
                'sort_order' => 50 + $record->variation_number, 'visibility' => 'private',
                'alt_text' => $record->recipe->title.' için inceleme bekleyen AI görsel taslağı',
                'width' => $dimensions[0], 'height' => $dimensions[1], 'processing_status' => 'ready', 'thumbnail_status' => 'not_requested',
                'source_name' => 'Gemini Imagen pilot', 'license' => 'Provider output; House Chef Cyprus internal review required',
                'attribution' => 'AI-generated with Gemini Imagen', 'verification_status' => 'pending_review', 'confidence' => 0,
                'generated_by_ai' => true, 'ai_provider' => 'gemini', 'ai_model' => $record->model, 'ai_prompt_version' => $record->prompt_version,
                'ai_review_status' => 'pending_review', 'checksum_sha256' => hash('sha256', $bytes),
                'provider_metadata' => ['response_mime_type' => $mime, 'storage' => 'private_pending_review'],
            ]);
            $record->update(['media_id' => $media->id, 'status' => 'generated', 'generated_at' => now(), 'actual_cost_cents' => $recordedCostCents, 'error_code' => null, 'error_message' => null]);
        });
    }
}
