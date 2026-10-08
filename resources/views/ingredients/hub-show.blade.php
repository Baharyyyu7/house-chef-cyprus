<x-layouts.site
    :title="__('ingredient_hub.page_title', ['name' => $malzeme->name, 'count' => $tarifler->count()])"
    :meta-description="__('ingredient_hub.meta_description', ['name' => $malzeme->name, 'count' => $tarifler->count()])"
    :structured-data="$seoSchema"
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <nav class="hc-body-sm text-muted" aria-label="{{ __('hub.breadcrumb_label') }}">
            <a class="font-semibold text-gold-700 transition hover:underline dark:text-gold-400" href="{{ route('ingredients.hub.index') }}">{{ __('ingredient_hub.breadcrumb') }}</a>
            <span aria-hidden="true"> › </span><span class="text-charcoal-900 dark:text-charcoal-100 font-medium">{{ $malzeme->name }}</span>
        </nav>

        {{-- Başlık doğrudan arama niyetini karşılıyor: insanlar "hellim ile ne
             yapılır" diye arıyor, "hellim" diye değil. --}}
        <h1 class="mt-3 font-display text-4xl font-semibold">{{ __('ingredient_hub.h1', ['name' => $malzeme->name]) }}</h1>

        <p class="hc-body-lg mt-4 max-w-2xl text-muted">
            {{ __('ingredient_hub.body', ['name' => $malzeme->name, 'count' => $tarifler->count()]) }}
        </p>

        {{-- Not alanı yalnız Türkçe tutuluyor; yabancı sayfada Türkçe
             paragraf basılmaz. --}}
        @if (turkce_sayfa() && filled($malzeme->notes))
            <p class="hc-body-sm mt-4 max-w-2xl">{{ $malzeme->notes }}</p>
        @endif

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($tarifler as $tarif)
                <x-recipe-card-v3 :recipe="$tarif" :show-favorite="false" />
                @if ($loop->iteration === 4 && ! $loop->last)
                    <x-ad-slot slot-name="in_feed" class="col-span-full my-2" />
                @endif
            @endforeach
        </div>

        <div class="mt-12 max-w-3xl">
            <x-ad-slot slot-name="multiplex" class="my-8" />
        </div>
    </section>
</x-layouts.site>
