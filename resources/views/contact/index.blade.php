<x-layouts.site :title="__('contact.title')" :meta-description="__('contact.meta_description')" :canonical-url="route('contact.index')">
    <section class="hc-container py-10 sm:py-16">
        <div class="mx-auto max-w-2xl text-center">
            <p class="hc-kicker">{{ __('contact.write_us') }}</p>
            <h1 class="hc-display-lg mt-3">{{ __('contact.heading') }}</h1>
            <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">
                {{ __('contact.lead') }}
            </p>
        </div>

        @if(session('status'))
            <div class="hc-v3-alert hc-v3-alert--success mx-auto mt-8 max-w-2xl" role="status">
                <span aria-hidden="true">✓</span>
                <div><strong>{{ __('contact.received_title') }}</strong><p>{{ session('status') }}</p></div>
            </div>
        @endif

            <form class="hc-v3-card p-6 sm:p-8" method="post" action="{{ route('legal.center.contact.store') }}" toolname="contact_housechef" tooldescription="Submit an inquiry or editorial feedback to HouseChef Cyprus editorial team">
                @csrf
                <input type="hidden" name="source_page" value="{{ route('contact.index') }}">
                
                {{-- Honeypot --}}
                <div class="hidden" aria-hidden="true">
                    <label>{{ __('form.website') }}<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="hc-v3-field-label" for="first_name">{{ __('form.first_name') }}</label>
                        <input id="first_name" class="hc-v3-input" type="text" name="first_name" value="{{ old('first_name') }}" required maxlength="60">
                        @error('first_name')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="hc-v3-field-label" for="last_name">{{ __('form.last_name') }}</label>
                        <input id="last_name" class="hc-v3-input" type="text" name="last_name" value="{{ old('last_name') }}" required maxlength="60">
                        @error('last_name')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="hc-v3-field-label" for="email">{{ __('form.email_address') }}</label>
                        <input id="email" class="hc-v3-input" type="email" name="email" value="{{ old('email') }}" required>
                        @error('email')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="hc-v3-field-label" for="phone">{{ __('form.phone') }} <span class="font-normal" style="color: var(--hc-text-muted)">{{ __('form.optional') }}</span></label>
                        <input id="phone" class="hc-v3-input" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel">
                    </div>
                </div>

                <div class="mt-5">
                    <label class="hc-v3-field-label" for="subject">{{ __('form.subject') }}</label>
                    <select id="subject" class="hc-v3-input" name="subject" required>
                        @foreach(array_keys(config('contact-requests.subjects')) as $anahtar)
                            <option value="{{ $anahtar }}" @selected(old('subject', request()->query('subject')) === $anahtar)>{{ __('contact.subject.'.$anahtar) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-5">
                    <label class="hc-v3-field-label" for="message">{{ __('form.your_message') }}</label>
                    <textarea id="message" class="hc-v3-input min-h-36" name="message" required minlength="20" maxlength="4000" placeholder="{{ __('form.message_placeholder') }}">{{ old('message') }}</textarea>
                    @error('message')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                </div>

                <label class="mt-6 flex items-start gap-3 cursor-pointer">
                    <input class="mt-1 h-4 w-4 shrink-0 rounded border-stone-300 text-orange-600 focus:ring-orange-500" type="checkbox" name="privacy_consent" value="1" required>
                    <span class="hc-body-sm text-xs leading-relaxed" style="color: var(--hc-text-secondary)">
                        {{ __('form.consent_before') }}
                        <a class="font-bold text-orange-700 underline underline-offset-2 dark:text-orange-400" href="{{ route('policy.gizlilik') }}">{{ __('legal.privacy_title') }}</a>
                        {{ __('form.consent_after') }}
                    </span>
                </label>
                @error('privacy_consent')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

                @if(config('contact-requests.captcha.enabled') && config('contact-requests.captcha.site_key'))
                    <div class="cf-turnstile mt-5" data-sitekey="{{ config('contact-requests.captcha.site_key') }}" data-response-field-name="captcha_token"></div>
                    @once<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endonce
                @endif
                @error('captcha_token')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <button class="hc-v3-btn hc-v3-btn--primary px-8" type="submit">{{ __('form.send') }}</button>
                    <span class="text-xs" style="color: var(--hc-text-muted)">{{ __('form.reference_note') }}</span>
                </div>
            </form>

            <aside class="space-y-5">
                <div class="hc-v3-card p-6">
                    <h2 class="hc-heading-3">{{ __('contact.commercial_title') }}</h2>
                    <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">{{ __('contact.commercial_text') }}</p>
                    <div class="mt-4 space-y-3">
                        @foreach(config('contact-requests.commercial_subjects') as $subject)
                            <a class="block text-sm font-bold hover:underline" href="{{ route('contact.index', ['subject' => $subject]) }}#subject">{{ __('contact.subject.'.$subject) }}</a>
                        @endforeach
                    </div>
                    <p class="hc-body-sm mt-4" style="color: var(--hc-text-muted)">{{ __('contact.no_payment') }}</p>
                </div>
                <div class="hc-v3-card p-6">
                    <p class="hc-overline" style="color: var(--hc-text-muted)">{{ __('contact.direct_email') }}</p>
                    <a class="hc-heading-3 mt-2 block break-all font-extrabold text-orange-700 dark:text-orange-400" href="mailto:info@housechefcyprus.com">info@housechefcyprus.com</a>
                    <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">{{ __('contact.every_request') }}</p>
                </div>

                <div class="hc-v3-card p-6">
                    <p class="hc-overline" style="color: var(--hc-text-muted)">{{ __('contact.quick_links') }}</p>
                    <div class="mt-4 space-y-3">
                        @foreach ([
                            ['information.show', 'sss', __('contact.faq')],
                            ['information.show', 'tarif-dogrulama-politikasi', __('contact.verification_policy')],
                            ['information.show', 'nasil-calisir', __('contact.how_it_works')],
                        ] as [$rota, $sayfa, $etiket])
                            @if (Route::has($rota))
                                <a class="flex items-center justify-between text-sm font-bold text-orange-700 dark:text-orange-400 hover:underline" href="{{ route($rota, $sayfa) }}">
                                    <span>{{ $etiket }}</span>
                                    <span aria-hidden="true">→</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="hc-v3-card p-6">
                    <p class="hc-overline" style="color: var(--hc-text-muted)">{{ __('contact.copyright_privacy') }}</p>
                    <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">
                        {{ __('contact.copyright_privacy_text') }}
                    </p>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.site>
