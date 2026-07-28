<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\PilotRecipeDataCorrectionService;
use Illuminate\Console\Command;

final class CorrectAiImagePilotRecipeData extends Command
{
    protected $signature = 'recipes:correct-ai-image-pilot {--json : Makine tarafından okunabilir sonuç üret}';

    protected $description = 'AI görsel pilotu için sekiz tarifin editoryal veri uyumunu düzeltir.';

    public function handle(PilotRecipeDataCorrectionService $corrections): int
    {
        $result = $corrections->apply();
        if ((bool) $this->option('json')) {
            $this->line(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info("{$result['corrected']} tarif düzeltildi; {$result['needs_review']} tarif inceleme bekliyor.");

        return self::SUCCESS;
    }
}
