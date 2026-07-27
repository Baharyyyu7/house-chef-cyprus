@props(['value'])

<label {{ $attributes->merge(['class' => 'hc-v3-field-label']) }}>
    {{ $value ?? $slot }}
</label>
