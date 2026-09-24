<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Localization\ContentTranslationImporter;
use Illuminate\Console\Command;

final class ImportContentTranslations extends Command
{
    protected $signature = 'content:import-translations {--locale=en : Target locale} {--dry-run : Validate without writing} {--json}';

    protected $description = 'Imports the versioned recipe and blog translations used by the localized editions (/en).';

    public function handle(ContentTranslationImporter $importer): int
    {
        $locale = (string) $this->option('locale');
        if ($locale === config('localization.default') || ! array_key_exists($locale, (array) config('localization.locales'))) {
            $this->error('UNSUPPORTED_TRANSLATION_LOCALE');

            return self::FAILURE;
        }

        $result = $importer->import($locale, (bool) $this->option('dry-run'));
        if ((bool) $this->option('json')) {
            $this->line(json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        } else {
            foreach (['recipes', 'blog'] as $type) {
                $this->line(sprintf('%s: %d imported, %d unchanged, %d without source, %d invalid', $type, $result[$type]['imported'], $result[$type]['unchanged'], count($result[$type]['missing_source']), count($result[$type]['invalid'])));
            }
        }

        // Missing sources are expected on databases that do not carry the full
        // catalogue; only malformed catalogue entries are a release blocker.
        return $result['recipes']['invalid'] === [] && $result['blog']['invalid'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
