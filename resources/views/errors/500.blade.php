<x-layouts.site :title="__('ux.500_title').' · House Chef Cyprus'" :meta-description="__('ux.500_heading')" robots="noindex,nofollow">
    <section class="hc-container flex min-h-[60vh] items-center justify-center py-16 sm:py-24">
        <div class="hc-v3-card max-w-2xl p-8 text-center sm:p-12 shadow-xl">
            <span class="hc-overline text-orange-600 dark:text-orange-300">500 - {{ __('ux.500_title') }}</span>
            <h1 class="mt-4 hc-display-lg text-[var(--hc-text-primary)]">{{ __('ux.500_heading') }}</h1>
            <p class="mt-4 text-base leading-relaxed text-[var(--hc-text-secondary)] sm:text-lg">{{ __('ux.500_intro') }}</p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a class="hc-v3-btn hc-v3-btn--primary min-h-12 px-6" href="{{ route('home') }}">
                    <x-ui.icon name="home" size="18" />{{ __('ux.home') }}
                </a>
                <button onclick="window.location.reload()" class="hc-v3-btn hc-v3-btn--outline min-h-12 px-6">
                    <x-ui.icon name="refresh-cw" size="18" />{{ __('ux.reload') }}
                </button>
            </div>
        </div>
    </section>
</x-layouts.site>
