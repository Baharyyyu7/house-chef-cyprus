<x-layouts.site
    :title="$koleksiyon['title'].' · House Chef Cyprus'"
    :meta-description="$koleksiyon['description']"
    :structured-data="$seoSchema"
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">
    <section class="hc-container py-14">
        <nav class="hc-body-sm text-muted" aria-label="{{ __('hub.breadcrumb_label') }}">
            <a class="font-semibold text-gold-700 transition hover:underline dark:text-gold-400" href="{{ route('recipes.index') }}">{{ __('nav.recipes') }}</a>
            <span aria-hidden="true"> › </span><span class="text-charcoal-900 dark:text-charcoal-100 font-medium">{{ $koleksiyon['title'] }}</span>
        </nav>

        <h1 class="mt-4 font-display text-4xl font-semibold sm:text-5xl">{{ $koleksiyon['title'] }}</h1>
        <p class="hc-body-lg mt-4 max-w-2xl text-muted">{{ $koleksiyon['description'] }}</p>
        <p class="hc-body-sm mt-3 text-muted">{{ __('collection.show_body', ['count' => $tarifler->count()]) }}</p>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach ($tarifler as $tarif)
                <x-recipe-card-v3 :recipe="$tarif" :show-favorite="false" />
            @endforeach
        </div>
    </section>
</x-layouts.site>
