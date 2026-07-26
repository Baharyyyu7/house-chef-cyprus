<?php

namespace Tests\Feature\Import;

use App\DTOs\Importing\NormalizedRecipeData;
use App\Models\DataSource;
use App\Models\Recipe;
use App\Services\Importing\RecipeImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_is_idempotent_by_source_and_external_id(): void
    {
        $source = DataSource::create(['name' => 'Demo', 'type' => 'api']);
        $data = new NormalizedRecipeData('abc-1', 'Demo Tarif', 'demo-tarif', ['excerpt' => 'x']);
        $service = app(RecipeImportService::class);
        $service->import($source, $data);
        $service->import($source, $data);
        $this->assertSame(1, Recipe::count());
    }
}
