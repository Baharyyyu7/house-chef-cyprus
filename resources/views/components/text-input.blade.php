@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'hc-v3-input']) }}>
