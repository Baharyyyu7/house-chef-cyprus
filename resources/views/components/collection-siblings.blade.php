@props(['baslik', 'ogeler', 'mevcutUrl' => null])

@php
    $digerleri = collect($ogeler)->reject(fn (array $oge): bool => $oge['url'] === $mevcutUrl)->values();
@endphp

@if ($digerleri->isNotEmpty())
    {{-- Koleksiyon sayfalari cikmaz sokakti: ziyaretci bir mutfaktan
         digerine gecemiyordu ve tarama grafigi yatay bag kurmuyordu.
         Yalniz esigi gecmis, gercekten var olan sayfalar listeleniyor. --}}
    <nav class="hc-kardes" aria-label="{{ $baslik }}">
        <p class="hc-kardes__baslik">{{ $baslik }}</p>
        <ul class="hc-kardes__liste">
            @foreach ($digerleri as $oge)
                <li><a class="hc-kardes__oge" href="{{ $oge['url'] }}">{{ $oge['title'] }}</a></li>
            @endforeach
        </ul>
    </nav>
@endif
