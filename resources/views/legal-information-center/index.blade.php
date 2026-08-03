<x-layouts.site :title="$title.' · House Chef Cyprus'" :meta-description="$introduction" :canonical-url="route('legal.center')" :structured-data="$schema">
    <section class="hc-container py-10 sm:py-14" data-legal-center>
        <div class="mx-auto max-w-5xl">
            <nav aria-label="Sayfa yolu" class="text-sm text-muted"><a class="hover:text-orange-600" href="{{ route('home') }}">Ana sayfa</a><span class="mx-2" aria-hidden="true">/</span><span aria-current="page">Yasal Bilgilendirme Merkezi</span></nav>
            <div class="mt-7 rounded-[2rem] border p-6 shadow-sm sm:p-10" style="border-color:var(--hc-border); background:linear-gradient(135deg, var(--hc-surface-raised), var(--hc-surface-base));">
                <x-brand-logo size="lg" context="legal" />
                <p class="hc-kicker mt-8">Yasal Bilgilendirme Merkezi</p>
                <h1 class="hc-title mt-3 max-w-4xl text-balance">{{ $title }}</h1>
                <p class="mt-5 text-sm font-semibold text-muted">{{ $updatedAt }}</p>
                <p class="mt-6 max-w-4xl text-base leading-8 text-muted sm:text-lg">{{ $introduction }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <button type="button" class="hc-btn hc-btn-primary" data-legal-print>Yazdır</button>
                    <a href="#kullanim-kosullari" class="hc-btn hc-btn-outline">Bölümleri incele</a>
                </div>
            </div>
        </div>

        <div class="mx-auto mt-8 grid max-w-7xl gap-8 lg:grid-cols-[17rem_minmax(0,1fr)]">
            <aside class="lg:sticky lg:top-28 lg:self-start" aria-label="Yasal bölüm menüsü">
                <div class="hc-v3-card p-4">
                    <label class="block text-sm font-extrabold" for="legal-search">Sayfada ara</label>
                    <input id="legal-search" type="search" class="hc-input mt-3" placeholder="Bölüm veya metin ara" autocomplete="off" data-legal-search>
                    <p class="mt-2 text-xs text-muted" data-legal-search-status aria-live="polite"></p>
                    <label class="mt-5 block text-sm font-extrabold lg:hidden" for="legal-section-select">Bölüm seçin</label>
                    <select id="legal-section-select" class="hc-input mt-2 w-full lg:hidden" data-legal-section-select>
                        @foreach ($sections as $section)
                            <option value="{{ $section['id'] }}">{{ $section['title'] }}</option>
                        @endforeach
                    </select>
                    <nav class="mt-5 hidden max-h-[calc(100vh-16rem)] gap-1 overflow-y-auto pr-1 lg:grid" aria-label="Yasal bölüm bağlantıları">
                        @foreach ($sections as $section)
                            <a href="#{{ $section['id'] }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-muted transition hover:bg-orange-50 hover:text-orange-700 dark:hover:bg-orange-500/10 dark:hover:text-orange-200" data-legal-nav-link data-section-id="{{ $section['id'] }}">{{ $section['title'] }}</a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            <div class="min-w-0 space-y-5">
                @foreach ($sections as $section)
                    <article id="{{ $section['id'] }}" class="hc-v3-card scroll-mt-28 p-6 sm:p-8" data-legal-section tabindex="-1">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <h2 class="font-display text-2xl font-semibold tracking-tight sm:text-3xl">{{ $section['title'] }}</h2>
                            <button type="button" class="hc-btn hc-btn-outline !min-h-10 !rounded-xl px-3 py-2 text-sm" data-copy-section data-section-id="{{ $section['id'] }}" aria-label="{{ $section['title'] }} bağlantısını kopyala">Bağlantıyı kopyala</button>
                        </div>
                        <div class="mt-6 space-y-5 text-[0.98rem] leading-8 text-muted sm:text-base">
                            @foreach ($section['paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </article>
                @endforeach
                <p class="sr-only" data-legal-copy-status aria-live="polite"></p>
            </div>
        </div>
    </section>
</x-layouts.site>
