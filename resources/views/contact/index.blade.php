<x-layouts.site title="İletişim" meta-description="House Chef Cyprus ile iletişime geçin: tarif hatası, telif bildirimi, iş birliği veya gizlilik talepleri.">
    {{--
        İletişim formu yasal metnin ortasına gömülüydü: bir kullanıcı yazmak
        için önce sözleşme okumak zorundaydı ve alan etiketleri girdilerin
        soluna düşüp hizasız duruyordu. Kendi sayfası oldu; yasal metindeki
        form o metne ait bağlamıyla yerinde kalabilir.
    --}}
    <section class="hc-container hc-page">
        <div class="mx-auto max-w-2xl text-center">
            <p class="hc-kicker">Bize yazın</p>
            <h1 class="hc-display-lg mt-3">İletişim</h1>
            <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">
                Tarifte bir hata mı gördünüz, iş birliği mi öneriyorsunuz, yoksa verilerinizle ilgili bir talebiniz mi var? Hepsi buradan ulaşır.
            </p>
        </div>

        @if(session('status'))
            <div class="hc-alert hc-alert-success mx-auto mt-8 max-w-2xl" role="status">{{ session('status') }}</div>
        @endif

        <div class="mx-auto mt-10 grid max-w-5xl gap-6 lg:grid-cols-[1fr_.62fr] lg:items-start">
            <form class="hc-v3-card p-6 sm:p-7" method="post" action="{{ route('legal.center.contact.store') }}">
                @csrf
                <input type="hidden" name="source_page" value="iletisim">
                {{-- Bal küpü: gerçek kullanıcı görmez, doldurulmuşsa istek elenir. --}}
                <div class="hidden" aria-hidden="true">
                    <label>Web sitesi<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="hc-field">
                        <span class="hc-field__label">Ad</span>
                        <input class="hc-input" type="text" name="first_name" value="{{ old('first_name') }}" required maxlength="60">
                    </label>
                    <label class="hc-field">
                        <span class="hc-field__label">Soyad</span>
                        <input class="hc-input" type="text" name="last_name" value="{{ old('last_name') }}" required maxlength="60">
                    </label>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="hc-field">
                        <span class="hc-field__label">E-posta adresi</span>
                        <input class="hc-input" type="email" name="email" value="{{ old('email') }}" required>
                    </label>
                    <label class="hc-field">
                        <span class="hc-field__label">Telefon <span class="font-normal" style="color: var(--hc-text-muted)">(isteğe bağlı)</span></span>
                        <input class="hc-input" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel">
                    </label>
                </div>

                <label class="hc-field mt-4">
                    <span class="hc-field__label">Konu</span>
                    <select class="hc-input" name="subject" required>
                        @foreach(config('contact-requests.subjects') as $anahtar => $tanim)
                            <option value="{{ $anahtar }}" @selected(old('subject') === $anahtar)>{{ is_array($tanim) ? ($tanim['label'] ?? $anahtar) : $tanim }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="hc-field mt-4">
                    <span class="hc-field__label">Mesajınız</span>
                    <textarea class="hc-input min-h-40" name="message" required minlength="20" maxlength="4000" placeholder="Hangi tarif, hangi sayfa veya hangi konu olduğunu yazarsanız daha hızlı dönebiliriz.">{{ old('message') }}</textarea>
                </label>

                <label class="mt-5 flex items-start gap-3">
                    <input class="mt-1 h-4 w-4 shrink-0" type="checkbox" name="privacy_consent" value="1" required>
                    <span class="hc-body-sm" style="color: var(--hc-text-secondary)">
                        Talebimin değerlendirilmesi için kişisel verilerimin
                        <a class="font-bold underline underline-offset-2" href="{{ route('legal.center') }}#gizlilik-ve-kisisel-veriler">Gizlilik ve Kişisel Veriler</a>
                        bölümünde açıklandığı şekilde işlenmesini kabul ediyorum.
                    </span>
                </label>

                <div class="mt-6 flex flex-wrap items-center gap-4">
                    <button class="hc-btn hc-btn-primary px-7" type="submit">Mesajı gönder</button>
                    <span class="hc-caption" style="color: var(--hc-text-muted)">Talep numaranız gönderimden sonra gösterilir.</span>
                </div>
            </form>

            <aside class="grid gap-4">
                <div class="hc-v3-card p-5">
                    <p class="hc-overline" style="color: var(--hc-text-muted)">Doğrudan</p>
                    <a class="hc-heading-4 mt-2 block break-all" style="color: var(--hc-text-accent)" href="mailto:info@housechefcyprus.com">info@housechefcyprus.com</a>
                    <p class="hc-caption mt-2" style="color: var(--hc-text-secondary)">Formdan gelen her talep de bu adrese düşer.</p>
                </div>

                <div class="hc-v3-card p-5">
                    <p class="hc-overline" style="color: var(--hc-text-muted)">Önce şuraya bakın</p>
                    <div class="mt-3 grid gap-2.5">
                        @foreach ([
                            ['information.show', 'sss', 'Sıkça sorulanlar'],
                            ['information.show', 'tarif-dogrulama-politikasi', 'Tarifler nasıl doğrulanıyor?'],
                            ['information.show', 'nasil-calisir', 'Site nasıl çalışır?'],
                        ] as [$rota, $sayfa, $etiket])
                            @if (Route::has($rota))
                                <a class="hc-body-sm font-semibold hover:underline" href="{{ route($rota, $sayfa) }}">{{ $etiket }} →</a>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="hc-v3-card p-5">
                    <p class="hc-overline" style="color: var(--hc-text-muted)">Telif ve gizlilik</p>
                    <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">
                        Telif bildirimi ve kişisel veri talepleri öncelikli işlenir; formda konuyu seçmeniz yeterli.
                    </p>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.site>
