<?php

use App\Events\MemberRegistered;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone = '';

    public string $country = '';

    public string $city_or_region = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $terms_and_privacy_acceptance = false;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $throttleKey = 'registration|'.request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => __('ux.registration_throttle'),
            ]);
        }
        RateLimiter::hit($throttleKey, 3600);

        $this->first_name = (string) preg_replace('/\s+/u', ' ', trim($this->first_name));
        $this->last_name = (string) preg_replace('/\s+/u', ' ', trim($this->last_name));
        $this->email = mb_strtolower(trim($this->email));
        $this->phone = (string) preg_replace('/\s+/', ' ', trim($this->phone));
        $this->country = (string) preg_replace('/\s+/u', ' ', trim($this->country));
        $this->city_or_region = (string) preg_replace('/\s+/u', ' ', trim($this->city_or_region));

        $validated = $this->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\pM][\pL\pM\s.\'’-]*$/u'],
            'last_name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\pM][\pL\pM\s.\'’-]*$/u'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^\+?[0-9\s().-]{7,32}$/'],
            'country' => ['nullable', 'string', 'max:120', 'not_regex:/[<>]/'],
            'city_or_region' => ['nullable', 'string', 'max:120', 'not_regex:/[<>]/'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'terms_and_privacy_acceptance' => ['accepted'],
        ]);

        $user = DB::transaction(fn (): User => User::create([
            'name' => $validated['first_name'].' '.$validated['last_name'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'country' => $validated['country'] ?: null,
            'city_or_region' => $validated['city_or_region'] ?: null,
            'preferred_language' => app()->getLocale(),
            'registration_source' => null,
            'terms_accepted_at' => now(),
            'password' => Hash::make($validated['password']),
        ]));

        try {
            event(new Registered($user));
        } catch (Throwable $exception) {
            Log::error('Post-registration event dispatch failed after account commit.', [
                'code' => 'REGISTRATION_POST_COMMIT_EVENT_FAILED',
                'member_public_id' => $user->public_id,
                'exception_class' => $exception::class,
            ]);
        }

        try {
            event(new MemberRegistered($user));
        } catch (Throwable $exception) {
            Log::error('Internal registration notification dispatch failed after account commit.', [
                'code' => 'REGISTRATION_NOTIFICATION_DISPATCH_FAILED',
                'member_public_id' => $user->public_id,
                'exception_class' => $exception::class,
            ]);
        }

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false));
    }
}; ?>

