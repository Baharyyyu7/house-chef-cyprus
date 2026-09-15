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
    <p class="hc-kicker">{{ __('ux.welcome') }}</p>
    <h1 class="mt-2 hc-display-lg">{{ __('ux.login') }}</h1>
    <p class="mt-2 text-sm leading-6 text-muted">{{ __('ux.login_intro') }}</p>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <x-social-login :divider-after="true" class="mt-6" />

    <form wire:submit="login" class="mt-6" x-data="{ passwordVisible: false }">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('ux.email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('ux.password')" />
            <div class="relative mt-1">
                <x-text-input wire:model="form.password" id="password" class="block w-full pr-20"
                                x-bind:type="passwordVisible ? 'text' : 'password'"
                                name="password"
                                required autocomplete="current-password" />
                <button type="button" class="absolute inset-y-0 right-2 my-1 rounded-md px-3 text-xs font-semibold text-gold-700 outline-none transition hover:bg-gold-50 focus-visible:ring-2 focus-visible:ring-gold-500 dark:hover:bg-forest-800" @click="passwordVisible = !passwordVisible" x-text="passwordVisible ? @js(__('ux.hide')) : @js(__('ux.show'))"></button>
            </div>

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-muted">{{ __('ux.remember') }}</span>
            </label>
        </div>

        <div class="mt-6 flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-gold-700 hover:text-gold-600" href="{{ route('password.request') }}">
                    {{ __('ux.forgot') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('ux.login') }}
            </x-primary-button>
        </div>

        <p class="mt-6 text-center text-sm text-muted">{{ __('ux.no_account') }} <a class="font-bold text-gold-700 hover:text-gold-600" href="{{ route('register') }}">{{ __('ux.register') }}</a></p>
    </form>
</div>
