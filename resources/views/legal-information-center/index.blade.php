<x-layouts.site :title="$title.' · House Chef Cyprus'" :meta-description="$introduction" :canonical-url="route('legal.center')" :structured-data="$schema">
    <section class="bg-[#fbf7f1] py-8 dark:bg-[#11100e] sm:py-12" data-legal-center data-copied-status="{{ __('legal.copied_status') }}" data-copy-prompt="{{ __('legal.copy_prompt') }}">
        <article class="mx-auto w-full max-w-[860px] border-y px-5 py-9 shadow-[0_18px_60px_rgba(45,32,20,.06)] dark:border-stone-800 dark:bg-[#181715] sm:rounded-2xl sm:border sm:bg-white sm:px-10 sm:py-12 lg:px-14 lg:py-14" style="border-color:var(--hc-border)" data-legal-document>
            <header>
                <x-brand-logo size="lg" context="legal" />
                <p class="hc-kicker mt-8">{{ __('legal.kicker') }}</p>
                <h1 class="mt-3 max-w-3xl hc-display-xl text-[var(--hc-text-primary)]">{{ $title }}</h1>
                <p class="mt-5 text-sm font-semibold text-muted">{{ $updatedAt }}</p>
                <p class="mt-6 text-base leading-8 text-muted sm:text-[1.05rem]">{{ $introduction }}</p>
                <div class="mt-7 flex flex-wrap gap-3 print:hidden">
                    <button type="button" class="hc-v3-btn hc-v3-btn--primary" data-legal-print>{{ __('legal.print') }}</button>
                    <button type="button" class="hc-v3-btn hc-v3-btn--outline" data-copy-page>{{ __('legal.copy_link') }}</button>
                    <span class="self-center text-sm text-muted" data-legal-copy-status aria-live="polite"></span>
                </div>
            </header>

            <div class="mt-10 border-t pt-9 text-[1rem] leading-[1.85] text-warm dark:border-stone-800 dark:text-stone-300 sm:text-[1.06rem]" style="border-color:var(--hc-border-subtle)">
                {{-- Gizlilik, çerez ve kullanım koşulları kendi sayfalarında.
                     Merkez onları tekrarlamaz, gösterir. --}}
                <div class="mt-2 grid gap-3 print:hidden sm:grid-cols-3">
                    @foreach ($ayriSayfalar as $anahtar => $tanim)
                        <a class="hc-card-hover rounded-2xl border p-4" style="border-color: var(--hc-line); background: var(--hc-surface)" href="{{ route('policy.'.$anahtar) }}">
                            <span class="block font-bold">{{ $tanim['baslik'] }}</span>
                            <span class="hc-caption mt-1 block" style="color: var(--hc-text-secondary)">{{ $tanim['ozet'] }}</span>
                        </a>
                    @endforeach
                </div>

                @foreach ($sections as $section)
                    <div id="{{ $section['id'] }}" class="scroll-mt-28 [&+&]:mt-6" data-legal-section>
                        @foreach ($section['paragraphs'] as $paragraph)
                            <p @class(['mt-5' => !$loop->first])>@if($loop->first)<strong class="font-extrabold text-[var(--hc-text-primary)]">{{ $section['title'] }}.</strong> @endif{{ $paragraph }}</p>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <section id="iletisim-ve-geri-bildirim" class="mt-12 border-t pt-10 dark:border-stone-800" style="border-color:var(--hc-border-subtle)" aria-labelledby="contact-heading">
                <p class="hc-kicker">{{ __('legal.contact_kicker') }}</p>
                <h2 id="contact-heading" class="mt-2 hc-display-lg text-[var(--hc-text-primary)]">{{ __('legal.contact_heading') }}</h2>
                <p class="mt-4 max-w-3xl leading-7 text-muted">{{ __('legal.contact_intro') }}</p>
                @if (session('status'))<div class="hc-alert hc-alert-success mt-6" role="status">{{ session('status') }}</div>@endif
                <form class="mt-7 grid gap-5 rounded-xl border bg-[#fffaf5] p-5 dark:border-stone-700 dark:bg-[#201d19] sm:grid-cols-2 sm:p-6" style="border-color:var(--hc-border-subtle)" method="post" action="{{ route('legal.center.contact.store') }}" novalidate>
                    @csrf
                    <input type="hidden" name="source_page" value="{{ url()->current() }}">
                    <div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true"><label for="contact-website">{{ __('form.website') }}</label><input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                    <label><span class="hc-form-label">{{ __('form.first_name') }}</span><input class="hc-form-control mt-1" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" aria-describedby="contact-first-name-error">@error('first_name')<span id="contact-first-name-error" class="hc-form-error">{{ $message }}</span>@enderror</label>
                    <label><span class="hc-form-label">{{ __('form.last_name') }}</span><input class="hc-form-control mt-1" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" aria-describedby="contact-last-name-error">@error('last_name')<span id="contact-last-name-error" class="hc-form-error">{{ $message }}</span>@enderror</label>
                    <label><span class="hc-form-label">{{ __('form.email') }}</span><input class="hc-form-control mt-1" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">@error('email')<span class="hc-form-error">{{ $message }}</span>@enderror</label>
                    <label><span class="hc-form-label">{{ __('form.phone') }} <span class="font-normal text-muted">{{ __('form.optional') }}</span></span><input class="hc-form-control mt-1" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel">@error('phone')<span class="hc-form-error">{{ $message }}</span>@enderror</label>
                    <label class="sm:col-span-2"><span class="hc-form-label">{{ __('form.subject') }}</span><select class="hc-form-control mt-1" name="subject" required>@foreach(array_keys(config('contact-requests.subjects')) as $value)<option value="{{ $value }}" @selected(old('subject') === $value)>{{ __('contact.subject.'.$value) }}</option>@endforeach</select>@error('subject')<span class="hc-form-error">{{ $message }}</span>@enderror</label>
                    <label class="sm:col-span-2"><span class="hc-form-label">{{ __('form.message') }}</span><textarea class="hc-form-control mt-1 min-h-40" name="message" required minlength="10" maxlength="5000">{{ old('message') }}</textarea>@error('message')<span class="hc-form-error">{{ $message }}</span>@enderror</label>
                    @if(config('contact-requests.captcha.enabled') && config('contact-requests.captcha.site_key'))
                        <div class="sm:col-span-2"><div class="cf-turnstile" data-sitekey="{{ config('contact-requests.captcha.site_key') }}" data-response-field-name="captcha_token"></div>@error('captcha_token')<span class="hc-form-error">{{ $message }}</span>@enderror</div>
                        @once<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endonce
                    @endif
                    <label class="sm:col-span-2 flex items-start gap-3 text-sm leading-6"><input class="mt-1 rounded border-stone-300 text-orange-600 focus:ring-orange-500" type="checkbox" name="privacy_consent" value="1" @checked(old('privacy_consent')) required><span>{{ __('form.consent_before') }} <a class="font-bold text-orange-700 underline" href="{{ route('policy.gizlilik') }}">{{ __('legal.privacy_title') }}</a> {{ __('form.consent_after') }}</span></label>
                    @error('privacy_consent')<p class="sm:col-span-2 hc-form-error">{{ $message }}</p>@enderror
                    <div class="sm:col-span-2 flex flex-wrap items-center gap-4"><button class="hc-v3-btn hc-v3-btn--primary" type="submit">{{ __('form.send') }}</button><p class="text-sm text-muted">{{ __('form.spam_note') }}</p></div>
                </form>
            </section>

            <p class="mt-10 text-sm leading-7 text-muted print:block">House Chef Cyprus · https://housechefcyprus.com · info@housechefcyprus.com</p>
        </article>
    </section>
</x-layouts.site>
