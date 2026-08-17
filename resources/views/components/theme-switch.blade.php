<button
    type="button"
    role="switch"
    data-theme-switch
    @click="dark = !dark"
    :aria-checked="dark.toString()"
    :aria-label="dark ? 'Açık temaya geç' : 'Koyu temaya geç'"
    :title="dark ? 'Açık temaya geç' : 'Koyu temaya geç'"
    {{ $attributes->class(['hc-theme-switch']) }}
>
    <span class="hc-theme-switch__knob" aria-hidden="true">
        <span x-show="!dark" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 rotate-45" x-transition:enter-end="opacity-100 rotate-0" class="hc-theme-switch__icon text-amber-500">
            <x-ui.icon name="sun" size="15" />
        </span>
        <span x-cloak x-show="dark" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 -rotate-45" x-transition:enter-end="opacity-100 rotate-0" class="hc-theme-switch__icon text-indigo-400">
            <x-ui.icon name="moon" size="15" />
        </span>
    </span>
</button>
