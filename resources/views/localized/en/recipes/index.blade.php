<x-layouts.site :title="$metadata['title']" :meta-description="$metadata['description']" :canonical-url="$metadata['canonical']" :robots="$metadata['robots']" :alternates="$alternates">
    <section class="hc-container hc-page hc-recipe-archive">
        <div class="grid gap-6 border-b pb-8 sm:grid-cols-[1fr_auto] sm:items-end" style="border-color: var(--hc-line)">
            <div class="max-w-3xl">
                <p class="hc-kicker">Recipe collection</p>
                <h1 class="hc-display-lg mt-3">Cypriot &amp; Mediterranean recipes</h1>
                <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">Home recipes from Cyprus, Turkey and the wider Mediterranean, translated and adapted for English-speaking cooks with metric measures and clear steps.</p>
            </div>
        </div>

        <form class="hc-v3-card mt-7 grid gap-4 p-4 sm:grid-cols-[minmax(0,1fr)_14rem_auto] sm:items-end sm:p-5" action="{{ route('en.recipes.index') }}" role="search" aria-label="Recipe filters">
            <div><label class="hc-v3-field-label" for="recipe-search">Search recipes</label><input id="recipe-search" class="hc-v3-input" name="q" value="{{ $search }}" placeholder="Dish, ingredient or cuisine"></div>
            <div><label class="hc-v3-field-label" for="category">Course</label><select id="category" class="hc-v3-input" name="category"><option value="">All courses</option>@foreach($categories as $category)<option value="{{ $category }}" @selected($activeCategory === $category)>{{ $category }}</option>@endforeach</select></div>
            <button class="hc-v3-btn hc-v3-btn--primary" type="submit">Show recipes</button>
        </form>

        <p class="hc-body-sm mt-10" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">{{ $recipes->total() }}</strong> {{ $recipes->total() === 1 ? 'recipe' : 'recipes' }} found</p>
        <div class="hc-v3-stagger mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($recipes as $translation)
                <x-localized.recipe-card :translation="$translation" />
            @empty
                <div class="hc-v3-empty col-span-full"><p class="hc-heading-3">No recipes match your search.</p><p class="hc-body-sm mt-2">Try a different word or clear the filters.</p><a class="hc-v3-btn hc-v3-btn--outline mt-5" href="{{ route('en.recipes.index') }}">Show all recipes</a></div>
            @endforelse
        </div>
        <div class="mt-10">{{ $recipes->links() }}</div>
    </section>
</x-layouts.site>
