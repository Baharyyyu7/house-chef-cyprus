<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false));
    }
}; ?>

<div>
    <p class="hc-kicker">Tekrar hoş geldiniz</p>
    <h1 class="mt-2 font-display text-3xl font-semibold text-forest-950">Giriş yapın</h1>
    <p class="mt-2 text-sm leading-6 text-muted">Tariflerinizi, favorilerinizi ve malzeme dolabınızı kaldığınız yerden yönetin.</p>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="mt-7">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-posta adresi" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Şifre" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-muted">Beni hatırla</span>
            </label>
        </div>

        <div class="mt-6 flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-gold-700 hover:text-gold-600" href="{{ route('password.request') }}">
                    Şifremi unuttum
                </a>
            @endif

            <x-primary-button>
                Giriş yap
            </x-primary-button>
        </div>
        <p class="mt-6 text-center text-sm text-muted">Hesabınız yok mu? <a class="font-bold text-gold-700 hover:text-gold-600" href="{{ route('register') }}">Üye olun</a></p>
    </form>
</div>
