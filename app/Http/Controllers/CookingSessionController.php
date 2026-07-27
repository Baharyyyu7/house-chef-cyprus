<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CookingSession;
use App\Models\CookingTimer;
use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Services\CookingSessionService;
use App\Services\CookingTimerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CookingSessionController extends Controller
{
    public function start(Request $request, Recipe $recipe, CookingSessionService $sessions): RedirectResponse
    {
        abort_unless($recipe->status === 'published' || $request->user()?->isAdmin(), 404);
        $session = $sessions->startOrResume($request->user(), $recipe, max(1, (int) $request->integer('servings', $recipe->servings)));

        return redirect()->route('cooking-sessions.show', $session);
    }

    public function show(Request $request, CookingSession $cookingSession, CookingSessionService $sessions, CookingTimerService $timers): View
    {
        $this->authorizeOwner($request, $cookingSession);
        $cookingSession->load(['recipe.steps', 'completedSteps', 'timers.step', 'currentStep']);

        return view('cooking-sessions.show', ['session' => $cookingSession, 'progress' => $sessions->progress($cookingSession), 'timerService' => $timers]);
    }

    public function completeStep(Request $request, CookingSession $cookingSession, RecipeStep $step, CookingSessionService $sessions): RedirectResponse
    {
        $this->authorizeOwner($request, $cookingSession);
        $sessions->completeStep($cookingSession, $step);

        return back();
    }

    public function undoStep(Request $request, CookingSession $cookingSession, RecipeStep $step, CookingSessionService $sessions): RedirectResponse
    {
        $this->authorizeOwner($request, $cookingSession);
        $sessions->undoStep($cookingSession, $step);

        return back();
    }

    public function pause(Request $request, CookingSession $cookingSession, CookingSessionService $sessions): RedirectResponse
    {
        $this->authorizeOwner($request, $cookingSession);
        $sessions->pause($cookingSession);

        return back();
    }

    public function resume(Request $request, CookingSession $cookingSession, CookingSessionService $sessions): RedirectResponse
    {
        $this->authorizeOwner($request, $cookingSession);
        $sessions->resume($cookingSession);

        return back();
    }

    public function complete(Request $request, CookingSession $cookingSession, CookingSessionService $sessions): RedirectResponse
    {
        $this->authorizeOwner($request, $cookingSession);
        $sessions->complete($cookingSession);

        return back()->with('status', 'Tarif tamamlandı. Ellerinize sağlık!');
    }

    public function createTimer(Request $request, CookingSession $cookingSession, RecipeStep $step, CookingTimerService $timers): RedirectResponse
    {
        $this->authorizeOwner($request, $cookingSession);
        abort_unless($step->recipe_id === $cookingSession->recipe_id, 404);
        $duration = (int) $request->integer('duration_seconds', $step->duration_seconds ?? 0);
        $timers->start($timers->create($cookingSession, $step, $step->title, $duration));

        return back();
    }

    public function pauseTimer(Request $request, CookingSession $cookingSession, CookingTimer $timer, CookingTimerService $timers): RedirectResponse
    {
        $this->authorizeTimer($request, $cookingSession, $timer);
        $timers->pause($timer);

        return back();
    }

    public function resumeTimer(Request $request, CookingSession $cookingSession, CookingTimer $timer, CookingTimerService $timers): RedirectResponse
    {
        $this->authorizeTimer($request, $cookingSession, $timer);
        $timers->resume($timer);

        return back();
    }

    private function authorizeOwner(Request $request, CookingSession $session): void
    {
        abort_unless($session->user_id === $request->user()->id, 403);
    }

    private function authorizeTimer(Request $request, CookingSession $session, CookingTimer $timer): void
    {
        $this->authorizeOwner($request, $session);
        abort_unless($timer->cooking_session_id === $session->id, 404);
    }
}
