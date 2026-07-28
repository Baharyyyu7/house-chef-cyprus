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
    public function __construct(private readonly AiImagePilotService $pilot, private readonly GeminiImageResponseParser $parser) {}

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
            $records->each(fn (AiImageGeneration $record) => $this->transition($record, 'request_created', ['requested_at' => now()]));
            $reserved += $count * $price;
            $outcome = $this->request((string) $records->first()->prompt, $count, $settings);
            if ($outcome['status'] !== 'image_received') {
                $records->each(fn (AiImageGeneration $record) => $this->recordOutcome($record, $outcome));
                $failed += $count;

                continue;
            }
            foreach ($records as $index => $record) {
                $image = $outcome['images'][$index] ?? null;
                if ($image === null) {
                    $this->recordOutcome($record, array_merge($outcome, ['status' => 'provider_completed_without_image', 'error_code' => 'IMAGE_PROVIDER_COMPLETED_WITHOUT_IMAGE', 'error_message' => 'Sağlayıcı istenen varyasyon sayısına ulaşmadı.']));
                    $failed++;

                    continue;
                }
                $this->storePrivateResult($record, $image['bytes'], $image['mime'], $price);
                $generated++;
            }
        }

        return ['planned' => $plan['variation_count'], 'generated' => $generated, 'failed' => $failed, 'blocked' => $plan['variation_count'] - $generated - $failed, 'reserved_cost_cents' => $reserved, 'safe_message' => 'İstekler private depolamaya yazıldı; hiçbir çıktı otomatik onaylanmadı veya yayınlanmadı.'];
    }

    /** @return array{generated:int,failed:int,blocked:int,estimated_cost_cents:int,safe_message:string} */
    public function recoverTomatoSoup(): array
    {
        $settings = (array) config('ai_images.providers.gemini', []);
        $limit = (int) config('ai_images.diagnostic_recovery.max_calls');
        $existing = AiImageGeneration::query()->where('generation_scope', 'diagnostic_recovery')->count();
        $budget = (int) config('ai_images.diagnostic_recovery.budget_cents');
        $price = (int) config('ai_images.pilot_cost_estimate_cents_per_image');
        if ($existing >= $limit || $price > $budget || ! filled($settings['key'] ?? null)) {
            return ['generated' => 0, 'failed' => 0, 'blocked' => 1, 'estimated_cost_cents' => $price, 'safe_message' => 'Tek çağrılık diagnostic recovery limiti veya bütçe koruması çağrıyı engelledi.'];
        }
        $recipe = $this->pilot->recipes()->firstWhere('slug', 'domates-corbasi');
        if ($recipe === null) {
            return ['generated' => 0, 'failed' => 0, 'blocked' => 1, 'estimated_cost_cents' => $price, 'safe_message' => 'Recovery için seçilen tarif bulunamadı.'];
        }
        $prompt = app(RecipeImagePromptBuilder::class)->build($recipe, 'bright_ivory_editorial');
        if (! $prompt['ready']) {
            return ['generated' => 0, 'failed' => 0, 'blocked' => 1, 'estimated_cost_cents' => $price, 'safe_message' => 'Tarif doğruluk koruması recovery çağrısını engelledi.'];
        }
        $record = AiImageGeneration::query()->create([
            'recipe_id' => $recipe->id, 'provider' => 'gemini', 'generation_scope' => 'diagnostic_recovery', 'model' => $settings['model'],
            'prompt' => $prompt['prompt'], 'negative_prompt' => $prompt['negative_prompt'], 'prompt_version' => $prompt['prompt_version'],
            'aspect_ratio' => '4:3', 'variation_number' => 1, 'status' => 'request_created', 'estimated_cost_cents' => $price,
            'billing_status' => 'unknown', 'requested_at' => now(), 'status_transitions' => [['status' => 'request_created', 'at' => now()->toIso8601String()]],
        ]);
        $outcome = $this->request((string) $record->prompt, 1, $settings);
        $this->recordOutcome($record, $outcome);
        if ($outcome['status'] !== 'image_received' || ! isset($outcome['images'][0])) {
            return ['generated' => 0, 'failed' => 1, 'blocked' => 0, 'estimated_cost_cents' => $price, 'safe_message' => 'Recovery çağrısı kullanılabilir görsel döndürmedi; otomatik retry yapılmadı.'];
        }
        $this->storePrivateResult($record->fresh('recipe'), $outcome['images'][0]['bytes'], $outcome['images'][0]['mime'], $price);

        return ['generated' => 1, 'failed' => 0, 'blocked' => 0, 'estimated_cost_cents' => $price, 'safe_message' => 'Recovery görseli private depolamaya alındı; manuel review zorunludur.'];
    }

    private function spentAndReserved(): int
    {
        return (int) AiImageGeneration::query()->where('provider', 'gemini')->whereNotNull('actual_cost_cents')->sum('actual_cost_cents');
    }

    /** @return array{images:list<array{bytes:string,mime:string}>,diagnostic:array<string,mixed>,status:string,error_code:?string,error_message:?string} */
    private function request(string $prompt, int $count, array $settings): array
    {
        $payload = ['instances' => [['prompt' => $prompt]], 'parameters' => ['sampleCount' => $count, 'aspectRatio' => '4:3', 'personGeneration' => 'dont_allow']];
        $started = hrtime(true);
        $response = Http::withHeaders(['x-goog-api-key' => (string) $settings['key']])->acceptJson()->contentType('application/json')->timeout(60)
            ->post(rtrim((string) $settings['endpoint'], '/').'/models/'.rawurlencode((string) $settings['model']).':predict', $payload);

        return $this->parser->parse($response, strlen((string) json_encode($payload)), (int) ((hrtime(true) - $started) / 1_000_000));
    }

    /** @param array{diagnostic:array<string,mixed>,status:string,error_code:?string,error_message:?string} $outcome */
    private function recordOutcome(AiImageGeneration $record, array $outcome): void
    {
        $diagnostic = $outcome['diagnostic'];
        // A successful HTTP response and a usable image are deliberately separate
        // states: the provider can accept a request without returning media.
        if (($diagnostic['response_status'] ?? 0) >= 200 && ($diagnostic['response_status'] ?? 0) < 300) {
            $this->appendTransition($record, 'provider_accepted');
        }
        $this->transition($record, $outcome['status'], [
            'error_code' => $outcome['error_code'], 'error_message' => $outcome['error_message'],
            'response_status' => $diagnostic['response_status'], 'provider_request_id' => $diagnostic['provider_request_id'],
            'response_content_type' => $diagnostic['response_content_type'], 'response_body_present' => $diagnostic['response_body_present'],
            'candidate_count' => $diagnostic['candidate_count'], 'part_count' => $diagnostic['part_count'], 'image_part_count' => $diagnostic['image_part_count'],
            'text_part_count' => $diagnostic['text_part_count'], 'finish_reason' => $diagnostic['finish_reason'], 'moderation_status' => $diagnostic['moderation_status'],
            'request_bytes' => $diagnostic['request_bytes'], 'response_bytes' => $diagnostic['response_bytes'], 'provider_latency_ms' => $diagnostic['provider_latency_ms'],
            'billing_status' => $diagnostic['billing_status'], 'parser_result' => $diagnostic['parser_result'], 'diagnostic_snapshot' => $diagnostic,
        ]);
    }

    private function transition(AiImageGeneration $record, string $status, array $attributes = []): void
    {
        $history = $this->transitionHistory($record, $status);
        $record->update(array_merge($attributes, ['status' => $status, 'status_transitions' => $history]));
    }

    private function appendTransition(AiImageGeneration $record, string $status): void
    {
        $record->update(['status_transitions' => $this->transitionHistory($record, $status)]);
    }

    /** @return list<array{status:string,at:string}> */
    private function transitionHistory(AiImageGeneration $record, string $status): array
    {
        $history = (array) $record->status_transitions;
        $history[] = ['status' => $status, 'at' => now()->toIso8601String()];

        return $history;
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
            $this->transition($record, 'ready_for_review', ['media_id' => $media->id, 'generated_at' => now(), 'actual_cost_cents' => $recordedCostCents, 'error_code' => null, 'error_message' => null, 'parser_result' => 'image_validated']);
        });
    }
}
