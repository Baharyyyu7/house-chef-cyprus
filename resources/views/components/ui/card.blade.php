@props(['variant' => 'base', 'padding' => 'p-5 sm:p-6'])
<section {{ $attributes->class(['hc-v3-card', 'hc-v3-card--interactive' => $variant === 'interactive', 'hc-v3-card--selected' => $variant === 'selected', 'hc-v3-card--danger' => $variant === 'danger', $padding]) }}>{{ $slot }}</section>
