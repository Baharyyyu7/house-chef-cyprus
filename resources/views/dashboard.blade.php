<x-app-layout>
    <x-slot name="header">
        <p class="hc-kicker">Hesabım</p>
        <h1 class="mt-2 hc-display-xl">Mutfağınıza hoş geldiniz, {{ auth()->user()->name }}.</h1>
    </x-slot>

    <section class="grid gap-5 md:grid-cols-3">
        <a href="{{ route('pantry.index') }}" class="hc-v3-card p-6 transition hover:shadow-lg focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:outline-none dark:hover:border-gold-500/50">
            <p class="text-2xl text-gold-600">⌘</p>
            <h2 class="mt-3 hc-heading-3">Malzeme dolabım</h2>
            <p class="mt-2 hc-body-sm text-[var(--hc-text-secondary)]">Elinizdekileri güncelleyin ve eşleşmeleri iyileştirin.</p>
        </a>
        <a href="{{ route('favorites.index') }}" class="hc-v3-card p-6 transition hover:shadow-lg focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:outline-none dark:hover:border-gold-500/50">
            <p class="text-2xl text-gold-600">♡</p>
            <h2 class="mt-3 hc-heading-3">Favorilerim</h2>
            <p class="mt-2 hc-body-sm text-[var(--hc-text-secondary)]">Sevdiğiniz tariflere kolayca geri dönün.</p>
        </a>
        <a href="{{ route('ingredients.explore') }}" class="hc-v3-card p-6 transition hover:shadow-lg focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:outline-none dark:hover:border-gold-500/50">
            <p class="text-2xl text-gold-600">↗</p>
            <h2 class="mt-3 hc-heading-3">Ne pişirebilirim?</h2>
            <p class="mt-2 hc-body-sm text-[var(--hc-text-secondary)]">Dolabınıza göre gerçekçi tarif önerileri alın.</p>
        </a>
    </section>

    <section class="hc-v3-card mt-8 p-6">
        <h2 class="hc-heading-3">Hızlı başlangıç</h2>
        <ol class="mt-4 grid gap-3 text-sm text-[var(--hc-text-secondary)] sm:grid-cols-3">
            <li><strong class="text-[var(--hc-text-primary)]">1.</strong> Dolabınıza malzeme ekleyin.</li>
            <li><strong class="text-[var(--hc-text-primary)]">2.</strong> Tarifleri eşleştirin.</li>
            <li><strong class="text-[var(--hc-text-primary)]">3.</strong> Pişirme modunda ilerleyin.</li>
        </ol>
    </section>
</x-app-layout>
