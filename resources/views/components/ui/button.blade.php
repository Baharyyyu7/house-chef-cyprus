@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button', 'fullWidth' => false, 'loading' => false, 'icon' => null, 'iconRight' => null, 'label' => null])

<button {{ $attributes->class(['hc-v3-btn', 'hc-v3-btn--'.$variant, 'hc-v3-btn--'.$size, 'hc-v3-btn--block' => $fullWidth])->merge(['type' => $type, 'aria-label' => $label]) }} @disabled($loading)>
    @if($loading)<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>@elseif($icon)<x-ui.icon :name="$icon" size="16" />@endif
    @if(trim($slot) !== '')<span>{{ $slot }}</span>@endif
    @if($iconRight)<x-ui.icon :name="$iconRight" size="16" />@endif
</button>
