@props(['icon' => 'sparkles', 'title' => 'Henüz içerik yok.'])
<div {{ $attributes->class('hc-v3-empty') }}><x-ui.icon :name="$icon" size="32" class="mx-auto mb-3 text-orange-500" /><h2 class="hc-heading-3">{{ $title }}</h2><div class="mt-2 hc-body-sm">{{ $slot }}</div></div>
