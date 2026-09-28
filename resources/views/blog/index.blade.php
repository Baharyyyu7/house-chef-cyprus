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
                <article class="hc-v3-card hc-v3-card--interactive group relative flex w-full min-w-0 flex-col overflow-hidden" data-reveal>
                    <div class="flex flex-1 flex-col p-6">
                        <p class="hc-kicker">{{ $post->category?->name ?? __('blog.index.kitchen_note') }}</p>
                        <h2 class="mt-2 text-lg font-extrabold leading-snug line-clamp-2" style="color: var(--hc-text-primary)"><a class="before:absolute before:inset-0 focus-visible:rounded" href="{{ route('blog.show', $post) }}">{{ $post->translated('title') }}</a></h2>
                        <p class="mt-3 line-clamp-3 hc-body-sm" style="color: var(--hc-text-secondary)">{{ $post->translated('excerpt') }}</p>
                        <div class="mt-auto flex items-center justify-between gap-3 pt-5">
                            <p class="text-xs font-bold" style="color: var(--hc-text-muted)">{{ $post->published_at?->format('d.m.Y') }} · {{ __('blog.index.min_read', ['n' => max(1, (int) ceil(str_word_count(strip_tags($post->translated('body'))) / 180))]) }}</p>
                            <a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm font-bold text-orange-700 dark:text-orange-300" href="{{ route('blog.show', $post) }}">{{ __('blog.index.read') }} <span aria-hidden="true">→</span></a>
                        </div>
                    </div>
                </article>

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
