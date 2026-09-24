<x-layouts.site :title="__('ux.404_title').' · House Chef Cyprus'" :meta-description="__('ux.404_heading')" robots="noindex,nofollow">
    <section class="hc-container flex min-h-[60vh] items-center justify-center py-16 sm:py-24">
        <div class="hc-v3-card max-w-2xl p-8 text-center sm:p-12 shadow-xl">
            <span class="hc-overline text-orange-600 dark:text-orange-300">404 - {{ __('ux.404_title') }}</span>
            <h1 class="mt-4 hc-display-lg text-[var(--hc-text-primary)]">{{ __('ux.404_heading') }}</h1>
            <p class="mt-4 text-base leading-relaxed text-[var(--hc-text-secondary)] sm:text-lg">{{ __('ux.404_intro') }}</p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a class="hc-v3-btn hc-v3-btn--primary min-h-12 px-6" href="{{ route('home') }}">
                    <x-ui.icon name="home" size="18" />{{ __('ux.home') }}
                </a>
                <a class="hc-v3-btn hc-v3-btn--outline min-h-12 px-6" href="{{ route('recipes.index') }}">
                    <x-ui.icon name="search" size="18" />{{ __('ux.recipes') }}
                </a>
            </div>
        </div>
    </section>
</x-layouts.site>
