<x-layouts.site title="Tarif Kategorileri · House Chef Cyprus" meta-description="Ana yemek, çorba, tatlı, hamur işi ve daha fazla kategoride doğrulanmış tarifleri keşfedin." :canonical-url="$seoIndexability['canonical']" :robots="$seoIndexability['robots']">
    <section class="hc-container py-14">
        <p class="hc-kicker">Tarif arşivi</p>
        <h1 class="mt-3 font-display text-4xl font-semibold">Tarif kategorileri</h1>
        <p class="hc-body-lg mt-4 max-w-2xl text-muted">Yalnız arkasında en az dört doğrulanmış tarif bulunan kategoriler burada yer alır.</p>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach($kategoriler as $kategori)
                @php($kapak = $kapaklar[$kategori->id] ?? null)
                <a class="hc-hub-card group" href="{{ route('categories.show', $kategori->slug) }}">
                    @if ($kapak)
                        <span class="hc-hub-card__gorsel" aria-hidden="true">
                            <img src="{{ $kapak->responsiveUrl(400) }}" srcset="{{ $kapak->responsiveSrcset([400, 800]) }}" sizes="(min-width: 1024px) 22rem, (min-width: 640px) 45vw, 92vw" width="400" height="300" alt="{{ taxonomy_name($kategori) }}" loading="lazy" decoding="async">
                        </span>
                    @endif
                    <span class="hc-hub-card__govde">
                        <span class="hc-hub-card__baslik">{{ $kategori->name }}</span>
                        <span class="hc-caption">{{ $kategori->recipes_count }} doğrulanmış tarif</span>
                    </span>
                    <span class="hc-hub-card__ok" aria-hidden="true">›</span>
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.site>
