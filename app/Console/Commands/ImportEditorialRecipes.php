<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\EditorialRecipeImporter;
use Illuminate\Console\Command;

final class ImportEditorialRecipes extends Command
{
    protected $signature = 'recipes:import-editorial {--dry-run} {--validate-only} {--batch=all} {--skip-published} {--json}';

    protected $description = 'Versioned editoryal tarif kataloğunu doğrular veya güvenli olarak içe aktarır.';

    public function handle(EditorialRecipeImporter $importer): int
    {
        try {
            $result = $importer->import((bool) $this->option('dry-run'), (bool) $this->option('validate-only'), (string) $this->option('batch'), (bool) $this->option('skip-published'));
        } catch (\InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::INVALID;
        }
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE));
        } else {
            $this->table(['Toplam', 'Oluşturulan', 'Güncellenen', 'Atlanan', 'Geçersiz'], [[$result['total'], $result['created'], $result['updated'], $result['skipped'], $result['invalid']]]);
        }

        return self::SUCCESS;
    }
}
