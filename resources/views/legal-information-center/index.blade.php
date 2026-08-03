<x-layouts.site :title="$title.' · House Chef Cyprus'" :meta-description="$introduction" :canonical-url="route('legal.center')" :structured-data="$schema">
    @php
        $navigationSections = array_merge($sections, [[
            'id' => 'iletisim-ve-geri-bildirim',
            'title' => 'İletişim ve Geri Bildirim',
        ]]);
    @endphp
    <section class="hc-container py-10 sm:py-14" data-legal-center>
        <div class="mx-auto max-w-5xl">
            <nav aria-label="Sayfa yolu" class="text-sm text-muted"><a class="hover:text-orange-600" href="{{ route('home') }}">Ana sayfa</a><span class="mx-2" aria-hidden="true">/</span><span aria-current="page">Yasal Bilgilendirme Merkezi</span></nav>
            <div class="mt-7 rounded-[2rem] border p-6 shadow-sm sm:p-10" style="border-color:var(--hc-border); background:linear-gradient(135deg, var(--hc-surface-raised), var(--hc-surface-base));">
                <x-brand-logo size="lg" context="legal" />
                <p class="hc-kicker mt-8">Yasal Bilgilendirme Merkezi</p>
                <h1 class="hc-title mt-3 max-w-4xl text-balance">{{ $title }}</h1>
                <p class="mt-5 text-sm font-semibold text-muted">{{ $updatedAt }}</p>
                <p class="mt-6 max-w-4xl text-base leading-8 text-muted sm:text-lg">{{ $introduction }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <button type="button" class="hc-btn hc-btn-primary" data-legal-print>Yazdır</button>
                    <a href="#kullanim-kosullari" class="hc-btn hc-btn-outline">Bölümleri incele</a>
                </div>
            </div>
        </div>

        <div class="mx-auto mt-8 grid max-w-7xl gap-8 lg:grid-cols-[17rem_minmax(0,1fr)]">
            <aside class="lg:sticky lg:top-28 lg:self-start" aria-label="Yasal bölüm menüsü">
                <div class="hc-v3-card p-4">
                    <label class="block text-sm font-extrabold" for="legal-search">Sayfada ara</label>
                    <input id="legal-search" type="search" class="hc-input mt-3" placeholder="Bölüm veya metin ara" autocomplete="off" data-legal-search>
                    <p class="mt-2 text-xs text-muted" data-legal-search-status aria-live="polite"></p>
                    <label class="mt-5 block text-sm font-extrabold lg:hidden" for="legal-section-select">Bölüm seçin</label>
                    <select id="legal-section-select" class="hc-input mt-2 w-full lg:hidden" data-legal-section-select>
                        @foreach ($navigationSections as $section)
                            <option value="{{ $section['id'] }}">{{ $section['title'] }}</option>
                        @endforeach
                    </select>
                    <nav class="mt-5 hidden max-h-[calc(100vh-16rem)] gap-1 overflow-y-auto pr-1 lg:grid" aria-label="Yasal bölüm bağlantıları">
                        @foreach ($navigationSections as $section)
                            <a href="#{{ $section['id'] }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-muted transition hover:bg-orange-50 hover:text-orange-700 dark:hover:bg-orange-500/10 dark:hover:text-orange-200" data-legal-nav-link data-section-id="{{ $section['id'] }}">{{ $section['title'] }}</a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            <div class="min-w-0 space-y-5">
                @foreach ($sections as $section)
                    <article id="{{ $section['id'] }}" class="hc-v3-card scroll-mt-28 p-6 sm:p-8" data-legal-section tabindex="-1">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <h2 class="font-display text-2xl font-semibold tracking-tight sm:text-3xl">{{ $section['title'] }}</h2>
                            <button type="button" class="hc-btn hc-btn-outline !min-h-10 !rounded-xl px-3 py-2 text-sm" data-copy-section data-section-id="{{ $section['id'] }}" aria-label="{{ $section['title'] }} bağlantısını kopyala">Bağlantıyı kopyala</button>
                        </div>
                        <div class="mt-6 space-y-5 text-[0.98rem] leading-8 text-muted sm:text-base">
                            @foreach ($section['paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </article>
                @endforeach
                <article id="iletisim-ve-geri-bildirim" class="hc-v3-card scroll-mt-28 p-6 sm:p-8" data-legal-section tabindex="-1">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div><p class="hc-kicker">İletişim</p><h2 class="mt-2 font-display text-2xl font-semibold tracking-tight sm:text-3xl">İletişim ve Geri Bildirim</h2></div>
                        <button type="button" class="hc-btn hc-btn-outline !min-h-10 !rounded-xl px-3 py-2 text-sm" data-copy-section data-section-id="iletisim-ve-geri-bildirim" aria-label="İletişim ve Geri Bildirim bağlantısını kopyala">Bağlantıyı kopyala</button>
                    </div>
                    <p class="mt-4 max-w-3xl leading-7 text-muted">Geri bildiriminizi, içerik hatasını veya destek talebinizi üyelik oluşturmadan iletebilirsiniz. Bilgiler yalnız talebinizi değerlendirmek için kullanılır.</p>
                    @if (session('status'))<div class="hc-alert hc-alert-success mt-6" role="status">{{ session('status') }}</div>@endif
                    <form class="mt-7 grid gap-5 sm:grid-cols-2" method="post" action="{{ route('legal.center.contact.store') }}" novalidate>
                        @csrf
                        <input type="hidden" name="source_page" value="{{ url()->current() }}">
                        <div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true"><label for="contact-website">Web sitesi</label><input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                        <label><span class="hc-form-label">Ad</span><input class="hc-form-control mt-1" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" aria-describedby="contact-first-name-error">@error('first_name')<span id="contact-first-name-error" class="hc-form-error">{{ $message }}</span>@enderror</label>
                        <label><span class="hc-form-label">Soyad</span><input class="hc-form-control mt-1" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" aria-describedby="contact-last-name-error">@error('last_name')<span id="contact-last-name-error" class="hc-form-error">{{ $message }}</span>@enderror</label>
                        <label><span class="hc-form-label">E-posta</span><input class="hc-form-control mt-1" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">@error('email')<span class="hc-form-error">{{ $message }}</span>@enderror</label>
                        <label><span class="hc-form-label">Telefon <span class="font-normal text-muted">(isteğe bağlı)</span></span><input class="hc-form-control mt-1" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel">@error('phone')<span class="hc-form-error">{{ $message }}</span>@enderror</label>
                        <label class="sm:col-span-2"><span class="hc-form-label">Konu</span><select class="hc-form-control mt-1" name="subject" required>@foreach(config('contact-requests.subjects') as $value => $label)<option value="{{ $value }}" @selected(old('subject') === $value)>{{ $label }}</option>@endforeach</select>@error('subject')<span class="hc-form-error">{{ $message }}</span>@enderror</label>
                        <label class="sm:col-span-2"><span class="hc-form-label">Mesaj</span><textarea class="hc-form-control mt-1 min-h-40" name="message" required minlength="10" maxlength="5000">{{ old('message') }}</textarea>@error('message')<span class="hc-form-error">{{ $message }}</span>@enderror</label>
                        @if(config('contact-requests.captcha.enabled') && config('contact-requests.captcha.site_key'))
                            <div class="sm:col-span-2"><div class="cf-turnstile" data-sitekey="{{ config('contact-requests.captcha.site_key') }}" data-response-field-name="captcha_token"></div>@error('captcha_token')<span class="hc-form-error">{{ $message }}</span>@enderror</div>
                            @once<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endonce
                        @endif
                        <label class="sm:col-span-2 flex items-start gap-3 text-sm leading-6"><input class="mt-1 rounded border-stone-300 text-orange-600 focus:ring-orange-500" type="checkbox" name="privacy_consent" value="1" @checked(old('privacy_consent')) required><span>Talebimin değerlendirilmesi için kişisel verilerimin <a class="font-bold text-orange-700 underline" href="#gizlilik-ve-kisisel-veriler">Gizlilik ve Kişisel Veriler</a> bölümünde açıklandığı şekilde işlenmesini kabul ediyorum.</span></label>
                        @error('privacy_consent')<p class="sm:col-span-2 hc-form-error">{{ $message }}</p>@enderror
                        <div class="sm:col-span-2 flex flex-wrap items-center gap-4"><button class="hc-btn hc-btn-primary" type="submit">Mesajı gönder</button><p class="text-sm text-muted">Spam koruması uygulanır. Talep numaranız gönderimden sonra gösterilir.</p></div>
                    </form>
                </article>
                <p class="sr-only" data-legal-copy-status aria-live="polite"></p>
            </div>
        </div>
    </section>
</x-layouts.site>
