<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AiInteraction;
use App\Services\AI\AiKitchenService;
use Illuminate\Console\Command;

final class AiKitchenMorningBrief extends Command
{
    protected $signature = 'ai-kitchen:morning-brief {--json : Makine okunabilir çıktı}';

    protected $description = 'AI Kitchen için yalnız gözlemlenebilir sabah operasyon özetini üretir';

    public function handle(AiKitchenService $kitchen): int
    {
        $topPurposes = AiInteraction::query()->selectRaw('purpose, count(*) as requests')->groupBy('purpose')->orderByDesc('requests')->limit(10)->get()->map(fn (AiInteraction $row): array => ['purpose' => $row->purpose, 'requests' => (int) $row->getAttribute('requests')])->all();
        $report = ['generated_at' => now()->toIso8601String(), 'stats' => $kitchen->stats(), 'top_purposes' => $topPurposes, 'note' => 'Bu rapor hiçbir AI çıktısını yayınlamaz veya doğrulanmış olarak işaretlemez.'];
        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        $this->info('AI Kitchen sabah özeti');
        $this->line('İstek: '.$report['stats']['requests'].' · Cache hit: '.$report['stats']['cache_hits'].' · Hata: '.$report['stats']['failures']);

        return self::SUCCESS;
    }
}
