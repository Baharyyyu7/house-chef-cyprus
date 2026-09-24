<?php

use App\Models\SystemEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $throttleKey = 'password-reset:'.hash('sha256', strtolower(trim($this->email)).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('email', __('ux.wait'));

            return;
        }

        RateLimiter::hit($throttleKey, 3600);

        try {
            Password::sendResetLink($this->only('email'));
        } catch (\Throwable $exception) {
            // Do not disclose mail infrastructure failures to guests or turn them
            // into a 500 response. Operations can investigate the safe event.
            Log::error('Password reset delivery request failed.', [
                'exception' => class_basename($exception),
            ]);

            try {
                SystemEvent::query()->create([
                    'event_type' => 'password_reset_delivery_failed',
                    'severity' => 'error',
                    'message' => 'Parola sıfırlama e-postası teslim edilemedi.',
                    'metadata' => ['exception' => class_basename($exception)],
                ]);
            } catch (\Throwable) {
                // Logging must never make the public recovery flow fail.
            }
        }

        $this->reset('email');
        session()->flash('status', __('ux.reset_sent'));
    }
}; ?>

<div>
    <p class="hc-kicker">{{ __('ux.recovery') }}</p>
    <h1 class="mt-2 hc-display-lg">{{ __('ux.reset_title') }}</h1>
    <p class="mt-2 text-sm leading-6 text-muted">{{ __('ux.reset_intro') }}</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="mt-7">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('ux.email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-6 flex items-center justify-between gap-4">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-gold-700 hover:text-gold-600">{{ __('ux.back_login') }}</a>
            <x-primary-button>
                {{ __('ux.send_reset') }}
            </x-primary-button>
        </div>
    </form>
</div>
