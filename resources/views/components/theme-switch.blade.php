<button
    type="button"
    role="switch"
    data-theme-switch
    @click="window.hcTemaDegistir(() => dark = !dark)"
    :aria-checked="dark.toString()"
    :aria-label="dark ? @js(__('theme.switch_to_light')) : @js(__('theme.switch_to_dark'))"
    :title="dark ? @js(__('theme.switch_to_light')) : @js(__('theme.switch_to_dark'))"
    {{ $attributes->class(['hc-theme-switch']) }}
>
    <span class="hc-theme-switch__knob" aria-hidden="true">
        <span x-show="!dark" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 rotate-45 scale-75" x-transition:enter-end="opacity-100 rotate-0 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 rotate-0 scale-100" x-transition:leave-end="opacity-0 -rotate-45 scale-75" class="hc-theme-switch__icon text-amber-500">
            <x-ui.icon name="sun" size="15" />
        </span>
        <span x-cloak x-show="dark" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -rotate-45 scale-75" x-transition:enter-end="opacity-100 rotate-0 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 rotate-0 scale-100" x-transition:leave-end="opacity-0 rotate-45 scale-75" class="hc-theme-switch__icon text-indigo-400">
            <x-ui.icon name="moon" size="15" />
        </span>
    </span>
    <span class="hc-theme-switch__label">{{ __('theme.label') }}</span>
</button>
