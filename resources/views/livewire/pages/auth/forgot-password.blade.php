<?php

use Illuminate\Support\Facades\Password;
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

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <p class="hc-kicker">Hesap erişimi</p>
    <h1 class="mt-2 font-display text-3xl font-semibold text-forest-950 dark:text-cream-50">Şifrenizi yenileyin</h1>
    <p class="mt-2 text-sm leading-6 text-muted">E-posta adresinizi girin. Hesabınız varsa parolanızı yenilemeniz için güvenli bir bağlantı gönderilecektir.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="mt-7">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-posta adresi" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-6 flex items-center justify-between gap-4">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-gold-700 hover:text-gold-600">Girişe dön</a>
            <x-primary-button>
                Sıfırlama bağlantısı gönder
            </x-primary-button>
        </div>
    </form>
</div>
