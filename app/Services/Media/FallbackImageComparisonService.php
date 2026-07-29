<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Contracts\Media\RecipeImageProviderInterface;
use App\Models\AiImageGeneration;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

final class FallbackImageComparisonService
{
    private const SCOPE = 'fallback_provider_comparison';

    public function __construct(
        private readonly RecipeImagePromptBuilder $prompts,
        private readonly BflRecipeImageProvider $bfl,
        private readonly StabilityRecipeImageProvider $stability,
        private readonly PrivateAiImageMediaStore $mediaStore,
    ) {}

    /** @return array{requested:int,image_returned:int,image_validated:int,blocked:int,estimated_cost_cents:int,actual_cost:string,providers:array<string,array<string,mixed>>} */
    public function run(?string $onlyProvider = null): array
    {
        if ($onlyProvider !== null && ! in_array($onlyProvider, ['bfl', 'stability'], true)) {
            throw new \InvalidArgumentException('Desteklenmeyen fallback görsel sağlayıcısı.');
        }

        $providerNames = $onlyProvider === null ? ['bfl', 'stability'] : [$onlyProvider];
        $recipe = Recipe::query()->with(['ingredients', 'media'])->where('slug', 'domates-corbasi')->first();
        if ($recipe === null) {
            return $this->blockedSummary($providerNames, 'recipe_not_found');
        }
        $prompt = $this->prompts->build($recipe, 'bright_ivory_editorial');
        if (! $prompt['ready']) {
            return $this->blockedSummary($providerNames, 'blocked_recipe_fidelity');
        }
        $summary = ['requested' => 0, 'image_returned' => 0, 'image_validated' => 0, 'blocked' => 0, 'estimated_cost_cents' => 0, 'actual_cost' => 'unknown', 'providers' => []];
        foreach ([$this->bfl, $this->stability] as $provider) {
            if (! in_array($provider->name(), $providerNames, true)) {
                continue;
            }
            $outcome = $this->runProvider($provider, $recipe, $prompt);
            $summary['providers'][$provider->name()] = $outcome;
            $summary['requested'] += (int) ($outcome['requested'] ?? 0);
            $summary['image_returned'] += (int) ($outcome['image_returned'] ?? 0);
            $summary['image_validated'] += (int) ($outcome['image_validated'] ?? 0);
            $summary['blocked'] += (int) ($outcome['blocked'] ?? 0);
            $summary['estimated_cost_cents'] += (int) ($outcome['estimated_cost_cents'] ?? 0);
        }

        return $summary;
    }

    /** @return array{requested:int,image_returned:int,image_validated:int,blocked:int,estimated_cost_cents:int,actual_cost:string,providers:array<string,array<string,mixed>>} */
    public function dryRun(): array
    {
        $providers = [];
        foreach ([$this->bfl, $this->stability] as $provider) {
            $health = $provider->health();
            $providers[$provider->name()] = [
                'status' => ! $health['configured'] || ! $health['enabled'] ? 'not_configured' : 'ready_for_controlled_call',
                'requested' => 0,
                'image_returned' => 0,
                'image_validated' => 0,
                'blocked' => ! $health['configured'] || ! $health['enabled'] ? 1 : 0,
                'estimated_cost_cents' => ! $health['configured'] || ! $health['enabled'] ? 0 : (int) config('ai_images.fallback_provider_comparison.'.$provider->name().'_estimate_cents'),
                'safe_message' => $health['safe_message'],
            ];
        }

        return [
            'requested' => 0,
            'image_returned' => 0,
            'image_validated' => 0,
            'blocked' => (int) collect($providers)->sum('blocked'),
            'estimated_cost_cents' => (int) collect($providers)->sum('estimated_cost_cents'),
            'actual_cost' => 'unknown',
            'providers' => $providers,
        ];
    }

    /** @param list<string> $providerNames @return array{requested:int,image_returned:int,image_validated:int,blocked:int,estimated_cost_cents:int,actual_cost:string,providers:array<string,array{status:string}>} */
    private function blockedSummary(array $providerNames, string $status): array
    {
        $providers = [];
        foreach ($providerNames as $providerName) {
            $providers[$providerName] = ['status' => $status];
        }

        return ['requested' => 0, 'image_returned' => 0, 'image_validated' => 0, 'blocked' => count($providerNames), 'estimated_cost_cents' => 0, 'actual_cost' => 'unknown', 'providers' => $providers];
    }

