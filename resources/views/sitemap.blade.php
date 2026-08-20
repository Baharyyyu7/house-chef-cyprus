<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
@php
    /**
     * Dil alternatifleri sitemap üzerinden bildirilir.
     *
     * hreflang'in üç eşdeğer yeri var: HTML <head>, HTTP Link başlığı ve
     * sitemap. Sayfa sayısı arttıkça sitemap en ucuzu; her istekte ek bayt
     * taşımıyor. İkisi birden yazıldığında çelişmemeleri gerekiyor, o yüzden
     * küme aynı servisten üretiliyor.
     *
     * Alternatifler kayıt başına hesaplanır: çevirisi olmayan dil kümeye
     * girmez. Girseydi sitemap var olmayan bir adresi ilan ederdi ve hedefi
     * 404 olan çifti Google atardı.
     */
    $cokDilli = collect($urls)->contains(fn (array $entry): bool => ($entry['alternates'] ?? []) !== []);
@endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"@if($cokDilli) xmlns:xhtml="http://www.w3.org/1999/xhtml"@endif>
@foreach($urls as $entry)
    <url><loc>{{ $entry['loc'] }}</loc>@if($entry['lastmod'])<lastmod>{{ $entry['lastmod'] }}</lastmod>@endif
@foreach($entry['alternates'] ?? [] as $alternatif)
        <xhtml:link rel="alternate" hreflang="{{ $alternatif['hreflang'] }}" href="{{ $alternatif['href'] }}"/>
@endforeach
    </url>
@endforeach
</urlset>
