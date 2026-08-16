<x-layouts.site :title="$title" :canonical-url="$seoIndexability['canonical']" :robots="$seoIndexability['robots']">
    <section class="hc-container py-12 sm:py-16">
        <p class="hc-kicker">Editoryal içerik</p>
        <h1 class="hc-display-xl mt-3">Mutfak rehberi</h1>
        <p class="hc-body-lg mt-5" style="color: var(--hc-text-secondary)">Tarif verisi, malzemeler ve mutfak pratikleri hakkında özgün ve temkinli notlar.</p>
        <form class="mt-8 flex max-w-xl gap-2" method="get">
            <label class="sr-only" for="blog-search">Blog ara</label>
            <input id="blog-search" class="hc-input flex-1" name="q" value="{{ request('q') }}" placeholder="Yazılarda ara">
            <button class="hc-v3-btn hc-v3-btn--primary">Ara</button>
        </form>
        <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($posts as $post)
                <article class="hc-v3-card hc-v3-card--interactive group relative flex w-full min-w-0 flex-col overflow-hidden">
                    <a class="relative block aspect-[4/3] bg-stone-100 dark:bg-stone-900" tabindex="-1" aria-hidden="true" href="{{ route('blog.show', $post) }}">
                        <x-blog-cover :post="$post" />
                    </a>
                    <div class="p-6">
                        <p class="hc-kicker">{{ $post->category?->name ?? 'Mutfak notu' }}</p>
                        <h2 class="mt-2 text-xl font-bold"><a class="before:absolute before:inset-0" href="{{ route('blog.show', $post) }}">{{ $post->title }}</a></h2>
                        <p class="mt-3 line-clamp-3 hc-body-sm" style="color: var(--hc-text-secondary)">{{ $post->excerpt }}</p>
                        <p class="mt-4 text-xs" style="color: var(--hc-text-muted)">{{ $post->published_at?->format('d.m.Y') }} · {{ max(1, (int) ceil(str_word_count(strip_tags($post->body)) / 180)) }} dk okuma</p>
                        <a class="mt-5 inline-flex font-bold text-orange-700" href="{{ route('blog.show', $post) }}">Devamını oku →</a>
                    </div>
                </article>
            @empty
                <div class="hc-v3-empty md:col-span-2 xl:col-span-3"><strong>Henüz yayınlanmış blog yazısı yok.</strong><p>Yeni editoryal içerikler burada listelenecek.</p></div>
            @endforelse
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    </section>
</x-layouts.site>
