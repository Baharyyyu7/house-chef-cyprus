<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\MediaExcellenceService;
use Illuminate\Console\Command;

final class MediaLicenseAudit extends Command
{
    protected $signature = 'media:license-audit {--limit=100 : Güvenli batch sınırı} {--dry-run : Kayıt yazma} {--json : Makine okunabilir çıktı}';

    protected $description = 'Medya lisans zincirini denetler; insan lisans onayı vermez';

    public function handle(MediaExcellenceService $media): int
    {
        $result = $this->option('dry-run') ? ['scanned' => 0, 'missing' => 0, 'dry_run' => true] : $media->auditLicense(max(1, min(100, (int) $this->option('limit'))));
        $this->option('json') ? $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE)) : $this->info('License audit · taranan: '.$result['scanned'].' · eksik: '.$result['missing']);

        return self::SUCCESS;
    }
}
