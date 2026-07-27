@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-stone-300 bg-white px-3 py-2.5 text-forest-900 shadow-sm focus:border-forest-700 focus:ring-forest-700']) }}>
