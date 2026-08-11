<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false));
    }
}; ?>

<div>
    <p class="hc-kicker">Güvenli işlem</p>
    <h1 class="mt-2 hc-display-lg">Parolanızı doğrulayın</h1>
    <p class="mt-2 text-sm leading-6 text-[var(--hc-text-secondary)]">Bu hassas alana devam etmek için mevcut parolanızı tekrar girin.</p>

    <form wire:submit="confirmPassword" class="mt-7" x-data="{ passwordVisible: false }">
        <!-- Password -->
        <div>
            <x-input-label for="password" value="Mevcut parola" />
            <div class="relative mt-1"><x-text-input wire:model="password" id="password" class="block w-full pr-20" x-bind:type="passwordVisible ? 'text' : 'password'" name="password" required autocomplete="current-password" /><button type="button" class="absolute inset-y-0 right-2 my-1 rounded-md px-3 text-xs font-semibold text-gold-700 outline-none transition hover:bg-gold-50 focus-visible:ring-2 focus-visible:ring-gold-500 dark:hover:bg-forest-800" @click="passwordVisible = !passwordVisible" x-text="passwordVisible ? 'Gizle' : 'Göster'"></button></div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end">
            <x-primary-button>
                Devam et
            </x-primary-button>
        </div>
    </form>
</div>
