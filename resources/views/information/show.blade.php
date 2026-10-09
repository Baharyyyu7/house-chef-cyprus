<x-layouts.site :title="$page['title'].' · House Chef Cyprus'" :meta-description="$page['lead']" :canonical-url="$canonical" :structured-data="$schema">
    <section class="hc-container py-10 sm:py-16">
        <div class="max-w-3xl">
            <nav aria-label="{{ __('info.breadcrumb') }}" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted mb-4">
                <a class="hover:text-orange-600 transition-colors" href="{{ route('home') }}">{{ __('info.home') }}</a>
                <span class="opacity-50" aria-hidden="true">/</span>
                <span aria-current="page">{{ $page['title'] }}</span>
            </nav>
            <p class="hc-kicker">{{ $page['eyebrow'] }}</p>
            <h1 class="mt-2 hc-display-xl leading-tight">{{ $page['title'] }}</h1>
            <p class="mt-4 hc-body-lg text-[var(--hc-text-secondary)] font-medium leading-relaxed">{{ $page['lead'] }}</p>
        </div>

        <div class="mt-10 grid gap-6 md:grid-cols-2">
            @foreach($page['sections'] as $section)
                <article class="hc-v3-card p-6 sm:p-7 flex flex-col justify-between">
                    <div>
                        <h2 class="hc-heading-3 text-lg font-extrabold" style="color: var(--hc-text-primary)">{{ $section['title'] }}</h2>
                        <p class="mt-3 hc-body-sm leading-relaxed" style="color: var(--hc-text-secondary)">{{ $section['body'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-12 rounded-3xl border p-8 flex flex-col sm:flex-row items-center justify-between gap-6" style="border-color: var(--hc-border-subtle); background: var(--hc-surface-subtle)">
            <div>
                <h3 class="font-extrabold text-base" style="color: var(--hc-text-primary)">{{ __('info.more_questions') }}</h3>
                <p class="text-sm mt-1" style="color: var(--hc-text-secondary)">{{ __('info.more_text') }}</p>
            </div>
            <a class="hc-v3-btn hc-v3-btn--primary whitespace-nowrap" href="{{ route('contact.index') }}">{{ __('contact.write_us') }} →</a>
        </div>
    </section>
</x-layouts.site>
