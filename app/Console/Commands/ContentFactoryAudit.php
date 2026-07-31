<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ContentFactoryEnrichmentService;
use App\Services\ContentFactoryService;
use Illuminate\Console\Command;

final class ContentFactoryAudit extends Command
{
    protected $signature = 'content:factory-audit {--json}';

    protected $description = 'Taslak katalogda yayın ve doğrulama sızıntısı olmadığını denetler.';

    public function handle(ContentFactoryService $factory, ContentFactoryEnrichmentService $enrichment): int
    {
        $result = $factory->audit();
        $safe = $result['public_recipe_leaks'] === 0 && $result['verified_recipe_leaks'] === 0 && $result['public_blog_leaks'] === 0;
        $result = array_merge($result, $enrichment->audit());
        $result['safe'] = $safe;
        $this->option('json') ? $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->line('Tarif taslakları: '.$result['recipe_drafts'].', blog taslakları: '.$result['blog_drafts'].', public sızıntı: '.($safe ? '0' : 'tespit edildi'));

        return $safe ? self::SUCCESS : self::FAILURE;
    }
}
