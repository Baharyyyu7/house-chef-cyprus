@props(['name' => 'sparkles', 'size' => 20, 'label' => null])

@php($decorative = blank($label))
<svg {{ $attributes->class('shrink-0')->merge(['width' => $size, 'height' => $size, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.9', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round']) }} @if($decorative) aria-hidden="true" @else role="img" aria-label="{{ $label }}" @endif>
    @switch($name)
        @case('heart')<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.9-8.6a5.5 5.5 0 0 0-.1-7.8Z"/>@break
        @case('menu')<path d="M4 7h16M4 12h16M4 17h16"/>@break
        @case('close')<path d="m6 6 12 12M18 6 6 18"/>@break
        @case('sun')<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>@break
        @case('moon')<path d="M20.6 15.8A8.5 8.5 0 0 1 8.2 3.4 8.5 8.5 0 1 0 20.6 15.8Z"/>@break
        @case('arrow-right')<path d="M5 12h14M13 6l6 6-6 6"/>@break
        @case('check')<path d="m5 12 4 4L19 6"/>@break
        @case('warning')<path d="M10.3 3.8 2.1 18a2 2 0 0 0 1.7 3h16.4a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/>@break
        @case('info')<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>@break
        @case('search')<circle cx="11" cy="11" r="6"/><path d="m16 16 4 4"/>@break
        @case('image')<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8" cy="9" r="1.3"/><path d="m21 16-5-5L5 20"/>@break
        @case('users')<path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-4A4.5 4.5 0 0 0 3 18.5V20"/><circle cx="9.5" cy="7" r="3.5"/><path d="M17 10a3 3 0 1 0-1.2-5.75M21 20v-1.5a4.5 4.5 0 0 0-2.7-4.12"/>@break
        @case('archive')<path d="M4 8h16v12H4zM3 4h18v4H3zM10 12h4"/>@break
        @case('chevron-down')<path d="m6 9 6 6 6-6"/>@break
        @default <path d="m12 3 1.5 5.5L19 10l-5.5 1.5L12 17l-1.5-5.5L5 10l5.5-1.5L12 3Z"/>
    @endswitch
</svg>
