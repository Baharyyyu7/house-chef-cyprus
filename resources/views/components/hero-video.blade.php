@php
    /**
     * Kahraman videosu: hero görselinin kendisi.
     *
     * Eskiden altta bir kapak görseli, üstte geç yüklenen video vardı;
     * açılışta önce resim, sonra videoya geçiş görünüyordu. Artık resim
     * yok: video doğrudan <source> ile basılır, preload="auto" ve autoplay
     * ile ilk kareye kadar koyu zeminde bekler, oynayınca yumuşakça belirir.
     *
     * Dosya sunucuda yoksa hiçbir şey basılmaz: boş bir istek bile gitmez.
     * Hareket kısıtlıysa tarayıcı tarafı (app.js) oynatmayı durdurur, CSS
     * katmanı gizler; video içerik değil süstür (aria-hidden, tabindex dışı).
     */
    use Illuminate\Support\Facades\Storage;

    $disk = Storage::disk('public');
    $qualityMp4 = 'videos/hero-quality-20260908.mp4';
    $loopMp4 = 'videos/hero-loop.mp4';
    $mp4 = $disk->exists($qualityMp4) ? $qualityMp4 : ($disk->exists($loopMp4) ? $loopMp4 : null);
@endphp

@if ($mp4 !== null)
    <video
        class="hc-hero-video"
        data-hero-video
        src="{{ $disk->url($mp4) }}"
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

