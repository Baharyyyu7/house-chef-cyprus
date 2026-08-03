<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PublishedRecipeRepairProposalService;
use Illuminate\Console\Command;

final class PreparePublishedRecipeRepairBatch extends Command
{
    protected $signature = 'content:published-recipe-repair {--batch=1} {--write : Private editör paketlerini kaydet} {--json}';

    protected $description = 'Yayınlanmış tarifler için canlı içeriği değiştirmeden özgün onarım ve medya inceleme önerileri hazırlar.';

    public function handle(PublishedRecipeRepairProposalService $service): int
    {
        if ((int) $this->option('batch') !== 1) {
            $this->error('Bu katalog yalnız kontrollü Batch 1 paketini içerir.');

            return self::INVALID;
        }

        $result = $service->prepare((bool) $this->option('write'));
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Seçilen', 'Editör incelemesi', 'Medya incelemesi', 'Aday görsel'], [[$result['selected'], $result['ready_for_editor_review'], $result['ready_for_media_review'], $result['image_candidates_created']]]);
            $this->info('Canlı tarif, doğrulama ve medya kayıtları değiştirilmedi.');
        }

        return self::SUCCESS;
    }
}
