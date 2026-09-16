@php
    /**
     * Kahraman videosu: hero görselinin kendisi.
     *
     * Eskiden altta bir kapak görseli, üstte geç yüklenen video vardı;
     * açılışta önce resim, sonra videoya geçiş görünüyordu. Artık resim
     * yok: video doğrudan src ile basılır, preload="auto" ve autoplay ile
     * oynar. İlk kare gelene kadar videonun kendi afiş karesi (poster)
     * görünür, siyah zemin beklenmez.
     *
     * Kaynak önceliği: hafif akıcı sürüm (720p, ~1 MB) önce gelir; yoksa
     * eski tam kalite dosyalara düşülür. Afiş yoksa poster basılmaz.
     *
     * Dosya sunucuda yoksa hiçbir şey basılmaz: boş bir istek bile gitmez.
     * Hareket kısıtlıysa tarayıcı tarafı (app.js) oynatmayı durdurur, CSS
     * katmanı gizler; video içerik değil süstür (aria-hidden, tabindex dışı).
     */
    use Illuminate\Support\Facades\Storage;

    $disk = Storage::disk('public');
    $adaylar = ['videos/hero-720p-20260916.mp4', 'videos/hero-quality-20260908.mp4', 'videos/hero-loop.mp4'];
    $mp4 = null;

    foreach ($adaylar as $aday) {
        if ($disk->exists($aday)) {
            $mp4 = $aday;
            break;
        }
    }

    $poster = $disk->exists('videos/hero-poster.webp') ? 'videos/hero-poster.webp' : null;
@endphp

@if ($mp4 !== null)
    {{-- Afiş varsa katman en baştan görünür: poster ilk kareyi gösterir,
         video başlayınca aynı kareden devam eder, geçiş hissedilmez. --}}
    <video
        class="hc-hero-video{{ $poster !== null ? ' is-active' : '' }}"
        data-hero-video
        src="{{ $disk->url($mp4) }}"
        @if ($poster !== null) poster="{{ $disk->url($poster) }}" @endif
        muted
        loop
        playsinline
        autoplay
        preload="auto"
        disablepictureinpicture
        aria-hidden="true"
        tabindex="-1"
    ></video>
@endif

