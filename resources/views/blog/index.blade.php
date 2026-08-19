@php
    // Açıklama sitedeki her sayfada aynıydı; blog listesi kendi işini anlatır.
    $blogAciklamasi = 'Mutfak teknikleri, malzeme rehberleri ve Kıbrıs mutfağı üzerine editoryal yazılar. Her yazı kaynak ve doğrulama notlarıyla yayımlanır.';
@endphp
<x-layouts.site :title="$title" :meta-description="$blogAciklamasi" :canonical-url="$seoIndexability['canonical']" :robots="$seoIndexability['robots']">
    <section class="hc-container py-12 sm:py-16">
        <p class="hc-kicker">Editoryal içerik</p>
        <h1 class="hc-display-xl mt-3">Mutfak rehberi</h1>
        <p class="hc-body-lg mt-5" style="color: var(--hc-text-secondary)">Tarif verisi, malzemeler ve mutfak pratikleri hakkında özgün ve temkinli notlar.</p>
        <form class="mt-8 flex max-w-xl gap-2" method="get">
            <label class="sr-only" for="blog-search">Blog ara</label>
            <input id="blog-search" class="hc-input flex-1" name="q" value="{{ request('q') }}" placeholder="Yazılarda ara">
            <button class="hc-v3-btn hc-v3-btn--primary">Ara</button>
        </form>
        <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-reveal-group>
            @forelse($posts as $post)
                {{-- Rehber yazılarının çoğunda fotoğraf yok ve tipografik kapak kullanılır.
                     Ama onaylı bir görsel varsa onu göstermek gerekir: aksi hâlde editörün
                     onayladığı görsel yayına hiç çıkmaz ve yapay zekâ açıklaması kaybolur. --}}
                @php($heroMedia = app(\App\Services\Media\PublicMediaEligibilityService::class)->heroOrFirstEligible($post->heroMedia, $post->media))
                <article class="hc-v3-card hc-v3-card--interactive group relative flex w-full min-w-0 flex-col overflow-hidden">
                    <a class="relative block aspect-[4/3] bg-stone-100 dark:bg-stone-900" tabindex="-1" aria-hidden="true" href="{{ route('blog.show', $post) }}">
                        @if($heroMedia?->publicUrl())
                            <picture>
                                <source type="image/avif" srcset="{{ $heroMedia->responsiveUrl(800, 'avif') }}">
                                <source type="image/webp" srcset="{{ $heroMedia->responsiveUrl(800, 'webp') }}">
                                <img class="h-full w-full object-cover" src="{{ $heroMedia->responsiveUrl(800) }}" width="800" height="600" alt="{{ $heroMedia->generated_by_ai ? 'Yapay zekâ ile üretilmiş editoryal illüstrasyon: ' : '' }}{{ $heroMedia->alt_text }}" loading="lazy">
                            </picture>
                            @if($heroMedia->generated_by_ai)
                                <span class="absolute right-3 top-3 hc-v3-badge border-white/20 bg-black/55 text-white backdrop-blur">Yapay zekâ görseli</span>
                            @endif
                        @else
                            <x-blog-cover :post="$post" />
                        @endif
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
