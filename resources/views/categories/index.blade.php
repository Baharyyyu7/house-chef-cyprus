<x-layouts.site title="Tarif Kategorileri · House Chef Cyprus" meta-description="Ana yemek, çorba, tatlı, hamur işi ve daha fazla kategoride doğrulanmış tarifleri keşfedin." :canonical-url="$seoIndexability['canonical']" :robots="$seoIndexability['robots']">
    <section class="hc-container py-14">
        <p class="hc-kicker">Tarif arşivi</p>
        <h1 class="mt-3 font-display text-4xl font-semibold">Tarif kategorileri</h1>
        <p class="hc-body-lg mt-4 max-w-2xl text-muted">Yalnız arkasında en az dört doğrulanmış tarif bulunan kategoriler burada yer alır.</p>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach($kategoriler as $kategori)
                <a class="hc-v3-card group flex items-center justify-between p-6" href="{{ route('categories.show', $kategori->slug) }}">
                    <span><strong class="font-display text-xl">{{ $kategori->name }}</strong><small class="mt-1 block text-muted">{{ $kategori->recipes_count }} doğrulanmış tarif</small></span>
                    <span aria-hidden="true">→</span>
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.site>