    /** @param array{prompt:string,negative_prompt:string,prompt_version:string,ready:bool} $prompt @return array<string,mixed> */
    private function runProvider(RecipeImageProviderInterface $provider, Recipe $recipe, array $prompt): array
    {
        $health = $provider->health();
        $estimate = (int) config('ai_images.fallback_provider_comparison.'.$provider->name().'_estimate_cents');
        if (! $health['configured'] || ! $health['enabled']) {
            return ['status' => 'not_configured', 'requested' => 0, 'image_returned' => 0, 'image_validated' => 0, 'blocked' => 1, 'estimated_cost_cents' => 0, 'safe_message' => $health['safe_message']];
        }
        $existing = AiImageGeneration::query()->where(['recipe_id' => $recipe->id, 'provider' => $provider->name(), 'generation_scope' => self::SCOPE, 'variation_number' => 1])->first();
        if ($existing !== null) {
            return ['status' => 'already_consumed', 'requested' => 0, 'image_returned' => 0, 'image_validated' => $existing->status === 'ready_for_review' ? 1 : 0, 'blocked' => 1, 'estimated_cost_cents' => 0, 'safe_message' => 'Bu provider için kontrollü karşılaştırma çağrısı zaten tüketildi.'];
        }
        $scope = AiImageGeneration::query()->where('generation_scope', self::SCOPE);
        $used = (clone $scope)->count();
        $providerUsed = (clone $scope)->where('provider', $provider->name())->count();
        $estimatedSpend = (int) ((clone $scope)->sum('estimated_cost_cents'));
        if (
            $used >= (int) config('ai_images.fallback_provider_comparison.max_total_requests')
            || $providerUsed >= (int) config('ai_images.fallback_provider_comparison.max_requests_per_provider')
            || $estimatedSpend + $estimate > (int) config('ai_images.fallback_provider_comparison.budget_cents')
        ) {
            return ['status' => 'budget_hard_stop', 'requested' => 0, 'image_returned' => 0, 'image_validated' => 0, 'blocked' => 1, 'estimated_cost_cents' => 0, 'safe_message' => 'Karşılaştırma bütçe veya çağrı limiti aşılamaz.'];
        }
        $record = AiImageGeneration::query()->create(['recipe_id' => $recipe->id, 'provider' => $provider->name(), 'generation_scope' => self::SCOPE, 'model' => $health['model'], 'prompt' => $prompt['prompt'], 'negative_prompt' => $prompt['negative_prompt'], 'prompt_version' => $prompt['prompt_version'], 'aspect_ratio' => '4:3', 'variation_number' => 1, 'status' => 'request_created', 'estimated_cost_cents' => $estimate, 'billing_status' => 'unknown', 'requested_at' => now(), 'status_transitions' => [['status' => 'request_created', 'at' => now()->toIso8601String()]]]);
        $result = $provider->generate($record->prompt, $record->negative_prompt ?? '', $record->aspect_ratio);
        $attributes = ['status' => $result->status, 'error_code' => $result->errorCode, 'error_message' => $result->safeMessage, 'provider_request_id' => $result->providerRequestId, 'billing_status' => 'unknown', 'parser_result' => $result->diagnostic['parser_result'] ?? null, 'diagnostic_snapshot' => $result->diagnostic, 'response_status' => $result->diagnostic['response_status'] ?? null, 'response_content_type' => $result->diagnostic['content_type'] ?? null, 'response_body_present' => (bool) ($result->diagnostic['response_body_present'] ?? false), 'response_bytes' => (int) ($result->diagnostic['payload_bytes'] ?? 0), 'request_bytes' => (int) ($result->diagnostic['request_bytes'] ?? 0), 'provider_latency_ms' => $result->diagnostic['latency_ms'] ?? null];
        $attributes['status_transitions'] = [['status' => 'request_created', 'at' => $record->requested_at], ['status' => $result->status, 'at' => now()->toIso8601String()]];
        $record->update($attributes);
        if (! $result->hasImage()) {
            return ['status' => $result->status, 'requested' => 1, 'image_returned' => 0, 'image_validated' => 0, 'blocked' => 0, 'estimated_cost_cents' => $estimate, 'safe_message' => $result->safeMessage];
        }
        try {
            $generation = $record->fresh('recipe');
            if ($generation === null) {
                throw new \RuntimeException('Görsel üretim kaydı bulunamadı.');
            }
            $media = DB::transaction(fn () => $this->mediaStore->store($generation, $result));
            $record->update(['media_id' => $media->id, 'status' => 'ready_for_review', 'generated_at' => now(), 'status_transitions' => array_merge((array) $record->status_transitions, [['status' => 'ready_for_review', 'at' => now()->toIso8601String()]])]);

            return ['status' => 'ready_for_review', 'requested' => 1, 'image_returned' => 1, 'image_validated' => 1, 'blocked' => 0, 'estimated_cost_cents' => $estimate, 'safe_message' => 'Görsel private depoya alındı; admin incelemesi zorunludur.'];
        } catch (\Throwable) {
            $record->update(['status' => 'media_validation_failed', 'error_code' => 'IMAGE_PROVIDER_MEDIA_VALIDATION_FAILED', 'error_message' => 'Görsel private medya doğrulamasını geçemedi.']);

            return ['status' => 'media_validation_failed', 'requested' => 1, 'image_returned' => 1, 'image_validated' => 0, 'blocked' => 0, 'estimated_cost_cents' => $estimate, 'safe_message' => 'Görsel medya doğrulamasını geçemedi.'];
        }
    }
}
