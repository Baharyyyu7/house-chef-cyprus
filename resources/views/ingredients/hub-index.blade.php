<x-layouts.site
    :title="__('ingredient_hub.index_title')"
    :meta-description="__('ingredient_hub.index_meta')"
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <p class="hc-kicker">{{ __('ingredient_hub.index_kicker') }}</p>
        <h1 class="mt-3 font-display text-4xl font-semibold">{{ __('ingredient_hub.index_h1') }}</h1>
        <p class="hc-body-lg mt-4 max-w-2xl text-muted">
            {{ __('ingredient_hub.index_lead') }}
        </p>

        {{-- Sayfası olan malzemeler eşikten geçenler. Bir malzemenin arkasında
             yeterli tarif yoksa listede de görünmez: listede olup açılmayan
             bağlantı hem ziyaretçiyi hem taramayı boşa yorar. --}}
        <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($malzemeler as $malzeme)
                @php($kapak = $kapaklar[$malzeme->id] ?? null)
                <a class="hc-hub-card group" href="{{ route('ingredients.hub.show', $malzeme->slug) }}">
                    @if ($kapak)
                        <span class="hc-hub-card__gorsel" aria-hidden="true">
                            <img src="{{ $kapak->responsiveUrl(400) }}" srcset="{{ $kapak->responsiveSrcset([400, 800]) }}" sizes="(min-width: 1024px) 22rem, (min-width: 640px) 45vw, 92vw" width="400" height="300" alt="{{ $malzeme->name }}" loading="lazy" decoding="async">
                        </span>
                    @endif
                    <span class="hc-hub-card__govde">
                        <span class="hc-hub-card__baslik">{{ $malzeme->name }}</span>
                        <span class="hc-caption">{{ __('hub.recipes_count', ['count' => $malzeme->tarif_sayisi]) }}</span>
                    </span>
                    <span class="hc-hub-card__ok" aria-hidden="true">›</span>
                </a>
            @endforeach
        </div>

        @if ($malzemeler->isEmpty())
            <p class="hc-body-sm mt-8 text-muted">{{ __('ingredient_hub.index_empty') }}</p>
        @endif
    </section>
</x-layouts.site>
