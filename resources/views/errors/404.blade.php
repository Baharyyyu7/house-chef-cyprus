<x-layouts.site title="Sayfa bulunamadı · House Chef Cyprus" meta-description="İstenen sayfa bulunamadı." robots="noindex,nofollow">
    <section class="hc-container flex min-h-[55vh] items-center py-16 sm:py-24">
        <div class="hc-v3-card max-w-2xl p-8 sm:p-12">
            <p class="hc-kicker">404</p>
            <h1 class="mt-4 text-4xl font-extrabold tracking-[-.04em] text-ink dark:text-stone-50 sm:text-5xl">Aradığınız sayfa burada değil.</h1>
            <p class="mt-5 max-w-xl leading-7 text-warm dark:text-stone-400">Adres değişmiş olabilir. Tarif koleksiyonuna veya ana sayfaya güvenle dönebilirsiniz.</p>
            <div class="mt-8 flex flex-wrap gap-3"><a class="hc-btn hc-btn-primary" href="{{ route('home') }}">Ana sayfaya dön</a><a class="hc-btn hc-btn-outline" href="{{ route('recipes.index') }}">Tarifleri keşfet</a></div>
        </div>
    </section>
</x-layouts.site>
