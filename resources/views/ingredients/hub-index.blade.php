<x-layouts.site
    title="Malzemeye Göre Tarifler · House Chef Cyprus"
    meta-description="Elinizdeki malzemeden yola çıkın: hellim, patlıcan, bulgur ve daha fazlası için doğrulanmış tarifler."
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <p class="hc-kicker">Malzemeden başla</p>
        <h1 class="mt-3 font-display text-4xl font-semibold">Malzemeye göre tarifler</h1>
        <p class="hc-body-lg mt-4 max-w-2xl text-muted">
            Dolabınızda ne varsa oradan başlayın. Her malzeme için ölçüleri doğrulanmış
            tarifleri bir arada topladık.
        </p>

        {{-- Sayfası olan malzemeler eşikten geçenler. Bir malzemenin arkasında
             yeterli tarif yoksa listede de görünmez: listede olup açılmayan
             bağlantı hem ziyaretçiyi hem taramayı boşa yorar. --}}
        <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($malzemeler as $malzeme)
                <a class="hc-card flex items-center justify-between gap-4 transition hover:border-[var(--hc-brand-primary)]"
                   href="{{ route('ingredients.hub.show', $malzeme->slug) }}">
                    <span class="font-semibold">{{ $malzeme->name }}</span>
                    <span class="hc-body-sm text-muted">{{ $malzeme->tarif_sayisi }} tarif</span>
                </a>
            @endforeach
        </div>

        @if ($malzemeler->isEmpty())
            <p class="hc-body-sm mt-8 text-muted">Henüz yeterli tarifi olan malzeme yok.</p>
        @endif
    </section>
</x-layouts.site>
