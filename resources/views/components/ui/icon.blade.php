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
        {{-- Yönetim menüsündeki bölümler birbirinden ayırt edilebilsin diye:
             önceki sözlükte karşılığı olmayan her ad varsayılan yıldıza düşüyordu
             ve birbiriyle ilgisiz yedi bölüm aynı ikonu paylaşıyordu. --}}
        @case('gauge')<path d="M12 14 16 9"/><path d="M3.5 18a9 9 0 1 1 17 0"/><circle cx="12" cy="18" r="1.6"/>@break
        @case('book')<path d="M4 4.5A1.5 1.5 0 0 1 5.5 3H19v18H5.5A1.5 1.5 0 0 1 4 19.5Z"/><path d="M4 17h15"/>@break
        @case('pen')<path d="M4 20h4L20 8a2.5 2.5 0 0 0-3.5-3.5L4 16.5Z"/><path d="m15 5.5 3.5 3.5"/>@break
        @case('shield')<path d="M12 3 5 6v5.5c0 4.2 2.9 7.6 7 9.5 4.1-1.9 7-5.3 7-9.5V6Z"/><path d="m9 12 2 2 4-4"/>@break
        @case('flask')<path d="M10 3h4M11 3v6L5.6 18a2 2 0 0 0 1.7 3h9.4a2 2 0 0 0 1.7-3L13 9V3"/><path d="M8 15h8"/>@break
        @case('tag')<path d="M3 11V4h7l10 10-7 7Z"/><circle cx="7.5" cy="7.5" r="1.4"/>@break
        @case('scale')<path d="M12 4v16M7 20h10"/><path d="m5 9 3-4 3 4a3 3 0 0 1-6 0Z"/><path d="m13 9 3-4 3 4a3 3 0 0 1-6 0Z"/>@break
        @case('database')<ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v12c0 1.7 3.6 3 8 3s8-1.3 8-3V6"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>@break
        @case('mail')<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7 8.5 6 8.5-6"/>@break
        @case('server')<rect x="3" y="4" width="18" height="7" rx="2"/><rect x="3" y="13" width="18" height="7" rx="2"/><path d="M7 7.5h.01M7 16.5h.01"/>@break
        @case('refresh')<path d="M20 11A8 8 0 0 0 6.3 6.3L4 8.5"/><path d="M4 4v4.5h4.5"/><path d="M4 13a8 8 0 0 0 13.7 4.7L20 15.5"/><path d="M20 20v-4.5h-4.5"/>@break
        @case('sliders')<path d="M4 7h9M17 7h3M4 17h3M11 17h9"/><circle cx="15" cy="7" r="2"/><circle cx="9" cy="17" r="2"/>@break
        @default <path d="m12 3 1.5 5.5L19 10l-5.5 1.5L12 17l-1.5-5.5L5 10l5.5-1.5L12 3Z"/>
    @endswitch
</svg>
