<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\RecipeFactoryReconciliationService;
use Illuminate\Console\Command;

final class RecipeFactoryReconcile extends Command
{
    protected $signature = 'recipe-factory:reconcile {--write : Yalnız reversible review evidence kayıtlarını güncelle} {--json : Makine okunur çıktı}';

    protected $description = 'Tarif kataloğunu duplicate, kimlik, ölçü ve güvenlik kanıtlarıyla geri alınabilir biçimde denetler.';

    public function handle(RecipeFactoryReconciliationService $service): int
    {
        $result = $service->reconcile((bool) $this->option('write'));
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->line(sprintf(
            'Denetlenen: %d · Meşru tarif sayısı: %d · Yeni tarif gereksinimi: %d · Exact duplicate: %d',
            $result['audited'],
            $result['legitimate_recipe_count'],
            $result['new_recipes_required'],
            $result['exact_duplicates'],
        ));

        return self::SUCCESS;
    }
}
