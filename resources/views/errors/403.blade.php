<x-layouts.site :title="__('ux.403_title').' · House Chef Cyprus'" :meta-description="__('ux.403_heading')" robots="noindex,nofollow">
    <section class="hc-container flex min-h-[55vh] items-center py-16 sm:py-24">
        <div class="hc-v3-card max-w-2xl p-8 sm:p-12">
            <p class="hc-kicker">403</p>
            <h1 class="mt-4 hc-display-lg text-[var(--hc-text-primary)]">{{ __('ux.403_heading') }}</h1>
            <p class="mt-5 max-w-xl leading-7 text-[var(--hc-text-secondary)]">{{ __('ux.403_intro') }}</p>
            <div class="mt-8 flex flex-wrap gap-3"><a class="hc-v3-btn hc-v3-btn--primary" href="{{ route('home') }}">{{ __('ux.home') }}</a><a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('recipes.index') }}">{{ __('ux.recipes') }}</a></div>
        </div>
    </section>
</x-layouts.site>
