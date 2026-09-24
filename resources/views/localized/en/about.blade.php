<x-layouts.site :title="$metadata['title']" :meta-description="$metadata['description']" :canonical-url="$metadata['canonical']" :robots="$metadata['robots']" :alternates="$alternates">
    <section class="hc-container py-12 sm:py-16">
        <article class="mx-auto max-w-3xl">
            <p class="hc-kicker">About us</p>
            <h1 class="hc-display-xl mt-4">A recipe kitchen from Cyprus</h1>
            <div class="prose prose-stone mt-8 max-w-none dark:prose-invert">
                <p>House Chef Cyprus is an independent recipe and kitchen-guide site based in Cyprus. We write original home recipes inspired by Cypriot, Turkish and wider Mediterranean cooking, and publish them first in Turkish and now in English, with {{ $recipeCount }} recipes already available to English-speaking readers.</p>
                <h2>How our recipes are made</h2>
                <p>Every recipe starts as an original editorial draft. Quantities are written in grams and millilitres alongside household measures, cooking times are checked against the method, and food-safety cues (such as safe internal temperatures and storage times) are added where they matter. A recipe is only published after an editor has reviewed it.</p>
                <h2>Photography and images</h2>
                <p>We only show a dish photo once its licence and source are confirmed. Where an image is AI-generated it is clearly labelled as such and never presented as a photograph of the exact dish.</p>
                <h2>Corrections and feedback</h2>
                <p>If you spot a mistake or a recipe does not work as written, please tell us through the contact section of our <a href="{{ route('legal.center') }}#iletisim-ve-geri-bildirim" hreflang="tr">legal information centre</a> (in Turkish; messages in English are welcome). We update recipes when readers report issues.</p>
            </div>
            <div class="mt-10 flex flex-wrap gap-3"><a class="hc-v3-btn hc-v3-btn--primary" href="{{ route('en.recipes.index') }}">Browse recipes</a><a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('en.blog.index') }}">Read the guides</a></div>
        </article>
    </section>
</x-layouts.site>
