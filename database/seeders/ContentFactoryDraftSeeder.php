<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\ContentFactoryService;
use Illuminate\Database\Seeder;

final class ContentFactoryDraftSeeder extends Seeder
{
    public function run(): void
    {
        app(ContentFactoryService::class)->seedAll();
    }
}
