<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\AiImageGeneration;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

/** Runs a single, review-only local image generation. No provider request is made while disabled. */
final class LocalComfyUiImageGenerationService
{
    private const SCOPE = 'local_comfyui';

    public function __construct(
        private readonly ComfyUiRecipeImageProvider $provider,
        private readonly RecipeImagePromptBuilder $prompts,
        private readonly PrivateAiImageMediaStore $mediaStore,
    ) {}

    /** @return array<string,mixed> */
    public function generateTomatoSoup(): array
    {
        $recipe = Recipe::query()->with(['ingredients', 'media'])->where('slug', 'domates-corbasi')->first();
        if ($recipe === null) {
            return ['status' => 'recipe_not_found', 'requested' => 0, 'safe_message' => 'Domates Çorbası bulunamadı.'];
        }

        return $this->generate($recipe);
    }

    /** @return array<string,mixed> */
    public function generate(Recipe $recipe): array
    {
        $health = $this->provider->health();
        if (! $health['configured'] || ! $health['enabled']) {
            return ['status' => 'not_configured', 'requested' => 0, 'safe_message' => $health['safe_message']];
        }
        $prompt = $this->prompts->build($recipe);
        if (! $prompt['ready']) {
            return ['status' => 'blocked_recipe_fidelity', 'requested' => 0, 'safe_message' => implode(' ', $prompt['blocking_reasons'])];
        }
        $existing = AiImageGeneration::query()->where([
            'recipe_id' => $recipe->id,
            'provider' => $this->provider->name(),
            'generation_scope' => self::SCOPE,
            'variation_number' => 1,
        ])->first();
        if ($existing !== null) {
            return ['status' => 'already_consumed', 'requested' => 0, 'safe_message' => 'Bu tarifin yerel üretim kaydı zaten var.'];
        }
        $record = AiImageGeneration::query()->create([
            'recipe_id' => $recipe->id,
            'provider' => $this->provider->name(),
            'generation_scope' => self::SCOPE,
            'model' => $health['model'],
            'prompt' => $prompt['prompt'],
            'negative_prompt' => $prompt['negative_prompt'],
            'prompt_version' => $prompt['prompt_version'],
            'aspect_ratio' => '4:3',
            'variation_number' => 1,
            'status' => 'request_created',
            'estimated_cost_cents' => 0,
            'billing_status' => 'not_applicable',
            'requested_at' => now(),
            'status_transitions' => [['status' => 'request_created', 'at' => now()->toIso8601String()]],
        ]);
        $result = $this->provider->generate($record->prompt, $record->negative_prompt ?? '', $record->aspect_ratio);
        $record->update([
            'status' => $result->status,
            'error_code' => $result->errorCode,
            'error_message' => $result->safeMessage,
            'provider_request_id' => $result->providerRequestId,
            'billing_status' => 'not_applicable',
            'parser_result' => $result->diagnostic['parser_result'] ?? null,
            'diagnostic_snapshot' => $result->diagnostic,
            'response_status' => $result->diagnostic['response_status'] ?? null,
            'response_content_type' => $result->diagnostic['content_type'] ?? null,
            'response_body_present' => (bool) ($result->diagnostic['response_body_present'] ?? false),
            'response_bytes' => (int) ($result->diagnostic['payload_bytes'] ?? 0),
            'request_bytes' => (int) ($result->diagnostic['request_bytes'] ?? 0),
            'provider_latency_ms' => $result->diagnostic['latency_ms'] ?? null,
        ]);
        if (! $result->hasImage()) {
            return ['status' => $result->status, 'requested' => 1, 'safe_message' => $result->safeMessage];
        }
        try {
            $generation = $record->fresh('recipe');
            if ($generation === null) {
                throw new \RuntimeException('Missing image generation record.');
            }
            $media = DB::transaction(fn () => $this->mediaStore->store($generation, $result));
            $record->update([
                'media_id' => $media->id,
                'status' => 'ready_for_review',
                'generated_at' => now(),
                'status_transitions' => [['status' => 'request_created', 'at' => now()->toIso8601String()], ['status' => 'ready_for_review', 'at' => now()->toIso8601String()]],
            ]);

            return ['status' => 'ready_for_review', 'requested' => 1, 'media_public_id' => $media->public_id, 'safe_message' => 'Görsel private depoya alındı; editoryal inceleme zorunludur.'];
        } catch (\Throwable) {
            $record->update(['status' => 'media_validation_failed', 'error_code' => 'LOCAL_IMAGE_MEDIA_VALIDATION_FAILED', 'error_message' => 'Yerel görsel medya doğrulamasını geçemedi.']);

            return ['status' => 'media_validation_failed', 'requested' => 1, 'safe_message' => 'Görsel medya doğrulamasını geçemedi.'];
        }
    }
}
