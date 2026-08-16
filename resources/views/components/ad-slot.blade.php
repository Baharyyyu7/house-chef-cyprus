@props([
    'slotName' => 'recipe_mid',
    'class' => 'my-6',
])

@php
    $isEnabled = config('monetization.ads_enabled', true);
    $isPremiumUser = auth()->check() && auth()->user()->isPremium();
    $slotId = config("monetization.slots.{$slotName}", '1234567890');
    $clientId = config('monetization.adsense_client_id', 'ca-pub-XXXXXXXXXXXXXXXX');
@endphp

@if($isEnabled && !$isPremiumUser)
    <div class="ad-slot-container rounded-2xl bg-neutral-100 dark:bg-neutral-800/60 p-4 border border-dashed border-neutral-300 dark:border-neutral-700 text-center transition-all overflow-hidden {{ $class }}">
        <div class="text-[11px] font-semibold tracking-wider text-neutral-400 dark:text-neutral-500 uppercase mb-2">
            REKLAM
        </div>
        
        <!-- Google AdSense Container -->
        <ins class="adsbygoogle"
             style="display:block; text-align:center;"
             data-ad-layout="in-article"
             data-ad-format="fluid"
             data-ad-client="{{ $clientId }}"
             data-ad-slot="{{ $slotId }}"></ins>
             
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script>

        <div class="mt-2 text-xs text-neutral-400 dark:text-neutral-500">
            <a href="{{ route('pricing') }}" class="underline hover:text-amber-600 transition-colors">
                Reklamları kaldırmak için Premium'a geçin →
            </a>
        </div>
    </div>
@endif
