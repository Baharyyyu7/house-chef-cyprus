<?php

namespace App\Services\Importing;

use App\DTOs\Importing\NormalizedRecipeData;
use App\Models\DataSource;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

final class RecipeImportService
{
    public function import(DataSource $source, NormalizedRecipeData $data): Recipe
    {
        return DB::transaction(function () use ($source, $data) {
            return Recipe::query()->updateOrCreate(['data_source_id' => $source->id, 'external_id' => $data->externalId], ['title' => $data->title, 'slug' => $data->slug, 'excerpt' => $data->payload['excerpt'] ?? null, 'content_origin' => 'imported', 'original_hash' => $data->hash(), 'first_imported_at' => now(), 'last_verified_at' => now(), 'status' => 'draft']);
        });
    }
}
