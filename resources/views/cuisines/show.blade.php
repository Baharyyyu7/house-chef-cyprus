<x-layouts.site
    :title="$mutfak->name.' Tarifleri · '.$tarifler->count().' Doğrulanmış Tarif · House Chef Cyprus'"
    :meta-description="$mutfak->name.' mutfağından '.$tarifler->count().' doğrulanmış tarif: ölçüler, süreler ve adım adım anlatım.'"
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <nav class="hc-body-sm text-muted" aria-label="Yol">
            <a class="font-semibold text-gold-700" href="{{ route('cuisines.index') }}">Mutfaklar</a>
            <span aria-hidden="true"> › </span>{{ $mutfak->name }}
        </nav>

        {{-- Başlık arama niyetini birebir karşılıyor: insanlar "kıbrıs mutfağı
             tarifleri" diye arıyor. --}}
        <h1 class="mt-3 font-display text-4xl font-semibold">{{ $mutfak->name }} Tarifleri</h1>

        <p class="hc-body-lg mt-4 max-w-2xl text-muted">
            {{ $mutfak->name }} mutfağından {{ $tarifler->count() }} doğrulanmış tarif.
            Her tarifte ölçüler, süreler ve besin değerleri görünür.
        </p>

        @if (filled($mutfak->description))
            <p class="hc-body-sm mt-4 max-w-2xl">{{ $mutfak->description }}</p>
        @endif

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($tarifler as $tarif)
                <x-recipe-card-v3 :recipe="$tarif" :show-favorite="false" />
            @endforeach
        </div>
    </section>
</x-layouts.site>
