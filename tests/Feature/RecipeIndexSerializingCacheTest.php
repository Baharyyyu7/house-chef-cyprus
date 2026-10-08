<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class RecipeIndexSerializingCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_cuisine_filter_survives_a_serializing_cache(): void
    {
        // Üretim önbelleği nesne açmıyor (cache.serializable_classes=false).
        // Model koleksiyonu saklandığında ikinci istekte __PHP_Incomplete_Class
        // döndü ve /tarifler 500 verdi; array store bunu göstermez.
        $dizin = storage_path('framework/cache/test-'.uniqid());
        config(['cache.default' => 'file', 'cache.stores.file.path' => $dizin]);
        Cache::flush();

        $mutfak = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);
        foreach (range(1, 10) as $i) {
            Recipe::query()->create([
                'title' => 'Tarif '.$i,
                'slug' => 'tarif-'.$i,
                'excerpt' => 'Özet.',
                'description' => 'Açıklama.',
                'servings' => 4,
                'cuisine_id' => $mutfak->id,
                'status' => 'published',
                'verification_status' => 'verified',
                'published_at' => now(),
            ]);
        }

        $this->get(route('recipes.index'))->assertOk()->assertSee('value="kibris-mutfagi"', false);
        $this->get(route('recipes.index'))->assertOk()->assertSee('value="kibris-mutfagi"', false);

        File::deleteDirectory($dizin);
    }
}
