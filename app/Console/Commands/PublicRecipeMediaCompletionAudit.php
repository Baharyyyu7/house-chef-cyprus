<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\PublicRecipeMediaCompletionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class PublicRecipeMediaCompletionAudit extends Command
{
    protected $signature = 'recipe-media:completion-audit {--probe-public : Güvenli HTTP HEAD kontrolü} {--manifest= : Yalnız uygun kayıtlar için apply manifest yolu} {--json : JSON çıktı}';

    protected $description = 'Yayınlanmış tariflerin medya failure mode envanterini ve güvenli apply manifestini üretir.';

    public function handle(PublicRecipeMediaCompletionService $service): int
    {
        $report = $service->audit((bool) $this->option('probe-public'));
        if (is_string($this->option('manifest')) && $this->option('manifest') !== '') {
            $path = base_path((string) $this->option('manifest'));
            File::ensureDirectoryExists(dirname($path));
            File::put($path, json_encode($service->applyManifest(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
            $report['manifest_path'] = $path;
        }
        $this->option('json')
            ? $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            : $this->info('Tarif: '.$report['published_recipes_audited'].' · hazır: '.$report['approved_media'].' · placeholder: '.$report['placeholder_cards_remaining']);

        return self::SUCCESS;
    }
}
