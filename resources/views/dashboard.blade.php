<x-layouts.site title="Hesabım · House Chef Cyprus">
    @php
        /**
         * Sayfa üç bağlantı kartı ve değişmeyen bir "hızlı başlangıç"
         * listesinden ibaretti. O üç bağlantı zaten üstteki Mutfağım
         * menüsünde duruyor, liste ise kimin ne yaptığına bakmıyordu:
         * dolabı dolu olana da "dolabınıza malzeme ekleyin" diyordu.
         *
         * Sayfa artık kullanıcının kendi durumunu gösteriyor ve sıradaki
         * adımı o duruma bakarak öneriyor.
         */
        $uye = auth()->user();
        $dolap = $uye->pantryItems()->count();
        $favori = $uye->favoriteRecipes()->count();
        $oturum = $uye->cookingSessions()->count();

        $adim = match (true) {
            $dolap === 0 => ['Dolabınızı kurun', 'Evdeki malzemeleri girin; tarifler ona göre eşleşsin.', 'pantry.index', 'Dolabı aç'],
            $favori === 0 => ['Beğendiklerinizi işaretleyin', 'Favoriye eklediğiniz tarifler burada birikir.', 'recipes.index', 'Tarifleri gez'],
            $oturum === 0 => ['Pişirme modunu deneyin', 'Adımları tek tek takip edin, ilerlemeniz cihazda kalsın.', 'recipes.index', 'Bir tarif seç'],
            default => ['Dolabınızla eşleştirin', 'Elinizdekilere göre bugün ne pişirebileceğinizi görün.', 'ingredients.explore', 'Eşleştir'],
        };
    @endphp

    <section class="hc-container hc-page">
        <p class="hc-kicker">Hesabım</p>
        <h1 class="hc-display-lg mt-3">Merhaba {{ str($uye->name)->before(' ') }}.</h1>

        {{-- Sıradaki adım kullanıcının gerçek durumundan çıkıyor. --}}
        <div class="hc-v3-card mt-8 flex flex-wrap items-center justify-between gap-5 p-6 sm:p-7">
            <div class="min-w-0">
                <p class="hc-overline" style="color: var(--hc-text-muted)">Sıradaki adım</p>
                <h2 class="hc-heading-3 mt-2">{{ $adim[0] }}</h2>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">{{ $adim[1] }}</p>
            </div>
            @if (Route::has($adim[2]))
                <a class="hc-btn hc-btn-primary shrink-0 px-6" href="{{ route($adim[2]) }}">{{ $adim[3] }}</a>
            @endif
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-3">
            @foreach ([
                [$dolap, 'dolabınızda ürün', 'pantry.index'],
                [$favori, 'favori tarif', 'favorites.index'],
                [$oturum, 'pişirme oturumu', null],
            ] as [$sayi, $etiket, $rota])
                @php($icerik = '<span class="block text-4xl font-black leading-none" style="color: var(--hc-text-primary)">'.$sayi.'</span><span class="hc-body-sm mt-2 block" style="color: var(--hc-text-secondary)">'.$etiket.'</span>')
                @if ($rota && Route::has($rota))
                    <a class="hc-card-hover rounded-3xl border p-5" style="border-color: var(--hc-line); background: var(--hc-surface)" href="{{ route($rota) }}">{!! $icerik !!}</a>
                @else
                    <div class="rounded-3xl border p-5" style="border-color: var(--hc-line); background: var(--hc-surface)">{!! $icerik !!}</div>
                @endif
            @endforeach
        </div>

        @unless ($uye->isPremium())
            @if (Route::has('pricing'))
                <div class="hc-v3-card mt-5 flex flex-wrap items-center justify-between gap-4 p-5">
                    <p class="hc-body-sm min-w-0" style="color: var(--hc-text-secondary)">
                        <span class="font-bold" style="color: var(--hc-text-primary)">Premium</span> üyelikte sitede hiç reklam görünmez.
                    </p>
                    <a class="hc-btn hc-btn-outline shrink-0" href="{{ route('pricing') }}">Planları gör</a>
                </div>
            @endif
        @endunless

        <div class="mt-8 flex flex-wrap gap-3">
            <a class="hc-btn hc-btn-outline" href="{{ route('profile') }}">Hesap ayarları</a>
            @if (Route::has('collections.index'))
                <a class="hc-btn hc-btn-outline" href="{{ route('collections.index') }}">Koleksiyonlarım</a>
            @endif
        </div>
    </section>
</x-layouts.site>