<div>
    <p class="hc-kicker">{{ __('ux.family') }}</p>
    <h1 class="mt-2 hc-display-lg">{{ __('ux.register') }}</h1>
    <p class="mt-2 text-sm leading-6 text-muted">{{ __('ux.register_intro') }}</p>

    {{-- Üyelikle açılan dolap, koleksiyon ve pişirme akışı tek bir cümlenin
         içinde kalıyordu; ziyaretçi neye kaydolduğunu bilmeden form dolduruyordu.
         Bunlar sitenin zaten çalışan özellikleri, vaat değil. --}}
    <ul class="mt-6 grid gap-3 sm:grid-cols-2">
        @foreach ([
            ['tag', __('ux.ingredient_pantry'), __('ux.pantry_benefit')],
            ['heart', __('ux.favorites_collections'), __('ux.favorites_benefit')],
            ['book', __('ux.cooking_mode'), __('ux.cooking_benefit')],
            ['sliders', __('ux.recommendations'), __('ux.recommendations_benefit')],
        ] as [$ikon, $baslik, $aciklama])
            <li class="flex gap-3 rounded-2xl border p-3" style="border-color: var(--hc-line)">
                <span class="mt-0.5 shrink-0 text-orange-600"><x-ui.icon :name="$ikon" size="18" /></span>
                <span>
                    <strong class="block text-sm font-bold">{{ $baslik }}</strong>
                    <span class="mt-0.5 block text-xs leading-5 text-muted">{{ $aciklama }}</span>
                </span>
            </li>
        @endforeach
    </ul>

    <x-social-login :divider-after="true" class="mt-6" />

    <form wire:submit="register" class="mt-6" x-data="{ passwordVisible: false, confirmationVisible: false }" toolname="create_account" tooldescription="Create a new personal HouseChef Cyprus account">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="first_name" :value="__('ux.first_name')" />
                <x-text-input wire:model="first_name" id="first_name" class="block mt-1 w-full" type="text" name="first_name" required autofocus autocomplete="given-name" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="last_name" :value="__('ux.last_name')" />
                <x-text-input wire:model="last_name" id="last_name" class="block mt-1 w-full" type="text" name="last_name" required autocomplete="family-name" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('ux.email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <details class="mt-4 rounded-xl border border-forest-100 p-4 dark:border-forest-700">
            <summary class="cursor-pointer text-sm font-semibold text-forest-900 dark:text-forest-50">{{ __('ux.optional_contact') }}</summary>
            <p class="mt-2 text-xs leading-5 text-muted">{{ __('ux.optional_intro') }}</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="phone" :value="__('ux.phone')" />
                    <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full" type="tel" name="phone" autocomplete="tel" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="country" :value="__('ux.country')" />
                    <x-text-input wire:model="country" id="country" class="block mt-1 w-full" type="text" name="country" autocomplete="country-name" />
                    <x-input-error :messages="$errors->get('country')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="city_or_region" :value="__('ux.city')" />
                    <x-text-input wire:model="city_or_region" id="city_or_region" class="block mt-1 w-full" type="text" name="city_or_region" autocomplete="address-level2" />
                    <x-input-error :messages="$errors->get('city_or_region')" class="mt-2" />
                </div>
            </div>
        </details>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('ux.password')" />

            <div class="relative mt-1">
                <x-text-input wire:model="password" id="password" class="block w-full pr-20"
                                x-bind:type="passwordVisible ? 'text' : 'password'"
                                name="password"
                                required autocomplete="new-password" />
                <button type="button" class="absolute inset-y-0 right-2 my-1 rounded-md px-3 text-xs font-semibold text-gold-700 outline-none transition hover:bg-gold-50 focus-visible:ring-2 focus-visible:ring-gold-500 dark:hover:bg-forest-800" @click="passwordVisible = !passwordVisible" x-text="passwordVisible ? @js(__('ux.hide')) : @js(__('ux.show'))"></button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('ux.confirm_password')" />

            <div class="relative mt-1">
                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block w-full pr-20"
                                x-bind:type="confirmationVisible ? 'text' : 'password'"
                                name="password_confirmation" required autocomplete="new-password" />
                <button type="button" class="absolute inset-y-0 right-2 my-1 rounded-md px-3 text-xs font-semibold text-gold-700 outline-none transition hover:bg-gold-50 focus-visible:ring-2 focus-visible:ring-gold-500 dark:hover:bg-forest-800" @click="confirmationVisible = !confirmationVisible" x-text="confirmationVisible ? @js(__('ux.hide')) : @js(__('ux.show'))"></button>
            </div>

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-5">
            <label for="terms_and_privacy_acceptance" class="flex items-start gap-3 text-sm leading-6 text-muted">
                <input wire:model="terms_and_privacy_acceptance" id="terms_and_privacy_acceptance" type="checkbox" class="mt-1 rounded border-forest-300 text-gold-600 focus:ring-gold-500" required>
                <span><a class="font-semibold text-gold-700 underline" href="{{ route('legal.center') }}#kullanim-kosullari">{{ __('ux.terms') }}</a> {{ __('ux.and') }} <a class="font-semibold text-gold-700 underline" href="{{ route('legal.center') }}#gizlilik-ve-kisisel-veriler">{{ __('ux.privacy') }}</a>{{ __('ux.consent') }}</span>
            </label>
            <x-input-error :messages="$errors->get('terms_and_privacy_acceptance')" class="mt-2" />
        </div>

        <div class="mt-6 flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a class="text-sm font-semibold text-gold-700 hover:text-gold-600" href="{{ route('login') }}">
                {{ __('ux.already_member') }}
            </a>

            <x-primary-button>
                {{ __('ux.create_account') }}
            </x-primary-button>
        </div>
    </form>
</div>
