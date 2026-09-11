@php
    /**
     * Kahraman alanı yüksek kaliteli doğrudan döngü videosu.
     *
     * Kullanıcı isteği doğrultusunda statik görsel önceliği kaldırılarak
     * 6.0 MB'lık kristal netliğindeki Akdeniz mutfak videosu doğrudan
     * HTML5 native video akışıyla ilk andan itibaren kesintisiz oynatılır.
     */
    use Illuminate\Support\Facades\Storage;

    $disk = Storage::disk('public');
    $qualityMp4 = 'videos/hero-quality-20260908.mp4';
    $loopMp4 = 'videos/hero-loop.mp4';

    $videoPath = $disk->exists($qualityMp4) 
        ? $qualityMp4 
        : ($disk->exists($loopMp4) ? $loopMp4 : $qualityMp4);

    $videoUrl = $disk->url($videoPath);
    $posterUrl = asset('images/house-chef-hero-v2.png');
@endphp

<video
    class="hc-hero-video is-active"
    data-hero-video
    autoplay
    muted
    loop
    playsinline
    preload="auto"
    poster="{{ $posterUrl }}"
    aria-hidden="true"
    tabindex="-1"
>
    <source src="{{ $videoUrl }}" type="video/mp4">
</video>

