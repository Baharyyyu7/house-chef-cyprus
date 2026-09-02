<x-layouts.site :livewire="true" title="Profilim · House Chef Cyprus">
    @php
        /**
         * Sayfa üç ayrı sorunu bir arada taşıyordu: başlık bir kapsayıcının
         * içindeydi, altındaki kartlar değildi - bu yüzden kartlar ekranın
         * kenarına yapışıyor ve başlıkla hizalanmıyordu. Dört kutu aynı
         * boyutta duruyor ama biri isim, üçü sayı gösteriyordu, yani aynı
         * görünen şeyler aynı işi yapmıyordu. Üç form da başlıksız kartlar
         * hâlinde alt alta diziliydi; hangisinin ne olduğu ancak içine
         * bakınca anlaşılıyordu.
         *
         * Artık tek bir kapsayıcı var, kimlik ile sayılar ayrı işler olarak
         * ayrıldı ve başlıkların tek bir sahibi kaldı: her formun kendi
         * bileşeni. Hesabı silme bölümü en sonda ve görsel olarak ayrı
         * duruyor: geri dönüşü olmayan tek işlem, yanlışlıkla tıklanabilecek
         * bir yerde durmamalı.
         */
        $uye = auth()->user();
        $sayilar = [
            [$uye->favoriteRecipes()->count(), 'favori tarif', Route::has('favorites.index') ? 'favorites.index' : null],
            [$uye->pantryItems()->count(), 'dolap ürünü', Route::has('pantry.index') ? 'pantry.index' : null],
            [$uye->cookingSessions()->count(), 'pişirme oturumu', null],
        ];
    @endphp

    <section class="hc-container hc-page">
        <p class="hc-kicker">Hesap ayarları</p>
        <h1 class="hc-display-lg mt-3">Profilim</h1>

        {{-- Kimlik kartı: kim olduğunuz ve hesabın durumu. Sayılarla aynı
             sırada değil, çünkü aynı soruyu yanıtlamıyorlar. --}}
        <div class="hc-v3-card mt-8 flex flex-wrap items-center gap-5 p-6 sm:p-7">
            <span class="hc-profile-avatar" aria-hidden="true">{{ str($uye->name)->substr(0, 1)->upper() }}</span>
            <div class="min-w-0">
                <h2 class="hc-heading-3">{{ $uye->name }}</h2>
                <p class="hc-body-sm mt-1 break-all" style="color: var(--hc-text-secondary)">{{ $uye->email }}</p>
            </div>
            <span @class(['hc-profile-rozet ms-auto', 'is-onayli' => $uye->hasVerifiedEmail()])>
                {{ $uye->hasVerifiedEmail() ? 'E-posta doğrulandı' : 'E-posta doğrulama bekliyor' }}
            </span>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-3">
            @foreach ($sayilar as [$deger, $etiket, $rota])
                @php($etiketli = $deger.' '.$etiket)
                <div class="hc-v3-card p-6">
                    <strong class="hc-display-sm block" style="color: var(--hc-text-primary)">{{ $deger }}</strong>
                    <p class="hc-body-sm mt-1" style="color: var(--hc-text-secondary)">{{ $etiket }}</p>
                    @if ($rota)
                        <a class="hc-profile-link mt-3 inline-flex" href="{{ route($rota) }}" aria-label="{{ $etiketli }} listesini aç">Listeyi aç →</a>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Başlıklar ve açıklamalar formların kendi bileşenlerinde duruyor;
             burada bir kez daha yazmak aynı cümleyi iki kez gösteriyordu. --}}
        <div class="mt-10 space-y-5">
            <section class="hc-v3-card p-6 sm:p-7">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </section>

            <section class="hc-v3-card p-6 sm:p-7">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </section>

            {{-- Geri dönüşü olmayan tek bölüm, arada değil en sonda ve kendi
                 rengiyle ayrı duruyor. --}}
            <section class="hc-v3-card hc-profile-tehlike p-6 sm:p-7">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </section>
        </div>
    </section>
</x-layouts.site>
