<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AI\NvidiaAiKitchenProvider;
use Illuminate\Console\Command;
use Throwable;

final class NvidiaModels extends Command
{
    protected $signature = 'nvidia:models {--json : Makine okunabilir çıktı}';

    protected $description = 'NVIDIA API anahtarının erişebildiği modelleri secret göstermeden listeler';

    public function handle(NvidiaAiKitchenProvider $provider): int
    {
        if (! (bool) ($provider->health()['configured'] ?? false)) {
            return $this->failure('NVIDIA_API_KEY_MISSING');
        }

        try {
            $models = $provider->models();
        } catch (Throwable) {
            return $this->failure('NVIDIA_MODEL_CATALOG_UNAVAILABLE');
        }

        if ($this->option('json')) {
            $this->line((string) json_encode([
                'provider' => 'nvidia',
                'count' => count($models),
                'models' => $models,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info(sprintf('%d erişilebilir NVIDIA modeli bulundu.', count($models)));
        $this->table(['Model', 'Sağlayıcı'], collect($models)->map(fn (array $model): array => [$model['id'], $model['owned_by']])->all());

        return self::SUCCESS;
    }

    private function failure(string $code): int
    {
        if ($this->option('json')) {
            $this->line((string) json_encode(['provider' => 'nvidia', 'status' => 'failed', 'error' => $code], JSON_UNESCAPED_SLASHES));
        } else {
            $this->error($code);
        }

        return self::FAILURE;
    }
}
