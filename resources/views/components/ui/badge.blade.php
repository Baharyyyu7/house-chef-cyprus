@props(['variant' => 'neutral', 'icon' => null])
<span {{ $attributes->class(['hc-v3-badge', 'hc-v3-badge--'.$variant]) }}>@if($icon)<x-ui.icon :name="$icon" size="14" />@endif{{ $slot }}</span>
