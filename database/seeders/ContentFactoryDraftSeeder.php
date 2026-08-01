<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\ContentFactoryEnrichmentService;
use App\Services\ContentFactoryFactCheckService;
use App\Services\ContentFactoryService;
use App\Services\EditorialOperationsService;
use Illuminate\Database\Seeder;

final class ContentFactoryDraftSeeder extends Seeder
{
    public function run(): void
    {
        app(ContentFactoryService::class)->seedAll();
        app(ContentFactoryEnrichmentService::class)->enrichAll();
        app(ContentFactoryFactCheckService::class)->triage();
        app(EditorialOperationsService::class)->refresh();
    }
}
