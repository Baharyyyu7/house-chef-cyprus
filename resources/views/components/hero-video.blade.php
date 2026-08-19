@php
    /**
     * Kahraman görselin üzerine binen döngü videosu.
     *
     * Video görselin yerine geçmiyor, üstüne biniyor. Görsel sayfanın LCP
     * öğesi ve fetchpriority="high" ile öne alınmış; video onu geciktirirse
     * ölçülen açılış süresi bozulur. Bu yüzden burada yalnız boş bir <video>
     * basılır - kaynağı JavaScript, sayfa yüklendikten sonra ve yalnız
     * koşullar tutuyorsa takar.
     *
     * Dosya sunucuda yoksa hiçbir şey basılmaz: video gelene kadar sayfa
     * bugünkü hâliyle çalışır, boş bir istek bile gitmez.
     */
    $mp4 = 'videos/hero-loop.mp4';
    $webm = 'videos/hero-loop.webm';

    $mp4Var = is_file(public_path($mp4));
    $webmVar = is_file(public_path($webm));
@endphp

@if ($mp4Var || $webmVar)
    {{-- aria-hidden ve tabindex: video dekoratif bir katman, içerik değil.
         Ekran okuyucuya ve klavye sırasına girmemeli. --}}
    <video
        class="hc-hero-video"
        data-hero-video
        @if ($mp4Var) data-mp4="{{ asset($mp4) }}" @endif
        @if ($webmVar) data-webm="{{ asset($webm) }}" @endif
        muted
        loop
        playsinline
        preload="none"
        aria-hidden="true"
        tabindex="-1"
    ></video>
@endif
