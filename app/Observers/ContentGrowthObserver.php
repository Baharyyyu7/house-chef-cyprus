<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\AuditContentGrowthJob;
use App\Models\BlogPost;
use App\Models\ContentTranslation;
use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Model;

final class ContentGrowthObserver
{
    public function saved(Model $model): void
    {
        if ($model instanceof ContentTranslation) {
            $model = $model->translatable;
        }
        if ($model instanceof RecipeStep) {
            $model = $model->recipe;
        }
        if ($model instanceof Ingredient) {
            Recipe::query()->whereHas('ingredients', fn ($query) => $query->where('ingredients.id', $model->getKey()))->select('recipes.id')->chunkById(100, function ($recipes): void {
                foreach ($recipes as $recipe) {
                    AuditContentGrowthJob::dispatch('recipe', (int) $recipe->id);
                }
            }, 'recipes.id', 'id');
            return;
        }
        if ($model instanceof Recipe || $model instanceof BlogPost) {
            AuditContentGrowthJob::dispatch($model instanceof Recipe ? 'recipe' : 'blog', (int) $model->getKey());
        }
    }

    public function deleted(Model $model): void
    {
        if ($model instanceof ContentTranslation || $model instanceof RecipeStep) {
            $this->saved($model);
        }
    }
}
