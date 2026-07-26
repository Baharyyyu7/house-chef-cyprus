<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CookingSession;
use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CookingSessionService
{
    public function startOrResume(User $user, Recipe $recipe, int $servings): CookingSession
    {
        return DB::transaction(function () use ($user, $recipe, $servings): CookingSession {
            $session = CookingSession::query()
                ->where('user_id', $user->id)
                ->where('recipe_id', $recipe->id)
                ->whereIn('status', ['in_progress', 'paused'])
                ->latest('last_activity_at')
                ->first();

            if ($session !== null) {
                return $session;
            }

            return CookingSession::create([
                'user_id' => $user->id,
                'recipe_id' => $recipe->id,
                'current_step_id' => $recipe->steps()->orderBy('position')->value('id'),
                'servings_snapshot' => max(1, $servings),
                'status' => 'in_progress',
                'started_at' => now(),
                'last_activity_at' => now(),
            ]);
        });
    }

    public function completeStep(CookingSession $session, RecipeStep $step): CookingSession
    {
        return DB::transaction(function () use ($session, $step): CookingSession {
            $this->ensureStepBelongsToSessionRecipe($session, $step);
            $session->completedSteps()->syncWithoutDetaching([$step->id => ['completed_at' => now()]]);
            $nextStep = $session->recipe->steps()->where('position', '>', $step->position)->first();
            $session->update(['current_step_id' => $nextStep?->id, 'last_activity_at' => now()]);

            return $session->refresh();
        });
    }

    public function undoStep(CookingSession $session, RecipeStep $step): CookingSession
    {
        return DB::transaction(function () use ($session, $step): CookingSession {
            $this->ensureStepBelongsToSessionRecipe($session, $step);
            $session->completedSteps()->detach($step->id);
            $session->update(['current_step_id' => $step->id, 'last_activity_at' => now()]);

            return $session->refresh();
        });
    }

    public function pause(CookingSession $session): CookingSession
    {
        $session->update(['status' => 'paused', 'last_activity_at' => now()]);

        return $session->refresh();
    }

    public function resume(CookingSession $session): CookingSession
    {
        $session->update(['status' => 'in_progress', 'last_activity_at' => now()]);

        return $session->refresh();
    }

    public function complete(CookingSession $session): CookingSession
    {
        $session->update(['status' => 'completed', 'completed_at' => now(), 'last_activity_at' => now()]);

        return $session->refresh();
    }

    public function progress(CookingSession $session): int
    {
        $required = $session->recipe->steps()->where('is_optional', false)->count();
        if ($required === 0) {
            return 0;
        }

        $completed = $session->completedSteps()->where('is_optional', false)->count();

        return (int) round(($completed / $required) * 100);
    }

    private function ensureStepBelongsToSessionRecipe(CookingSession $session, RecipeStep $step): void
    {
        if ($step->recipe_id !== $session->recipe_id) {
            throw new \InvalidArgumentException('Adım bu oturuma ait değildir.');
        }
    }
}
