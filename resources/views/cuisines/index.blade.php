<x-layouts.site
    title="Mutfaklar · Kıbrıs, Türk ve Akdeniz Tarifleri · House Chef Cyprus"
    meta-description="Kıbrıs, Türk, Akdeniz ve daha fazlası. Her mutfaktan doğrulanmış tarifler; ölçüler ve süreler görünür."
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <h1 class="font-display text-4xl font-semibold">Mutfaklar</h1>

        <p class="hc-body-lg mt-4 max-w-2xl text-muted">
            {{-- Yalnız arkasında yeterli tarif olan mutfaklar listeleniyor;
                 boş bir sayfaya bağlantı vermek ziyaretçiyi de arama motorunu
                 da yanıltır. --}}
            Arkasında yeterli sayıda doğrulanmış tarif bulunan mutfaklar.
        </p>

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($mutfaklar as $mutfak)
                <a class="hc-v3-card p-5 transition hover:-translate-y-0.5" href="{{ route('cuisines.show', $mutfak->slug) }}">
                    <p class="font-display text-xl font-semibold">{{ $mutfak->name }}</p>
                    <p class="hc-caption mt-1">{{ $mutfak->recipes_count }} doğrulanmış tarif</p>
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.site>
