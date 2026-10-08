<x-layouts.site
    :title="__('cuisine.index_title')"
    :meta-description="__('cuisine.index_meta')"
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <p class="hc-kicker">{{ __('cuisine.index_kicker') }}</p>
        <h1 class="mt-3 font-display text-4xl font-semibold">{{ __('cuisine.breadcrumb') }}</h1>

        <p class="hc-body-lg mt-4 max-w-2xl text-muted">
            {{-- Yalnız arkasında yeterli tarif olan mutfaklar listeleniyor;
                 boş bir sayfaya bağlantı vermek ziyaretçiyi de arama motorunu
                 da yanıltır. --}}
            {{ __('cuisine.index_lead') }}
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
                            <img src="{{ $kapak->responsiveUrl(400) }}" srcset="{{ $kapak->responsiveSrcset([400, 800]) }}" sizes="(min-width: 1024px) 22rem, (min-width: 640px) 45vw, 92vw" width="400" height="300" alt="{{ taxonomy_name($mutfak) }}" loading="lazy" decoding="async">
                        </span>
                    @endif
                    <span class="hc-hub-card__govde">
                        <span class="hc-hub-card__baslik">{{ taxonomy_name($mutfak) }}</span>
                        <span class="hc-caption">{{ __('hub.verified_count', ['count' => $mutfak->recipes_count]) }}</span>
                    </span>
                    <span class="hc-hub-card__ok" aria-hidden="true">›</span>
                </a>
            @endforeach
        </div>

        @if ($mutfaklar->isEmpty())
            <p class="hc-body-sm mt-8 text-muted">{{ __('cuisine.index_empty') }}</p>
        @endif
    </section>
</x-layouts.site>
