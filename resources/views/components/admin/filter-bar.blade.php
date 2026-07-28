<form {{ $attributes->class('hc-admin-filter-bar') }} method="get">
    <div class="hc-admin-filter-bar__fields">{{ $slot }}</div>
    <button class="hc-v3-btn hc-v3-btn--secondary" type="submit">Filtrele</button>
    @if(request()->query())
        <a class="hc-v3-btn hc-v3-btn--ghost" href="{{ url()->current() }}">Temizle</a>
    @endif
</form>
