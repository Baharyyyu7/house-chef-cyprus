<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CookingSession;
use App\Models\CookingTimer;
use App\Models\RecipeStep;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class CookingTimerService
{
    public function create(CookingSession $session, RecipeStep $step, string $label, int $durationSeconds): CookingTimer
    {
        if ($durationSeconds <= 0) {
            throw new \InvalidArgumentException('Zamanlayıcı süresi sıfırdan büyük olmalıdır.');
        }

        return DB::transaction(fn (): CookingTimer => $session->timers()->create([
            'recipe_step_id' => $step->id,
            'label' => $label,
            'duration_seconds' => $durationSeconds,
            'remaining_seconds' => $durationSeconds,
            'status' => 'ready',
        ]));
    }

    public function start(CookingTimer $timer): CookingTimer
    {
        $timer->update(['status' => 'running', 'started_at' => now(), 'paused_at' => null]);

        return $timer->refresh();
    }

    public function pause(CookingTimer $timer): CookingTimer
    {
        $timer->update(['remaining_seconds' => $this->remainingSeconds($timer), 'status' => 'paused', 'paused_at' => now()]);

        return $timer->refresh();
    }

    public function resume(CookingTimer $timer): CookingTimer
    {
        $timer->update(['status' => 'running', 'started_at' => now(), 'paused_at' => null]);

        return $timer->refresh();
    }

    public function complete(CookingTimer $timer): CookingTimer
    {
        $timer->update(['remaining_seconds' => 0, 'status' => 'completed', 'completed_at' => now()]);

        return $timer->refresh();
    }

    public function remainingSeconds(CookingTimer $timer): int
    {
        if ($timer->status !== 'running' || $timer->started_at === null) {
            return max(0, (int) ($timer->remaining_seconds ?? $timer->duration_seconds));
        }

        $elapsed = Carbon::parse($timer->started_at)->diffInSeconds(now());

        return max(0, (int) ((int) ($timer->remaining_seconds ?? $timer->duration_seconds) - $elapsed));
    }
}
