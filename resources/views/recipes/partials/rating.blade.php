<div
    x-data="hcRecipeRating(@js(route('recipes.rate', $recipe)), @js($recipe->slug), {{ $summary['count'] }}, {{ $summary['average'] ?? 'null' }})"
    class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-2"
    aria-label="{{ __('recipe.rating.title') }}"
>
    <div class="flex items-center gap-1" role="radiogroup" aria-label="{{ __('recipe.rating.title') }}">
        <template x-for="yildiz in [1,2,3,4,5]" :key="yildiz">
            <button
                type="button"
                role="radio"
                x-bind:aria-checked="secili === yildiz ? 'true' : 'false'"
                x-bind:aria-label="yildiz + ' / 5'"
                x-on:mouseenter="vurgu = yildiz"
                x-on:mouseleave="vurgu = 0"
                x-on:click="oyla(yildiz)"
                x-bind:disabled="gonderiliyor"
                class="text-2xl leading-none transition"
                x-bind:class="(vurgu || gorunen) >= yildiz ? 'text-amber-400' : 'text-stone-500'"
                x-text="(vurgu || gorunen) >= yildiz ? '★' : '☆'"
            ></button>
        </template>
    </div>
    <p class="text-sm text-stone-300">
        <template x-if="adet > 0">
            <span><strong class="text-white" x-text="ortalama.toFixed(1)"></strong> · <span x-text="adet"></span> {{ __('recipe.rating.votes') }}</span>
        </template>
        <template x-if="adet === 0">
            <span>{{ __('recipe.rating.first_hint') }}</span>
        </template>
    </p>
    <p x-show="tesekkur" x-cloak class="text-sm font-bold text-orange-300">{{ __('recipe.rating.thanks') }}</p>
</div>
