@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-bold text-forest-900']) }}>
    {{ $value ?? $slot }}
</label>
