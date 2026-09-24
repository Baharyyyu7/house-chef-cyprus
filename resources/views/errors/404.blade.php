@if(request()->is('en', 'en/*'))
@php(app()->setLocale('en'))
<x-layouts.site title="Page not found · House Chef Cyprus" meta-description="The page you were looking for could not be found." robots="noindex,nofollow">
    <section class="hc-container flex min-h-[60vh] items-center justify-center py-16 sm:py-24" lang="en">
        <div class="hc-v3-card max-w-2xl p-8 text-center sm:p-12 shadow-xl">
            <span class="hc-overline text-orange-600 dark:text-orange-300">404 — Page not found</span>
            <h1 class="mt-4 hc-display-lg text-[var(--hc-text-primary)]">We couldn’t find that recipe or page</h1>
            <p class="mt-4 text-base leading-relaxed text-[var(--hc-text-secondary)] sm:text-lg">It may have been moved, renamed or not published yet. Browse our English recipes instead.</p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a class="hc-v3-btn hc-v3-btn--primary min-h-12 px-6" href="{{ route('en.home') }}">
                    <x-ui.icon name="home" size="18" />Back to home
                </a>
                <a class="hc-v3-btn hc-v3-btn--outline min-h-12 px-6" href="{{ route('en.recipes.index') }}">
                    <x-ui.icon name="search" size="18" />Browse recipes
                </a>
            </div>
        </div>
    </section>
</x-layouts.site>
@else
<x-layouts.site title="Sayfa bulunamadı · House Chef Cyprus" meta-description="Aradığınız sayfa bulunamadı." robots="noindex,nofollow">
    <section class="hc-container flex min-h-[60vh] items-center justify-center py-16 sm:py-24">
        <div class="hc-v3-card max-w-2xl p-8 text-center sm:p-12 shadow-xl">
            <span class="hc-overline text-orange-600 dark:text-orange-300">404 — Sayfa Bulunamadı</span>
            <h1 class="mt-4 hc-display-lg text-[var(--hc-text-primary)]">Aradığınız tarif veya sayfa bulunamadı</h1>
            <p class="mt-4 text-base leading-relaxed text-[var(--hc-text-secondary)] sm:text-lg">Aradığınız içerik kaldırılmış, adresi değişmiş veya henüz eklenmemiş olabilir. Tarif arşivimizden ilham almaya devam edebilirsiniz.</p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a class="hc-v3-btn hc-v3-btn--primary min-h-12 px-6" href="{{ route('home') }}">
                    <x-ui.icon name="home" size="18" />Ana Sayfaya Dön
                </a>
                <a class="hc-v3-btn hc-v3-btn--outline min-h-12 px-6" href="{{ route('recipes.index') }}">
                    <x-ui.icon name="search" size="18" />Tarifleri Keşfet
                </a>
            </div>
        </div>
    </section>
</x-layouts.site>
@endif
