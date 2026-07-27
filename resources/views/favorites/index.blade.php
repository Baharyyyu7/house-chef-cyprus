<x-layouts.site title="Favorilerim">
    <section class="mx-auto max-w-7xl px-5 py-12">
        <h1 class="text-4xl font-black">Favorilerim</h1>
        <p class="mt-2 text-stone-600">Daha sonra tekrar pişirmek istediğiniz tarifler.</p>

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($recipes as $recipe)
                <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-stone-200">
                    <div class="flex h-36 items-end bg-gradient-to-br from-orange-200 via-amber-100 to-lime-100 p-5">
                        <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-bold">{{ $recipe->cuisine?->name ?? 'Tarif' }}</span>
                    </div>
                    <div class="p-5">
                        <div class="flex justify-between text-sm text-stone-500"><span>{{ $recipe->totalMinutes() }} dk</span><span>{{ $recipe->difficulty }}</span></div>
                        <h2 class="mt-2 text-xl font-black">{{ $recipe->title }}</h2>
                        <p class="mt-2 line-clamp-2 text-sm text-stone-600">{{ $recipe->excerpt }}</p>
                        <a class="mt-5 inline-block font-bold text-orange-700" href="{{ route('recipes.show', $recipe) }}">Tarifi incele →</a>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl bg-white p-8 text-stone-600 shadow-sm">
                    Henüz favori tarifiniz yok. Tarif detaylarından favori ekleyebilirsiniz.
                    <a class="ml-1 font-bold text-orange-700 underline" href="{{ route('recipes.index') }}">Tarifleri keşfedin.</a>
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $recipes->links() }}</div>
    </section>
</x-layouts.site>
