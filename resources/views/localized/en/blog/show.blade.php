<x-layouts.site :title="$metadata['title']" :meta-description="$metadata['description']" :canonical-url="$metadata['canonical']" :robots="$metadata['robots']" :alternates="$alternates" :social-image="$hero ? ($hero->responsiveUrl(1200) ?: $hero->publicUrl()) : null" :social-image-width="$hero?->width" :social-image-height="$hero?->height" :social-image-alt="$hero?->alt_text" :structured-data="$schema">
    <section class="hc-container py-12 sm:py-16">
        <article class="mx-auto max-w-3xl">
            <nav aria-label="Breadcrumb" class="text-sm" style="color: var(--hc-text-muted)"><a href="{{ route('en.home') }}">Home</a> <span aria-hidden="true">/</span> <a href="{{ route('en.blog.index') }}">Blog</a>@if($translation->category_label) <span aria-hidden="true">/</span> {{ $translation->category_label }}@endif</nav>
            <h1 class="hc-display-xl mt-5">{{ $translation->title }}</h1>
            <p class="hc-body-lg mt-5" style="color: var(--hc-text-secondary)">{{ $translation->excerpt }}</p>
            <p class="mt-5 text-sm" style="color: var(--hc-text-muted)">@if($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time> · @endif House Chef Cyprus editorial team</p>
            @if($hero && $hero->publicUrl())
                <img class="mt-8 aspect-[16/9] w-full rounded-3xl object-cover" src="{{ $hero->responsiveUrl(1280) ?: $hero->publicUrl() }}" alt="{{ $hero->alt_text }}" loading="lazy" @if($hero->width) width="{{ $hero->width }}" height="{{ $hero->height }}" @endif>
                @if($hero->unsplashAttribution())@php($credit = $hero->unsplashAttribution())<p class="mt-2 text-xs" style="color: var(--hc-text-muted)">Photo: @if($credit['photographer_url'])<a class="underline" href="{{ $credit['photographer_url'] }}" rel="noopener noreferrer" target="_blank">{{ $credit['photographer'] }}</a>@else{{ $credit['photographer'] }}@endif / Unsplash</p>@elseif($hero->attribution)<p class="mt-2 text-xs" style="color: var(--hc-text-muted)">{{ $hero->attribution }}</p>@endif
            @endif
            <div class="prose prose-stone mt-10 max-w-none dark:prose-invert">
                @foreach(preg_split('/\n\s*\n/', trim((string) $translation->body)) as $block)
                    @php($lines = preg_split('/\n/', $block))
                    @if(str_starts_with($block, '## '))<h2>{{ substr($block, 3) }}</h2>@elseif(collect($lines)->every(fn ($line) => str_starts_with($line, '- ')))<ul>@foreach($lines as $line)<li>{{ substr($line, 2) }}</li>@endforeach</ul>@else<p>{!! nl2br(e($block)) !!}</p>@endif
                @endforeach
            </div>
            @if(!empty($translation->faq_blocks))
                <section class="mt-10"><h2 class="hc-heading-3">Frequently asked questions</h2><div class="mt-4 space-y-3">@foreach($translation->faq_blocks as $faq)<details class="hc-v3-card p-5"><summary class="cursor-pointer font-extrabold">{{ $faq['question'] }}</summary><p class="mt-3 text-sm leading-6" style="color: var(--hc-text-secondary)">{{ $faq['answer'] }}</p></details>@endforeach</div></section>
            @endif
            <aside class="mt-10 rounded-2xl border border-[color:var(--hc-border-subtle)] bg-[color:var(--hc-surface-muted)] p-5 text-sm"><h2 class="font-bold">About this guide</h2><p class="mt-2" style="color: var(--hc-text-secondary)">Written by the House Chef Cyprus editorial team. Health and food-safety points are general information; follow product labels and local guidance.</p></aside>
            @if($relatedRecipes->isNotEmpty())
                <section class="mt-12"><h2 class="hc-heading-3">Recipes from this guide</h2><div class="mt-5 grid gap-5 sm:grid-cols-2">@foreach($relatedRecipes as $related)<x-localized.recipe-card :translation="$related" />@endforeach</div></section>
            @endif
            @if($relatedPosts->isNotEmpty())
                <section class="mt-12"><h2 class="hc-heading-3">More guides</h2><div class="mt-5 grid gap-4 sm:grid-cols-3">@foreach($relatedPosts as $relatedPost)<a class="hc-v3-card hc-v3-card--interactive p-5 font-bold" href="{{ route('en.blog.show', $relatedPost->slug) }}">{{ $relatedPost->title }}</a>@endforeach</div></section>
            @endif
        </article>
    </section>
</x-layouts.site>
