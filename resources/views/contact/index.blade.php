<x-layouts.site title="İletişim — House Chef Cyprus" meta-description="House Chef Cyprus ile iletişime geçin: tarif hatası, telif bildirimi, iş birliği veya gizlilik talepleri.">
    <section class="hc-container py-10 sm:py-16">
        <div class="mx-auto max-w-2xl text-center">
            <p class="hc-kicker">Bize yazın</p>
            <h1 class="hc-display-lg mt-3">İletişim</h1>
            <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">
                Tarifte bir hata mı gördünüz, iş birliği mi öneriyorsunuz, yoksa verilerinizle ilgili bir talebiniz mi var? Hepsi buradan ulaşır.
            </p>
        </div>

        @if(session('status'))
            <div class="hc-v3-alert hc-v3-alert--success mx-auto mt-8 max-w-2xl" role="status">
                <span aria-hidden="true">✓</span>
                <div><strong>Mesajınız alındı!</strong><p>{{ session('status') }}</p></div>
            </div>
        @endif

        <div class="mx-auto mt-10 grid max-w-5xl gap-8 lg:grid-cols-[1fr_.62fr] lg:items-start">
            <form class="hc-v3-card p-6 sm:p-8" method="post" action="{{ route('legal.center.contact.store') }}">
                @csrf
                <input type="hidden" name="source_page" value="iletisim">
                
                {{-- Honeypot --}}
                <div class="hidden" aria-hidden="true">
                    <label>Web sitesi<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="hc-v3-field-label" for="first_name">Ad</label>
                        <input id="first_name" class="hc-v3-input" type="text" name="first_name" value="{{ old('first_name') }}" required maxlength="60">
                        @error('first_name')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="hc-v3-field-label" for="last_name">Soyad</label>
                        <input id="last_name" class="hc-v3-input" type="text" name="last_name" value="{{ old('last_name') }}" required maxlength="60">
                        @error('last_name')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="hc-v3-field-label" for="email">E-posta adresi</label>
                        <input id="email" class="hc-v3-input" type="email" name="email" value="{{ old('email') }}" required>
                        @error('email')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="hc-v3-field-label" for="phone">Telefon <span class="font-normal" style="color: var(--hc-text-muted)">(isteğe bağlı)</span></label>
                        <input id="phone" class="hc-v3-input" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel">
                    </div>
                </div>

                <div class="mt-5">
                    <label class="hc-v3-field-label" for="subject">Konu</label>
                    <select id="subject" class="hc-v3-input" name="subject" required>
                        @foreach(config('contact-requests.subjects') as $anahtar => $tanim)
                            <option value="{{ $anahtar }}" @selected(old('subject') === $anahtar)>{{ is_array($tanim) ? ($tanim['label'] ?? $anahtar) : $tanim }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-5">
                    <label class="hc-v3-field-label" for="message">Mesajınız</label>
                    <textarea id="message" class="hc-v3-input min-h-36" name="message" required minlength="20" maxlength="4000" placeholder="Hangi tarif, hangi sayfa veya hangi konu olduğunu yazarsanız daha hızlı dönebiliriz.">{{ old('message') }}</textarea>
                    @error('message')<span class="mt-1 block text-xs text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                </div>

                <label class="mt-6 flex items-start gap-3 cursor-pointer">
                    <input class="mt-1 h-4 w-4 shrink-0 rounded border-stone-300 text-orange-600 focus:ring-orange-500" type="checkbox" name="privacy_consent" value="1" required>
                    <span class="hc-body-sm text-xs leading-relaxed" style="color: var(--hc-text-secondary)">
                        Talebimin değerlendirilmesi için kişisel verilerimin
                        <a class="font-bold text-orange-700 underline underline-offset-2 dark:text-orange-400" href="{{ route('legal.center') }}#gizlilik-ve-kisisel-veriler">Gizlilik ve Kişisel Veriler</a>
                        bölümünde açıklandığı şekilde işlenmesini kabul ediyorum.
                    </span>
                </label>
                @error('privacy_consent')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <button class="hc-v3-btn hc-v3-btn--primary px-8" type="submit">Mesajı gönder</button>
                    <span class="text-xs" style="color: var(--hc-text-muted)">Talep numaranız gönderimden sonra gösterilir.</span>
                </div>
            </form>

            <aside class="space-y-5">
                <div class="hc-v3-card p-6">
                    <p class="hc-overline" style="color: var(--hc-text-muted)">Doğrudan e-posta</p>
                    <a class="hc-heading-3 mt-2 block break-all font-extrabold text-orange-700 dark:text-orange-400" href="mailto:info@housechefcyprus.com">info@housechefcyprus.com</a>
                    <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">Formdan gelen her talep de bu adrese düşer.</p>
                </div>

                <div class="hc-v3-card p-6">
                    <p class="hc-overline" style="color: var(--hc-text-muted)">Hızlı bağlantılar</p>
                    <div class="mt-4 space-y-3">
                        @foreach ([
                            ['information.show', 'sss', 'Sıkça Sorulan Sorular'],
                            ['information.show', 'tarif-dogrulama-politikasi', 'Tarif Doğrulama Politikası'],
                            ['information.show', 'nasil-calisir', 'Nasıl Çalışır?'],
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
                    <p class="hc-overline" style="color: var(--hc-text-muted)">Telif ve gizlilik</p>
                    <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">
                        Telif bildirimi ve kişisel veri talepleri öncelikli işlenir; formda ilgili konuyu seçmeniz yeterlidir.
                    </p>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.site>
