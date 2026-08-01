<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Contracts\AI\AiKitchenProviderInterface;

final class AiKitchenProviderRegistry
{
    /** @return list<AiKitchenProviderInterface> */
    public function chain(): array
    {
        $names = array_values(array_unique(array_filter([(string) config('ai_kitchen.default_provider'), ...(array) config('ai_kitchen.fallback_chain', ['mock'])])));

        return array_map(fn (string $name): AiKitchenProviderInterface => $name === 'mock' ? app(MockAiKitchenProvider::class) : new ConfiguredAiKitchenProvider($name), $names);
    }

    /** @return list<array<string, scalar|bool|null>> */
    public function health(): array
    {
        return collect(['mock', 'gemini', 'openai', 'claude', 'openrouter', 'ollama'])
            ->map(fn (string $name): array => ($name === 'mock' ? app(MockAiKitchenProvider::class) : new ConfiguredAiKitchenProvider($name))->health())
            ->all();
    }
}
