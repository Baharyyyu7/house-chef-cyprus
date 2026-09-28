@php($fallbackImage = asset(ltrim((string) config('media_excellence.blog_fallback_url'), '/')))
<x-layouts.site :title="$metadata['title']" :meta-description="$metadata['description']" :canonical-url="$metadata['canonical']" :robots="$metadata['robots']" :social-image="$heroMedia?->publicUrl() ? $heroMedia->responsiveUrl(1200) : $fallbackImage" :social-image-width="$heroMedia?->publicUrl() ? $heroMedia->width : 1600" :social-image-height="$heroMedia?->publicUrl() ? $heroMedia->height : 1200" :social-image-alt="$heroMedia?->alt_text ?: $post->translated('title')" :structured-data="$schema" :seo-content="$post">
    <section class="hc-container py-10 sm:py-16">
        <article class="mx-auto max-w-3xl" data-hc-okuma>
            <nav class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider" style="color: var(--hc-text-muted)" aria-label="Breadcrumb">
                <a class="hover:text-orange-600 transition-colors" href="{{ route('blog.index') }}">Blog</a>
                @if($post->category)
                    <span aria-hidden="true" class="opacity-50">/</span>
                    <a class="hover:text-orange-600 transition-colors" href="{{ route('blog.category', $post->category) }}">{{ $post->category->name }}</a>
                @endif
            </nav>

            <h1 class="hc-display-xl mt-4 leading-tight">{{ $post->translated('title') }}</h1>
            <p class="hc-body-lg mt-5 font-medium leading-relaxed" style="color: var(--hc-text-secondary)">{{ $post->translated('excerpt') }}</p>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-b border-t py-4 text-xs font-semibold" style="border-color: var(--hc-border-subtle); color: var(--hc-text-muted)">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-orange-100 font-extrabold text-orange-800 dark:bg-orange-500/20 dark:text-orange-200">
                        {{ strtoupper(substr($post->author?->name ?? 'H', 0, 1)) }}
                    </span>
                    <div>
                        <span class="block font-bold text-sm" style="color: var(--hc-text-primary)">{{ $post->author?->name ?? __('blog.show.author_default') }}</span>
                        <time datetime="{{ $post->published_at?->toIso8601String() }}">{{ $post->published_at?->format('d F Y') }}</time>
                    </div>
                </div>
                <span class="hc-v3-badge">{{ __('blog.show.reading_time', ['minutes' => max(1, (int) ceil(str_word_count(strip_tags($post->translated('body'))) / 180))]) }}</span>
            </div>

            @if($heroMedia?->publicUrl())
                <figure class="mt-8">
                    <img class="aspect-[16/9] w-full rounded-3xl object-cover shadow-sm" src="{{ $heroMedia->responsiveUrl(1280) }}" @if($heroMedia->isApprovedUnsplash()) srcset="{{ $heroMedia->responsiveUrl(768) }} 768w, {{ $heroMedia->responsiveUrl(1280) }} 1280w" sizes="(min-width: 768px) 768px, 100vw" width="{{ $heroMedia->width }}" height="{{ $heroMedia->height }}" @endif alt="{{ $heroMedia->generated_by_ai ? 'Yapay zekâ ile üretilmiş editoryal illüstrasyon: ' : '' }}{{ $heroMedia->alt_text }}" loading="lazy">
                    @if($heroMedia->generated_by_ai)
                        <figcaption class="mt-3 flex items-center gap-2 text-xs" style="color: var(--hc-text-muted)">
                            <span class="hc-v3-badge">{{ __('blog.show.ai_image_badge') }}</span>
                            <span>{{ __('blog.show.ai_image_note') }}</span>
                        </figcaption>
                    @elseif($heroMedia->unsplashAttribution())
                        @php($unsplashCredit = $heroMedia->unsplashAttribution())
                        <figcaption class="mt-2 text-xs" style="color: var(--hc-text-muted)">
                            {{ __('blog.show.photo_credit') }} @if($unsplashCredit['photographer_url'])<a class="underline hover:text-orange-600" href="{{ $unsplashCredit['photographer_url'] }}" rel="noopener noreferrer" target="_blank">{{ $unsplashCredit['photographer'] }}</a>@else{{ $unsplashCredit['photographer'] }}@endif / Unsplash
                        </figcaption>
                    @elseif($heroMedia->attribution)
                        <figcaption class="mt-2 text-xs" style="color: var(--hc-text-muted)">{{ $heroMedia->attribution }}</figcaption>
                    @endif
                </figure>
            @else
                <figure class="mt-8 overflow-hidden rounded-3xl shadow-sm">
                    <img class="aspect-[16/9] w-full object-cover" src="{{ $fallbackImage }}" alt="" width="1600" height="1200" loading="lazy" aria-hidden="true">
                </figure>
            @endif

            <div class="hc-prose mt-10 space-y-6 text-base leading-relaxed" style="color: var(--hc-text-primary)">
                {!! $post->renderedBody() !!}
            </div>

            <div class="mt-8 border-t pt-4" style="border-color: var(--hc-border-subtle)">
                <x-social-share :title="$post->title" :description="$post->excerpt" :image="$heroMedia?->publicUrl() ? $heroMedia->responsiveUrl(1200) : null" />
            </div>

            {{-- Şefin Tavsiyesi: Profesyonel Mutfak Ekipmanı Affiliate Kartları --}}
            <x-recipe-equipment-affiliate :post="$post" heading="h2" />

            {{-- Kuzey Kıbrıs Özel Villa Şefi & Ada Catering Lead Kartı --}}
            @if(app(\App\Services\SiteFeatureService::class)->enabled('services'))<x-recipe-chef-lead-card :post="$post" />@endif

            <x-ad-slot slot-name="in_article" class="my-8" />

            {{-- Dijital Mutfak E-Kitabı Tanıtım & Satış Vitrini --}}
            <x-ebook-showcase-card class="mt-8" />

            @if(!empty($post->faq_blocks))
                <section class="mt-12 pt-8 border-t" style="border-color: var(--hc-border-subtle)" aria-labelledby="sss-basligi">
                    <h2 id="sss-basligi" class="hc-heading-3">{{ __('blog.show.faq_title') }}</h2>
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
                    <h2 class="font-extrabold text-base mb-2" style="color: var(--hc-text-primary)">{{ __('blog.show.source_title') }}</h2>
                    <p class="leading-relaxed" style="color: var(--hc-text-secondary)">{{ __('blog.show.source_body') }}</p>
                </aside>
            @endif

            @php($recipesToDisplay = $post->recipes->isNotEmpty() ? $post->recipes : ($suggestedRecipes ?? collect()))
            {{-- Kıbrıs konulu yazı, Kıbrıs hub sayfasına bağlanır: blog arşivi hub otoritesini besler. Sorgusuz tespit (ek sorgu yok). --}}
            @php($kibrisYazisi = str_contains(mb_strtolower(($post->translated('title') ?? '').' '.($post->translated('excerpt') ?? '').' '.($post->category?->name ?? ''), 'utf-8'), 'kıbrıs') || str_contains(mb_strtolower(($post->translated('title') ?? '').' '.($post->translated('excerpt') ?? ''), 'utf-8'), 'hellim'))
            @if($kibrisYazisi)
                <section class="mt-12 rounded-2xl border p-5 flex flex-wrap items-center justify-between gap-4" style="border-color: var(--hc-border-default)">
                    <div>
                        <p class="hc-kicker text-xs">Kıbrıs Mutfağı</p>
                        <p class="font-bold text-base mt-1" style="color: var(--hc-text-primary)">Doğrulanmış tariflerin tamamı tek koleksiyonda</p>
                    </div>
                    <a class="hc-v3-btn hc-v3-btn--primary" href="{{ route('cuisines.show', 'kibris-mutfagi') }}">Kıbrıs Yemekleri &rarr;</a>
                </section>
            @endif
             @if($recipesToDisplay->isNotEmpty())
                <section class="mt-12 pt-8 border-t" style="border-color: var(--hc-border-subtle)">
                    <h2 class="hc-heading-3 mb-6">{{ __('blog.show.related_recipes') }}</h2>
                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach($recipesToDisplay as $recipe)
                            <x-recipe-card-v3 :recipe="$recipe" :show-favorite="false" heading="h3" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if($relatedPosts->isNotEmpty())
                <section class="mt-12 pt-8 border-t" style="border-color: var(--hc-border-subtle)">
                    <h2 class="hc-heading-3 mb-6">{{ __('blog.show.related_guides') }}</h2>
                    <div class="grid gap-5 sm:grid-cols-3">
                        @foreach($relatedPosts as $relatedPost)
                            <article class="hc-v3-card hc-v3-card--interactive flex flex-col p-5 rounded-2xl border" style="border-color: var(--hc-border-default)">
                                <span class="hc-kicker text-xs mb-2">{{ $relatedPost->category?->name ?? __('blog.show.kitchen_note') }}</span>
                                <h3 class="font-bold text-sm leading-snug line-clamp-2 mb-4" style="color: var(--hc-text-primary)">
                                    <a href="{{ route('blog.show', $relatedPost) }}" class="before:absolute before:inset-0">{{ $relatedPost->translated('title') }}</a>
                                </h3>
                                <span class="mt-auto text-xs font-bold text-orange-600">{{ __('blog.show.read_more') }} &rarr;</span>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <x-ad-slot slot-name="multiplex" class="mt-12" />
        </article>
    </section>
</x-layouts.site>
