<x-layouts.site :title="$baslik.' · House Chef Cyprus'" :meta-description="$aciklama" :canonical-url="$seoIndexability['canonical']" :robots="$seoIndexability['robots']" :structured-data="$seoSchema">
    <section class="hc-container py-14">
        {{-- Yol gosterimi show sayfasiyla ayni desende. --}}
        <nav class="hc-body-sm text-muted" aria-label="{{ __('hub.breadcrumb_label') }}">
            <a class="font-semibold text-gold-700 transition hover:underline dark:text-gold-400" href="{{ route('recipes.index') }}">{{ __('nav.recipes') }}</a>
            <span aria-hidden="true"> › </span><span class="text-charcoal-900 dark:text-charcoal-100 font-medium">{{ $baslik }}</span>
        </nav>

        <h1 class="mt-4 font-display text-4xl font-semibold sm:text-5xl">{{ $baslik }}</h1>
        <p class="hc-body-lg mt-4 max-w-2xl text-muted">{{ $aciklama }}</p>

        <ul class="mt-10 grid list-none gap-5 p-0 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach($sayfalar as $sayfa)
                <li class="hc-v3-card hc-v3-card--interactive relative p-6">
                    <p class="hc-kicker">{{ __('hub.verified_count', ['count' => $sayfa['count']]) }}</p>
                    <h2 class="hc-heading-3 mt-2">
                        <a class="before:absolute before:inset-0" href="{{ $sayfa['url'] }}">{{ $sayfa['title'] }}</a>
                    </h2>
                    <p class="hc-body-sm mt-2" style="color:var(--hc-text-secondary)">{{ $sayfa['description'] }}</p>
                </li>
            @endforeach
        </ul>
    </section>
</x-layouts.site>
