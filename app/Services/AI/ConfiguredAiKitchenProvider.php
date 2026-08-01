<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Contracts\AI\AiKitchenProviderInterface;

/** Adapter seam: live providers remain disabled until a separately approved implementation is configured. */
final class ConfiguredAiKitchenProvider implements AiKitchenProviderInterface
{
    public function __construct(private readonly string $provider) {}

    public function name(): string
    {
        return $this->provider;
    }

    public function health(): array
    {
        $settings = (array) config("ai_kitchen.providers.{$this->provider}", []);
        $enabled = (bool) ($settings['enabled'] ?? false);
        $configured = $this->provider === 'ollama'
            ? trim((string) ($settings['url'] ?? '')) !== ''
            : trim((string) ($settings['key'] ?? '')) !== '' && trim((string) ($settings['model'] ?? '')) !== '';

        return ['provider' => $this->provider, 'status' => $enabled && $configured ? 'configured_not_activated' : 'disabled', 'configured' => $configured, 'model' => (string) ($settings['model'] ?? ''), 'billable' => $this->provider !== 'ollama'];
    }

    public function respond(array $request): array
    {
        return ['status' => 'unavailable', 'text' => '', 'input_tokens' => 0, 'output_tokens' => 0, 'cost_cents' => 0, 'safe_error_code' => 'AI_PROVIDER_NOT_ACTIVATED'];
    }
}
