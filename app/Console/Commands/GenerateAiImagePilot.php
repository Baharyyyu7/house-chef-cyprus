<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\GeminiImageGenerationService;
use Illuminate\Console\Command;

final class GenerateAiImagePilot extends Command
{
    protected $signature = 'ai-images:generate-pilot {--execute : Ücretli, onaylı pilot çağrılarını açıkça başlatır} {--json : Makine tarafından okunabilir çıktı üretir}';

    protected $description = 'Onaylanan Gemini 10 tarif / 20 varyasyon görsel pilotunu private depolamaya üretir.';

    public function handle(GeminiImageGenerationService $generator): int
    {
        if (! (bool) $this->option('execute')) {
            $this->warn('Dry-run: ücretli çağrı yapılmadı. Gerçek pilot için açıkça --execute kullanın.');

            return self::SUCCESS;
        }
        $result = $generator->execute();
        if ((bool) $this->option('json')) {
            $this->line(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("Üretildi: {$result['generated']} | Başarısız: {$result['failed']} | Engellendi: {$result['blocked']}");
        }

        return self::SUCCESS;
    }
}
