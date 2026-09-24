<x-layouts.site
    title="Mutfaklar · Kıbrıs, Türk ve Akdeniz Tarifleri · House Chef Cyprus"
    meta-description="Kıbrıs, Türk, Akdeniz ve daha fazlası. Her mutfaktan doğrulanmış tarifler; ölçüler ve süreler görünür."
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <p class="hc-kicker">Dünya ve Bölge Lezzetleri</p>
        <h1 class="mt-3 font-display text-4xl font-semibold">Mutfaklar</h1>

        <p class="hc-body-lg mt-4 max-w-2xl text-muted">
            {{-- Yalnız arkasında yeterli tarif olan mutfaklar listeleniyor;
                 boş bir sayfaya bağlantı vermek ziyaretçiyi de arama motorunu
                 da yanıltır. --}}
            Kıbrıs, Türk, Akdeniz ve ötesi. Arkasında yeterli sayıda doğrulanmış tarif bulunan geleneksel ve modern mutfaklar.
        </p>

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach ($mutfaklar as $mutfak)
                @php($kapak = $kapaklar[$mutfak->id] ?? null)
                <a class="hc-hub-card group" href="{{ route('cuisines.show', $mutfak->slug) }}">
                    {{-- Gorsel varsa kart bir yemek gosterir; yoksa metin
                         karti olarak kalir. Yer tutucu basmak, bos
                         birakmaktan kotu. --}}
                    @if ($kapak)
                        <span class="hc-hub-card__gorsel" aria-hidden="true">
                            <img src="{{ $kapak->responsiveUrl(400) }}" srcset="{{ $kapak->responsiveSrcset([400, 800]) }}" sizes="(min-width: 1024px) 22rem, (min-width: 640px) 45vw, 92vw" width="400" height="300" alt="" loading="lazy" decoding="async">
                        </span>
                    @endif
                    <span class="hc-hub-card__govde">
                        <span class="hc-hub-card__baslik">{{ $mutfak->name }}</span>
                        <span class="hc-caption">{{ $mutfak->recipes_count }} doğrulanmış tarif</span>
                    </span>
                    <span class="hc-hub-card__ok" aria-hidden="true">›</span>
                </a>
            @endforeach
        </div>

        @if ($mutfaklar->isEmpty())
            <p class="hc-body-sm mt-8 text-muted">Henüz yeterli tarifi olan mutfak yok.</p>
        @endif
    </section>
</x-layouts.site>
