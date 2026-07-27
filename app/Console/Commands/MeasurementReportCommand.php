<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\IngredientUnitConversion;
use App\Models\RecipeIngredient;
use Illuminate\Console\Command;

final class MeasurementReportCommand extends Command
{
    protected $signature = 'measurement:report {--json : Makine tarafından okunabilir çıktı}';

    protected $description = 'Ev ölçüsü ve canonical miktar tutarlılığını raporlar';

    public function handle(): int
    {
        $payload = ['recipe_ingredients' => RecipeIngredient::query()->count(), 'missing_display' => RecipeIngredient::query()->whereNull('display_measure_code')->count(), 'missing_canonical' => RecipeIngredient::query()->whereNull('canonical_quantity')->count(), 'low_confidence' => RecipeIngredient::query()->whereIn('conversion_confidence', ['low', 'unknown'])->count(), 'conversions' => IngredientUnitConversion::query()->count(), 'active_conversions' => IngredientUnitConversion::query()->where('is_active', true)->count(), 'missing_sources' => IngredientUnitConversion::query()->whereNull('source_name')->count()];
        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_THROW_ON_ERROR));
        } else {
            foreach ($payload as $key => $value) {
                $this->line("{$key}: {$value}");
            }
        }

        return self::SUCCESS;
    }
}
