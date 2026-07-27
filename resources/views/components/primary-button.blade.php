<button {{ $attributes->merge(['type' => 'submit', 'class' => 'hc-btn hc-btn-primary']) }}>
    {{ $slot }}
</button>
