<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();
        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $throttleKey = 'verification-resend|'.$user->getAuthIdentifier().'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            throw ValidationException::withMessages([
                'verification' => __('ux.verification_throttle'),
            ]);
        }

        // Count attempted sends before delivery so a broken mail transport cannot
        // be used to bypass a bounded resend policy.
        RateLimiter::hit($throttleKey, 3600);
        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/');
    }
}; ?>

<div>
    <p class="hc-kicker">{{ __('ux.last_step') }}</p>
    <h1 class="mt-2 hc-display-lg">{{ __('ux.verify_email') }}</h1>
    <p class="mt-2 text-sm leading-6 text-muted">{{ __('ux.verify_email_intro') }}</p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ __('ux.verification_sent') }}
        </div>
    @endif

    <div class="mt-7 flex items-center justify-between gap-4">
        <x-primary-button wire:click="sendVerification">
            {{ __('ux.resend') }}
        </x-primary-button>

        <button wire:click="logout" type="button" class="rounded-md text-sm font-semibold text-gold-700 underline outline-none hover:text-gold-600 focus-visible:ring-2 focus-visible:ring-gold-500">
            {{ __('ux.logout') }}
        </button>
    </div>
    <x-input-error :messages="$errors->get('verification')" class="mt-4" />
</div>
