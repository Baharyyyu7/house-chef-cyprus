<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;
use App\Models\RecipeStep;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class RecipeStepService
{
    /** @param array<string, mixed> $attributes */
    public function create(Recipe $recipe, array $attributes): RecipeStep
    {
        return DB::transaction(function () use ($recipe, $attributes): RecipeStep {
            $validated = $this->validate($attributes);
            $validated['position'] = $this->nextPosition($recipe);

            return $recipe->steps()->create($validated);
        });
    }

    /** @param array<string, mixed> $attributes */
    public function update(RecipeStep $step, array $attributes): RecipeStep
    {
        return DB::transaction(function () use ($step, $attributes): RecipeStep {
            $step->update($this->validate($attributes));

            return $step->refresh();
        });
    }

    public function delete(RecipeStep $step): void
    {
        DB::transaction(function () use ($step): void {
            $recipe = $step->recipe;
            $position = $step->position;
            $step->delete();
            $recipe->steps()->where('position', '>', $position)->decrement('position');
        });
    }

    /** @param list<int> $stepIds */
    public function reorder(Recipe $recipe, array $stepIds): void
    {
        DB::transaction(function () use ($recipe, $stepIds): void {
            $existing = $recipe->steps()->pluck('id')->all();
            sort($existing);
            $requested = $stepIds;
            sort($requested);

            if ($existing !== $requested) {
                throw new \InvalidArgumentException('Tarifin tüm adımları sıralamaya dahil edilmelidir.');
            }

            $recipe->steps()->increment('position', 1000);

            foreach ($stepIds as $index => $id) {
                RecipeStep::query()->whereKey($id)->update(['position' => $index + 1]);
            }
        });
    }

    /** @return Collection<int, RecipeStep> */
    public function timerSteps(Recipe $recipe): Collection
    {
        return $recipe->steps()->where('timer_required', true)->get();
    }

    /** @return Collection<int, RecipeStep> */
    public function optionalSteps(Recipe $recipe): Collection
    {
        return $recipe->steps()->where('is_optional', true)->get();
    }

    public function totalDurationSeconds(Recipe $recipe): int
    {
        return (int) $recipe->steps()->sum('duration_seconds');
    }

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    private function validate(array $attributes): array
    {
        return Validator::make($attributes, [
            'title' => ['required', 'string', 'max:255'],
            'instruction' => ['required', 'string'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'temperature_value' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'temperature_unit' => ['nullable', Rule::in(['celsius', 'fahrenheit'])],
            'timer_required' => ['boolean'],
            'is_optional' => ['boolean'],
            'equipment' => ['nullable', 'string', 'max:255'],
            'chef_tip' => ['nullable', 'string'],
            'safety_note' => ['nullable', 'string'],
            'animation_key' => ['nullable', 'string', 'max:100'],
            'completion_hint' => ['nullable', 'string', 'max:255'],
        ])->validate();
    }

    private function nextPosition(Recipe $recipe): int
    {
        return ((int) $recipe->steps()->max('position')) + 1;
    }
}
