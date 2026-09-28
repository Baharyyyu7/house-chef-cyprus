@php
    // Açıklama sitedeki her sayfada aynıydı; blog listesi kendi işini anlatır.
    $blogAciklamasi = __('blog.index.meta_description');
@endphp
<x-layouts.site :title="$title" :meta-description="$blogAciklamasi" :structured-data="$seoSchema ?? null" :canonical-url="$seoIndexability['canonical']" :robots="$seoIndexability['robots']">
    <section class="hc-container py-12 sm:py-16">
        <p class="hc-kicker">{{ __('blog.index.kicker') }}</p>
        <h1 class="hc-display-xl mt-3">{{ __('blog.index.title') }}</h1>
        <p class="hc-body-lg mt-5" style="color: var(--hc-text-secondary)">{{ __('blog.index.subtitle') }}</p>
        <form class="mt-8 flex max-w-xl gap-2" method="get">
            <label class="sr-only" for="blog-search">{{ __('blog.index.search_label') }}</label>
            <input id="blog-search" class="hc-input flex-1" name="q" value="{{ request('q') }}" placeholder="{{ __('blog.index.search_placeholder') }}">
            <button class="hc-v3-btn hc-v3-btn--primary">{{ __('blog.index.search_button') }}</button>
        </form>
        {{-- Öne çıkanlar karusel olarak; altında tam liste duruyor. Karusel
             aynı anda beş kart gösteriyor, 27 yazının hepsini ona bırakmak
             geri kalanını gömerdi ve blog listesi bir SEO sayfası. --}}
        <div class="mt-10">
            <x-blog-carousel :posts="$posts->take(8)" />
        </div>

        <h2 class="hc-heading-3 mt-14">{{ __('blog.index.all_posts') }}</h2>
        <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-reveal-group>
            @forelse($posts as $post)
                <x-blog-card :post="$post" data-reveal />

                {{-- Feed reklamı akışın içinde durur; kenar çubuğuna konursa
                     performansı düşer ve okuma akışını da bölmez. --}}
                @if($loop->iteration === 4 && ! $loop->last)
                    <x-ad-slot slot-name="in_feed" class="col-span-full my-2" />
                @endif
            @empty
                <div class="hc-v3-empty md:col-span-2 xl:col-span-3"><strong>{{ __('blog.index.empty_title') }}</strong><p>{{ __('blog.index.empty_body') }}</p></div>
            @endforelse
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    </section>
</x-layouts.site>
