<x-layouts.site :title="$title" :canonical-url="$seoIndexability['canonical']" :robots="$seoIndexability['robots']">
    <section class="hc-container py-12 sm:py-16">
        <p class="hc-kicker">Editoryal içerik</p>
        <h1 class="hc-title mt-3">Mutfak rehberi</h1>
        <p class="hc-subtitle mt-5">Tarif verisi, malzemeler ve mutfak pratikleri hakkında özgün ve temkinli notlar.</p>
        <form class="mt-8 flex max-w-xl gap-2" method="get">
            <label class="sr-only" for="blog-search">Blog ara</label>
            <input id="blog-search" class="hc-input flex-1" name="q" value="{{ request('q') }}" placeholder="Yazılarda ara">
            <button class="hc-v3-btn hc-v3-btn--primary">Ara</button>
        </form>
        <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($posts as $post)
                @php($heroMedia = $post->heroMedia)
                @php($hasApprovedMedia = $heroMedia?->isApprovedRealMedia() && $heroMedia?->publicUrl())
                <article class="hc-v3-card w-full min-w-0 overflow-hidden">
                    <a class="relative block aspect-[4/3] bg-stone-100 dark:bg-stone-900" href="{{ route('blog.show', $post) }}">
                        @if($hasApprovedMedia)
                            <picture>
                                <source type="image/avif" srcset="{{ $heroMedia->responsiveUrl(800, 'avif') }}">
                                <source type="image/webp" srcset="{{ $heroMedia->responsiveUrl(800, 'webp') }}">
                                <img class="h-full w-full object-cover" src="{{ $heroMedia->responsiveUrl(800) }}" width="800" height="600" alt="{{ $heroMedia->generated_by_ai ? 'Yapay zekâ ile üretilmiş editoryal illüstrasyon: ' : '' }}{{ $heroMedia->alt_text }}" loading="lazy">
                            </picture>
                            @if($heroMedia->generated_by_ai)
                                <span class="absolute right-3 top-3 hc-v3-badge border-white/20 bg-black/55 text-white backdrop-blur">Yapay zekâ görseli</span>
                            @endif
                        @else
                            <div class="flex h-full flex-col items-center justify-center gap-3 p-6 text-center">
                                <span class="grid h-11 w-11 place-items-center rounded-full bg-orange-100 text-xl text-orange-800 dark:bg-orange-500/15 dark:text-orange-200">▤</span>
                                <strong>Yazıya özel görsel hazırlanıyor</strong>
                                <span class="text-xs leading-5 text-muted">Onaylı ve konuya özel editoryal görsel bulunana kadar fotoğraf gösterilmez.</span>
                            </div>
                        @endif
                    </a>
                    <div class="p-6">
                        <p class="hc-kicker">{{ $post->category?->name ?? 'Mutfak notu' }}</p>
                        <h2 class="mt-2 text-xl font-bold"><a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a></h2>
                        <p class="mt-3 line-clamp-3 text-sm text-muted">{{ $post->excerpt }}</p>
                        <p class="mt-4 text-xs text-muted">{{ $post->published_at?->format('d.m.Y') }} · {{ max(1, (int) ceil(str_word_count(strip_tags($post->body)) / 180)) }} dk okuma</p>
                        <a class="mt-5 inline-flex font-bold text-orange-700" href="{{ route('blog.show', $post) }}">Devamını oku →</a>
                    </div>
                </article>
            @empty
                <div class="hc-admin-empty md:col-span-2 xl:col-span-3"><strong>Henüz yayınlanmış blog yazısı yok.</strong><p>Yeni editoryal içerikler burada listelenecek.</p></div>
            @endforelse
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    </section>
</x-layouts.site>
