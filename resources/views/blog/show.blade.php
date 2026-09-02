<x-layouts.site :title="$metadata['title']" :meta-description="$metadata['description']" :canonical-url="$metadata['canonical']" :robots="$metadata['robots']" :social-image="$heroMedia?->publicUrl() ? $heroMedia->responsiveUrl(1200) : route('blog.cover', $post)" :social-image-width="$heroMedia?->publicUrl() ? $heroMedia->width : 1200" :social-image-height="$heroMedia?->publicUrl() ? $heroMedia->height : 630" :social-image-alt="$heroMedia?->alt_text ?: $post->title" :structured-data="$schema">
    <section class="hc-container py-10 sm:py-16">
        <article class="mx-auto max-w-3xl">
            <nav class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider" style="color: var(--hc-text-muted)" aria-label="Breadcrumb">
                <a class="hover:text-orange-600 transition-colors" href="{{ route('blog.index') }}">Blog</a>
                @if($post->category)
                    <span aria-hidden="true" class="opacity-50">/</span>
                    <a class="hover:text-orange-600 transition-colors" href="{{ route('blog.category', $post->category) }}">{{ $post->category->name }}</a>
                @endif
            </nav>

            <h1 class="hc-display-xl mt-4 leading-tight">{{ $post->title }}</h1>
            <p class="hc-body-lg mt-5 font-medium leading-relaxed" style="color: var(--hc-text-secondary)">{{ $post->excerpt }}</p>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-b border-t py-4 text-xs font-semibold" style="border-color: var(--hc-border-subtle); color: var(--hc-text-muted)">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-orange-100 font-extrabold text-orange-800 dark:bg-orange-500/20 dark:text-orange-200">
                        {{ strtoupper(substr($post->author?->name ?? 'H', 0, 1)) }}
                    </span>
                    <div>
                        <span class="block font-bold text-sm" style="color: var(--hc-text-primary)">{{ $post->author?->name ?? 'House Chef Cyprus Editoryal Ekibi' }}</span>
                        <time datetime="{{ $post->published_at?->toIso8601String() }}">{{ $post->published_at?->format('d F Y') }}</time>
                    </div>
                </div>
                <span class="hc-v3-badge">{{ max(1, (int) ceil(str_word_count(strip_tags($post->body)) / 180)) }} dk okuma süresi</span>
            </div>

            @if($heroMedia?->publicUrl())
                <figure class="mt-8">
                    <img class="aspect-[16/9] w-full rounded-3xl object-cover shadow-sm" src="{{ $heroMedia->responsiveUrl(1280) }}" @if($heroMedia->isApprovedUnsplash()) srcset="{{ $heroMedia->responsiveUrl(768) }} 768w, {{ $heroMedia->responsiveUrl(1280) }} 1280w" sizes="(min-width: 768px) 768px, 100vw" width="{{ $heroMedia->width }}" height="{{ $heroMedia->height }}" @endif alt="{{ $heroMedia->generated_by_ai ? 'Yapay zekâ ile üretilmiş editoryal illüstrasyon: ' : '' }}{{ $heroMedia->alt_text }}" loading="lazy">
                    @if($heroMedia->generated_by_ai)
                        <figcaption class="mt-3 flex items-center gap-2 text-xs" style="color: var(--hc-text-muted)">
                            <span class="hc-v3-badge">Yapay zekâ görseli</span>
                            <span>Yapay zekâ ile üretilmiş, editoryal olarak onaylanmış görsel. Gerçek fotoğraf olarak sunulmaz.</span>
                        </figcaption>
                    @elseif($heroMedia->unsplashAttribution())
                        @php($unsplashCredit = $heroMedia->unsplashAttribution())
                        <figcaption class="mt-2 text-xs" style="color: var(--hc-text-muted)">
                            Fotoğraf: @if($unsplashCredit['photographer_url'])<a class="underline hover:text-orange-600" href="{{ $unsplashCredit['photographer_url'] }}" rel="noopener noreferrer" target="_blank">{{ $unsplashCredit['photographer'] }}</a>@else{{ $unsplashCredit['photographer'] }}@endif / Unsplash
                        </figcaption>
                    @elseif($heroMedia->attribution)
                        <figcaption class="mt-2 text-xs" style="color: var(--hc-text-muted)">{{ $heroMedia->attribution }}</figcaption>
                    @endif
                </figure>
            @else
                <div class="mt-8 flex aspect-[16/9] flex-col items-center justify-center gap-3 rounded-3xl border border-dashed p-8 text-center" style="border-color:var(--hc-border-subtle); background: var(--hc-surface-subtle)">
                    <span class="grid h-12 w-12 place-items-center rounded-full bg-orange-100 text-xl text-orange-800 dark:bg-orange-500/15 dark:text-orange-200">▤</span>
                    <strong class="font-extrabold" style="color: var(--hc-text-primary)">Bu yazı için onaylı görsel bekleniyor</strong>
                    <p class="max-w-md text-xs leading-5" style="color: var(--hc-text-muted)">Konuya özel, lisansı ve kaynağı doğrulanmış bir görsel onaylanana kadar genel veya ilgisiz fotoğraf gösterilmez.</p>
                </div>
            @endif

            <div class="hc-prose mt-10 space-y-6 text-base leading-relaxed" style="color: var(--hc-text-primary)">
                {!! $post->renderedBody() !!}
            </div>

            <x-ad-slot slot-name="in_article" class="my-8" />

            @if(!empty($post->faq_blocks))
                <section class="mt-12 pt-8 border-t" style="border-color: var(--hc-border-subtle)" aria-labelledby="sss-basligi">
                    <h2 id="sss-basligi" class="hc-heading-3">Sık sorulan sorular</h2>
                    <div class="mt-6 grid gap-4">
                        @foreach($post->faq_blocks as $faq)
                            @if(filled(data_get($faq, 'question')) && filled(data_get($faq, 'answer')))
                                <details class="hc-faq group">
                                    <summary class="hc-faq__question">{{ data_get($faq, 'question') }}</summary>
                                    <p class="hc-faq__answer">{{ data_get($faq, 'answer') }}</p>
                                </details>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

            @if(!empty($post->source_notes))
                <aside class="mt-10 rounded-2xl border p-6 text-sm" style="border-color: var(--hc-border-subtle); background: var(--hc-surface-muted)">
                    <h2 class="font-extrabold text-base mb-2" style="color: var(--hc-text-primary)">İçerik ve kaynak notu</h2>
                    <p class="leading-relaxed" style="color: var(--hc-text-secondary)">Bu yazı House Chef Cyprus Editoryal Ekibi tarafından hazırlanmıştır. Sağlık ve gıda güvenliğiyle ilgili noktalar genel bilgilendirme içindir.</p>
                </aside>
            @endif

            @if($post->recipes->isNotEmpty())
                <section class="mt-12 pt-8 border-t" style="border-color: var(--hc-border-subtle)">
                    <h2 class="hc-heading-3 mb-6">İlgili tarifler</h2>
                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach($post->recipes as $recipe)
                            <x-recipe-card-v3 :recipe="$recipe" :show-favorite="false" heading="h3" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if($relatedPosts->isNotEmpty())
                <section class="mt-12 pt-8 border-t" style="border-color: var(--hc-border-subtle)">
                    <h2 class="hc-heading-3 mb-6">İlgili rehberler</h2>
                    <div class="grid gap-5 sm:grid-cols-3">
                        @foreach($relatedPosts as $relatedPost)
                            <article class="hc-v3-card hc-v3-card--interactive flex flex-col p-5 rounded-2xl border" style="border-color: var(--hc-border-default)">
                                <span class="hc-kicker text-xs mb-2">{{ $relatedPost->category?->name ?? 'Mutfak notu' }}</span>
                                <h3 class="font-bold text-sm leading-snug line-clamp-2 mb-4" style="color: var(--hc-text-primary)">
                                    <a href="{{ route('blog.show', $relatedPost) }}" class="before:absolute before:inset-0">{{ $relatedPost->title }}</a>
                                </h3>
                                <span class="mt-auto text-xs font-bold text-orange-600">Devamını oku →</span>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <x-ad-slot slot-name="multiplex" class="mt-12" />
        </article>
    </section>
</x-layouts.site>
