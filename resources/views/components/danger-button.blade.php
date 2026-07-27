<button {{ $attributes->merge(['type' => 'submit', 'class' => 'hc-v3-btn hc-v3-btn--danger']) }}>
    {{ $slot }}
</button>
