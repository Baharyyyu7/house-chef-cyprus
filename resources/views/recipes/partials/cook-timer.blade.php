<div
    x-data="hcCookTimer()"
    x-on:hc-start-timer.window="baslat($event.detail.seconds, $event.detail.label)"
    x-show="acik"
    x-cloak
    x-transition
    class="fixed bottom-4 right-4 z-50 w-72 rounded-2xl border border-charcoal-200 bg-white p-4 shadow-2xl dark:border-charcoal-700 dark:bg-charcoal-900"
    role="timer"
    aria-label="{{ __('recipe.timer.title') }}"
>
    <div class="flex items-start justify-between gap-2">
        <p class="min-w-0 flex-1 truncate text-sm font-bold" x-text="etiket"></p>
        <button type="button" x-on:click="kapat()" class="text-lg leading-none text-stone-400 hover:text-stone-700" aria-label="{{ __('recipe.timer.close') }}">&times;</button>
    </div>
    <p class="mt-1 font-mono text-4xl font-extrabold tabular-nums" x-text="gosterim"></p>
    <div class="mt-2 h-2 overflow-hidden rounded-full bg-stone-200 dark:bg-charcoal-700" role="progressbar" aria-valuemin="0" aria-valuemax="100" x-bind:aria-valuenow="Math.round(yuzde)">
        <div class="h-full rounded-full bg-orange-500 transition-all" x-bind:style="`width: ${yuzde}%`"></div>
    </div>
    <p x-show="bitti" x-cloak class="mt-2 text-sm font-bold text-orange-600">{{ __('recipe.timer.finished') }}</p>
    <div class="mt-3 flex gap-2">
        <button type="button" x-on:click="duraklatDevam()" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm flex-1" x-text="calisiyor ? '{{ __('recipe.timer.pause') }}' : '{{ __('recipe.timer.resume') }}'"></button>
        <button type="button" x-on:click="sifirla()" class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm flex-1">{{ __('recipe.timer.reset') }}</button>
    </div>
</div>
