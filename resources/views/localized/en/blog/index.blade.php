<x-layouts.site :title="$metadata['title']" :meta-description="$metadata['description']" :canonical-url="$metadata['canonical']" :robots="$metadata['robots']" :alternates="$alternates">
    <section class="hc-container hc-page">
        <div class="max-w-3xl border-b pb-8" style="border-color: var(--hc-line)">
            <p class="hc-kicker">Kitchen guides</p>
            <h1 class="hc-display-lg mt-3">The House Chef Cyprus blog</h1>
            <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">Practical guides to Cypriot and Mediterranean home cooking — ingredients, techniques, food safety and seasonal eating.</p>
        </div>
        <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($posts as $translation)
                @php($hero = $translation->post?->heroMedia?->isApprovedRealMedia() ? $translation->post->heroMedia : null)
                <article class="hc-v3-card hc-v3-card--interactive relative flex flex-col overflow-hidden">
                    @if($hero && $hero->publicUrl())<img class="aspect-[16/9] w-full object-cover" src="{{ $hero->responsiveUrl(800) ?: $hero->publicUrl() }}" alt="" loading="lazy" width="800" height="450">@endif
                    <div class="flex flex-1 flex-col p-5">
                        <p class="hc-overline" style="color: var(--hc-text-muted)">{{ $translation->category_label ?: 'Guide' }}</p>
                        <h2 class="hc-heading-3 mt-2"><a class="before:absolute before:inset-0" href="{{ route('en.blog.show', $translation->slug) }}">{{ $translation->title }}</a></h2>
                        <p class="hc-body-sm mt-3 line-clamp-3" style="color: var(--hc-text-secondary)">{{ $translation->excerpt }}</p>
                        @if($translation->post?->published_at)<p class="mt-auto pt-4 text-xs" style="color: var(--hc-text-muted)"><time datetime="{{ $translation->post->published_at->toDateString() }}">{{ $translation->post->published_at->format('j F Y') }}</time></p>@endif
                    </div>
                </article>
            @empty
                <div class="hc-v3-empty col-span-full"><p class="hc-heading-3">English guides are on their way.</p><a class="hc-v3-btn hc-v3-btn--outline mt-5" href="{{ route('blog.index') }}" hreflang="tr">Read the Turkish blog</a></div>
            @endforelse
        </div>
        <div class="mt-10">{{ $posts->links() }}</div>
    </section>
</x-layouts.site>
