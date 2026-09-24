<x-layouts.site :title="$metadata['title']" :meta-description="$metadata['description']" :canonical-url="$metadata['canonical']" :robots="$metadata['robots']" :alternates="$alternates">
    <section class="hc-container pt-6 sm:pt-9">
        <div class="hc-home-hero relative isolate overflow-hidden rounded-[2rem] px-6 py-10 sm:rounded-[2.5rem] sm:px-10 sm:py-14 lg:min-h-[34rem] lg:px-14 lg:py-20">
            <img class="v2-hero-image absolute inset-0 -z-20 h-full w-full object-cover object-[68%_center] opacity-70" src="{{ asset('images/house-chef-hero-v2.png') }}" alt="Cyprus-inspired Mediterranean plate with halloumi, tomatoes and olives">
            <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(9,9,9,.96)_0%,rgba(9,9,9,.78)_42%,rgba(9,9,9,.12)_78%)]"></div>
            <div class="max-w-2xl">
                <p class="hc-kicker text-orange-300">Recipes from Cyprus, in English</p>
                <h1 class="mt-5 text-5xl font-extrabold leading-[.98] sm:text-6xl lg:text-7xl">Cypriot &amp; Mediterranean<br><span class="text-orange-400">home cooking</span></h1>
                <p class="hc-hero-muted mt-6 max-w-xl text-base leading-7 sm:text-lg">Halloumi breakfasts, sheftalia, lemony oven chicken, lentil soup, meze and syrupy desserts — written by a kitchen in Cyprus, with metric measures and clear, tested steps.</p>
                <form class="mt-8 flex max-w-xl flex-col gap-2 rounded-2xl bg-white/95 p-2 sm:flex-row" action="{{ route('en.recipes.index') }}" role="search">
                    <label class="sr-only" for="en-hero-search">Search recipes</label>
                    <input id="en-hero-search" class="min-w-0 flex-1 border-0 bg-transparent px-3 text-base text-stone-900 focus:ring-0" name="q" placeholder="Try halloumi, lentil soup or baklava">
                    <button class="hc-btn hc-btn-primary shrink-0" type="submit">Search</button>
                </form>
            </div>
        </div>
    </section>

    <section class="hc-container py-14">
        <div class="grid gap-6 lg:grid-cols-[1.1fr_.9fr] lg:items-start">
            <div>
                <p class="hc-kicker">Why cooks abroad use House Chef Cyprus</p>
                <h2 class="hc-title mt-3">The flavours of the island, explained for your kitchen.</h2>
                <p class="mt-5 max-w-2xl leading-7" style="color: var(--hc-text-secondary)">Cypriot food sits between Greek, Turkish and Levantine cooking: olive oil, lemon, fresh herbs, halloumi, pulses, grilled meat and slow-baked vegetables. Our English edition brings {{ $recipeCount }} of our home recipes to readers in the UK, Europe and beyond, so you can cook them with ingredients from an ordinary supermarket.</p>
            </div>
            <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                <li class="hc-v3-card p-5"><strong>Metric &amp; cup-friendly</strong><p class="mt-1 text-sm" style="color: var(--hc-text-secondary)">Grams and millilitres first, with household spoons where they help.</p></li>
                <li class="hc-v3-card p-5"><strong>Editorially checked</strong><p class="mt-1 text-sm" style="color: var(--hc-text-secondary)">Each recipe is reviewed before publishing and updated when readers report issues.</p></li>
                <li class="hc-v3-card p-5"><strong>Honest ingredient notes</strong><p class="mt-1 text-sm" style="color: var(--hc-text-secondary)">Substitutions for halloumi, bulgur, tahini and other items that can be hard to find.</p></li>
            </ul>
        </div>
    </section>

    @foreach([
        'cyprus' => ['Cypriot classics', 'Recipes rooted in the island’s home kitchens.'],
        'quick' => ['Ready in 30 minutes or less', 'Fast weeknight dishes that do not cut corners.'],
        'breakfast' => ['Mediterranean breakfast', 'Eggs, halloumi, olives and good bread.'],
        'mains' => ['Main courses', 'Reliable dinners for family meals and guests.'],
        'desserts' => ['Desserts', 'Milk puddings, syrup cakes and fruit to finish.'],
    ] as $key => [$heading, $intro])
        @if(($sections[$key] ?? collect())->isNotEmpty())
            <section class="hc-container pb-16" aria-labelledby="section-{{ $key }}">
                <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="hc-kicker">Recipe collection</p><h2 id="section-{{ $key }}" class="hc-title mt-2">{{ $heading }}</h2><p class="mt-2 max-w-2xl text-sm leading-6" style="color: var(--hc-text-secondary)">{{ $intro }}</p></div><a class="hc-btn hc-btn-outline" href="{{ route('en.recipes.index') }}">All recipes →</a></div>
                <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@foreach($sections[$key] as $translation)<x-localized.recipe-card :translation="$translation" />@endforeach</div>
            </section>
        @endif
    @endforeach

    @if($recipeCount === 0)
        <section class="hc-container pb-16"><div class="hc-v3-empty"><p class="hc-heading-3">English recipes are on their way.</p><p class="hc-body-sm mt-2">Meanwhile you can browse the full Turkish collection.</p><a class="hc-v3-btn hc-v3-btn--outline mt-5" href="{{ route('recipes.index') }}" hreflang="tr">Turkish recipes</a></div></section>
    @endif

    @if($latestPosts->isNotEmpty())
        <section class="hc-container pb-16"><div class="flex flex-wrap items-end justify-between gap-4"><div><p class="hc-kicker">Kitchen guides</p><h2 class="hc-title mt-2">From the blog</h2></div><a class="hc-btn hc-btn-outline" href="{{ route('en.blog.index') }}">All guides →</a></div>
            <div class="mt-7 grid gap-5 md:grid-cols-3">@foreach($latestPosts as $post)<article class="hc-v3-card hc-v3-card--interactive relative p-5"><p class="hc-overline" style="color: var(--hc-text-muted)">{{ $post->category_label ?: 'Guide' }}</p><h3 class="hc-heading-3 mt-2"><a class="before:absolute before:inset-0" href="{{ route('en.blog.show', $post->slug) }}">{{ $post->title }}</a></h3><p class="hc-body-sm mt-3 line-clamp-3" style="color: var(--hc-text-secondary)">{{ $post->excerpt }}</p></article>@endforeach</div>
        </section>
    @endif

    <section class="hc-container pb-16">
        <h2 class="hc-title">Cypriot food: quick answers</h2>
        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <details class="hc-v3-card p-5"><summary class="cursor-pointer font-extrabold">What is traditional Cypriot food?</summary><p class="mt-3 text-sm leading-6" style="color: var(--hc-text-secondary)">Home cooking in Cyprus relies on olive oil, lemon, tomatoes, pulses, herbs such as mint and oregano, and cheeses like halloumi. Typical dishes include sheftalia (caul-wrapped sausages), oven-baked meat with potatoes, lentil and bulgur dishes, stuffed vegetables and meze spreads.</p></details>
            <details class="hc-v3-card p-5"><summary class="cursor-pointer font-extrabold">Are these recipes Greek Cypriot or Turkish Cypriot?</summary><p class="mt-3 text-sm leading-6" style="color: var(--hc-text-secondary)">Both communities share many dishes and ingredients. We focus on everyday home cooking from across the island, alongside Turkish and wider Mediterranean recipes that are common on Cypriot tables.</p></details>
            <details class="hc-v3-card p-5"><summary class="cursor-pointer font-extrabold">Can I cook these recipes outside Cyprus?</summary><p class="mt-3 text-sm leading-6" style="color: var(--hc-text-secondary)">Yes. Every English recipe uses ingredients available in UK and European supermarkets, and we note substitutions where a Cypriot ingredient is hard to find.</p></details>
            <details class="hc-v3-card p-5"><summary class="cursor-pointer font-extrabold">Is there a Turkish version of the site?</summary><p class="mt-3 text-sm leading-6" style="color: var(--hc-text-secondary)">Yes — the original <a class="underline" href="{{ route('home') }}" hreflang="tr">Turkish edition</a> has the full recipe archive and member tools.</p></details>
        </div>
    </section>
</x-layouts.site>
