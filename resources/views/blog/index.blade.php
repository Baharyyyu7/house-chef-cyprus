@php
    // Açıklama sitedeki her sayfada aynıydı; blog listesi kendi işini anlatır.
    $blogAciklamasi = 'Mutfak teknikleri, malzeme rehberleri ve Kıbrıs mutfağı üzerine editoryal yazılar. Her yazı kaynak ve doğrulama notlarıyla yayımlanır.';
@endphp
<x-layouts.site :title="$title" :meta-description="$blogAciklamasi" :structured-data="$seoSchema ?? null" :canonical-url="$seoIndexability['canonical']" :robots="$seoIndexability['robots']">
    <section class="hc-container py-12 sm:py-16">
        <p class="hc-kicker">Editoryal içerik</p>
        <h1 class="hc-display-xl mt-3">Mutfak rehberi</h1>
        <p class="hc-body-lg mt-5" style="color: var(--hc-text-secondary)">Tarif verisi, malzemeler ve mutfak pratikleri hakkında özgün ve temkinli notlar.</p>
        <form class="mt-8 flex max-w-xl gap-2" method="get">
            <label class="sr-only" for="blog-search">Blog ara</label>
            <input id="blog-search" class="hc-input flex-1" name="q" value="{{ request('q') }}" placeholder="Yazılarda ara">
            <button class="hc-v3-btn hc-v3-btn--primary">Ara</button>
        </form>
        {{-- Öne çıkanlar karusel olarak; altında tam liste duruyor. Karusel
             aynı anda beş kart gösteriyor, 27 yazının hepsini ona bırakmak
             geri kalanını gömerdi ve blog listesi bir SEO sayfası. --}}
        <div class="mt-10">
            <x-blog-carousel :posts="$posts->take(8)" />
        </div>

        <h2 class="hc-heading-3 mt-14">Tüm yazılar</h2>
        <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-reveal-group>
            @forelse($posts as $post)
                <article class="hc-v3-card hc-v3-card--interactive group relative flex w-full min-w-0 flex-col overflow-hidden" data-reveal>
                    <div class="flex flex-1 flex-col p-6">
                        <p class="hc-kicker">{{ $post->category?->name ?? 'Mutfak notu' }}</p>
                        <h2 class="mt-2 text-lg font-extrabold leading-snug line-clamp-2" style="color: var(--hc-text-primary)"><a class="before:absolute before:inset-0 focus-visible:rounded" href="{{ route('blog.show', $post) }}">{{ $post->title }}</a></h2>
                        <p class="mt-3 line-clamp-3 hc-body-sm" style="color: var(--hc-text-secondary)">{{ $post->excerpt }}</p>
                        <div class="mt-auto flex items-center justify-between gap-3 pt-5">
                            <p class="text-xs font-bold" style="color: var(--hc-text-muted)">{{ $post->published_at?->format('d.m.Y') }} · {{ max(1, (int) ceil(str_word_count(strip_tags($post->body)) / 180)) }} dk okuma</p>
                            <a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm font-bold text-orange-700 dark:text-orange-300" href="{{ route('blog.show', $post) }}">Oku <span aria-hidden="true">→</span></a>
                        </div>
                    </div>
                </article>

                {{-- Feed reklamı akışın içinde durur; kenar çubuğuna konursa
                     performansı düşer ve okuma akışını da bölmez. --}}
                @if($loop->iteration === 4 && ! $loop->last)
                    <x-ad-slot slot-name="in_feed" class="col-span-full my-2" />
                @endif
            @empty
                <div class="hc-v3-empty md:col-span-2 xl:col-span-3"><strong>Henüz yayınlanmış blog yazısı yok.</strong><p>Yeni editoryal içerikler burada listelenecek.</p></div>
            @endforelse
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    </section>
</x-layouts.site>
