<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SeoAuthorityService;
use Illuminate\Console\Command;

final class SeoHealth extends Command
{
    protected $signature = 'seo:health {--json : Makine okunabilir çıktı}';

    protected $description = 'Yalnız gerçek katalog ve audit verisine dayalı SEO sağlık özetini gösterir.';

    public function handle(SeoAuthorityService $service): int
    {
        $snapshot = $service->snapshot();
        if ($this->option('json')) {
            $this->line((string) json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("SEO sağlık skoru {$snapshot['score']}/100 | indexlenebilir tarif {$snapshot['indexable']['recipes']} | blog {$snapshot['indexable']['articles']} | açık sorun ".array_sum($snapshot['issues']));
        }

        return self::SUCCESS;
    }
}
