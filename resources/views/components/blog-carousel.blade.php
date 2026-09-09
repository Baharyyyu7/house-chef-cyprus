@props(['posts', 'heading' => 'Öne çıkan yazılar'])

@php
    /**
     * Dairesel yazı karuseli.
     *
     * 21st.dev'deki React/framer-motion bileşeninin bu projede çalışması
     * mümkün değil: burada React, TypeScript ve framer-motion yok - Laravel
     * Blade, Alpine ve Tailwind var. Davranış buraya taşındı: eliptik yörünge,
     * merkezdeki kartın büyümesi, otomatik ilerleme, ok tuşları, nokta
     * göstergeleri.
     *
     * Bir fark bilerek yapıldı: orijinal her kartı <button> olarak basıyor.
     * Bu bir SEO bileşeni; arama motoru <button> takip edemez ve yazılar
     * keşfedilmez. Kartlar gerçek <a href> bağlantısı.
     *
     * Görsel yok. Kartı taşıyan şey başlık, kategori ve özet - blog zaten
     * fotoğrafsız yayımlanıyor ve olmayan görselin yerine yer tutucu koymak
     * sayfayı ağırlaştırmaktan başka bir işe yaramıyordu.
     */
    $liste = $posts->values();
    $adet = $liste->count();
@endphp

@if ($adet > 0)
    <section
        class="hc-karusel"
        aria-roledescription="carousel"
        aria-label="{{ $heading }}"
        x-data="hcBlogKaruseli({{ $adet }})"
        @mouseenter="duraklat = true"
        @mouseleave="duraklat = false"
        @focusin="duraklat = true"
        @focusout="duraklat = false"
        @keydown.left.prevent="geri()"
        @keydown.right.prevent="ileri()"
    >
        <div class="hc-karusel__yoruunge" role="group" aria-live="polite">
            @foreach ($liste as $sira => $post)
                <a
                    class="hc-karusel__kart"
                    href="{{ route('blog.show', $post) }}"
                    x-bind:style="konum({{ $sira }})"
                    x-bind:class="{{ $sira }} === etkin ? 'is-etkin' : ''"
                    x-bind:aria-hidden="gorunur({{ $sira }}) ? 'false' : 'true'"
                    x-bind:tabindex="gorunur({{ $sira }}) ? 0 : -1"
                >
                    <span class="hc-karusel__etiket">{{ $post->category?->name ?? 'Mutfak notu' }}</span>
                    <span class="hc-karusel__baslik"><span>{{ $post->translated('title') }}</span></span>
                    <span class="hc-karusel__ozet"><span>{{ \Illuminate\Support\Str::limit((string) $post->translated('excerpt'), 105) }}</span></span>
                    <span class="hc-karusel__tarih">{{ $post->published_at?->format('d.m.Y') }}</span>
                </a>
            @endforeach
        </div>

        <div class="hc-karusel__kontrol">
            <button type="button" class="hc-karusel__ok" @click="geri()" aria-label="Önceki yazı">
                <x-ui.icon name="chevron-down" size="18" class="rotate-90" />
            </button>

            <div class="hc-karusel__noktalar" role="tablist" aria-label="Yazı seç">
                @foreach ($liste as $sira => $post)
                    <button
                        type="button"
                        role="tab"
                        class="hc-karusel__nokta"
                        x-bind:class="{{ $sira }} === etkin ? 'is-etkin' : ''"
                        x-bind:aria-selected="{{ $sira }} === etkin"
                        @click="git({{ $sira }})"
                        aria-label="{{ $post->translated('title') }}"
                    ></button>
                @endforeach
            </div>

            <button type="button" class="hc-karusel__ok" @click="ileri()" aria-label="Sonraki yazı">
                <x-ui.icon name="chevron-down" size="18" class="-rotate-90" />
            </button>
        </div>
    </section>
@endif
