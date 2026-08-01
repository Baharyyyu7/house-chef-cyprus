<x-layouts.site :title="$page['title'].' · House Chef Cyprus'" :meta-description="$page['lead']" :canonical-url="url()->current()" :structured-data="$schema">
    <section class="hc-container py-12 sm:py-16">
        <div class="max-w-3xl">
            <nav aria-label="Sayfa yolu" class="text-sm text-muted"><a class="hover:text-orange-600" href="{{ route('home') }}">Ana sayfa</a><span class="mx-2" aria-hidden="true">/</span><span aria-current="page">{{ $page['title'] }}</span></nav>
            <p class="hc-kicker">{{ $page['eyebrow'] }}</p>
            <h1 class="hc-title mt-3">{{ $page['title'] }}</h1>
            <p class="hc-subtitle mt-5">{{ $page['lead'] }}</p>
        </div>
        <div class="mt-10 grid gap-5 md:grid-cols-2">
            @foreach($page['sections'] as $section)
                <article class="hc-v3-card p-6"><h2 class="font-display text-2xl font-semibold">{{ $section['title'] }}</h2><p class="mt-3 leading-7 text-muted">{{ $section['body'] }}</p></article>
            @endforeach
        </div>
    </section>
</x-layouts.site>
