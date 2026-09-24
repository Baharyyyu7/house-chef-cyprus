<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\Localization\ContentTranslationImporter;
use Illuminate\Database\Seeder;

final class ContentTranslationSeeder extends Seeder
{
    public function run(): void
    {
        app(ContentTranslationImporter::class)->import('en');
    }
}
