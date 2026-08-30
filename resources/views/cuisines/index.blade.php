<x-layouts.site
    title="Mutfaklar · Kıbrıs, Türk ve Akdeniz Tarifleri · House Chef Cyprus"
    meta-description="Kıbrıs, Türk, Akdeniz ve daha fazlası. Her mutfaktan doğrulanmış tarifler; ölçüler ve süreler görünür."
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <p class="hc-kicker">Dünya ve Bölge Lezzetleri</p>
        <h1 class="mt-3 font-display text-4xl font-semibold">Mutfaklar</h1>

        <p class="hc-body-lg mt-4 max-w-2xl text-muted">
            {{-- Yalnız arkasında yeterli tarif olan mutfaklar listeleniyor;
                 boş bir sayfaya bağlantı vermek ziyaretçiyi de arama motorunu
                 da yanıltır. --}}
            Kıbrıs, Türk, Akdeniz ve ötesi. Arkasında yeterli sayıda doğrulanmış tarif bulunan geleneksel ve modern mutfaklar.
        </p>

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach ($mutfaklar as $mutfak)
                <a class="hc-v3-card group flex items-center justify-between p-6 transition-all duration-300 hover:-translate-y-1 hover:border-[var(--hc-brand-primary)]" href="{{ route('cuisines.show', $mutfak->slug) }}">
                    <div>
                        <h2 class="font-display text-xl font-semibold transition-colors group-hover:text-[var(--hc-brand-primary)]">{{ $mutfak->name }}</h2>
                        <p class="hc-caption mt-1">{{ $mutfak->recipes_count }} doğrulanmış tarif</p>
                    </div>
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-charcoal-100 text-charcoal-700 transition-transform group-hover:translate-x-1 dark:bg-charcoal-800 dark:text-charcoal-200" aria-hidden="true">
                        ›
                    </span>
                </a>
            @endforeach
        </div>

        @if ($mutfaklar->isEmpty())
            <p class="hc-body-sm mt-8 text-muted">Henüz yeterli tarifi olan mutfak yok.</p>
        @endif
    </section>
</x-layouts.site>
