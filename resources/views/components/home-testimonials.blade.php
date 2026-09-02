@props(['testimonials' => [], 'disclosure' => null])

@php
    $maddeler = collect($testimonials)->values();
@endphp

@if($maddeler->isNotEmpty())
    {{-- Bu bolum bir yorum karuseliydi: taahhutler otomatik kayan
         blockquote kartlarinda duruyordu ve akis dongusu icin her madde iki
         kez basiliyordu. Sonuc anasayfanin en buyuk metin blogu (5.139
         karakter) ve sifir baglanti.

         Bicim icerikle celisiyordu. Kullanici yorumu olmadigini soyleyen bir
         aciklama satiri gerekmesi de bunun isaretiydi: yorum gibi gorunen
         bir seyin yorum olmadigini yazmak zorunda kaliyorduk.

         Simdi taahhutler taranabilir ve her biri kanitina baglaniyor. --}}
    <section class="hc-taahhut" aria-labelledby="home-taahhut-title">
        <div class="hc-container">
            <div class="mx-auto flex max-w-2xl flex-col items-center text-center" data-reveal>
                <p class="hc-kicker">Şeffaflık</p>
                <h2 id="home-taahhut-title" class="hc-title mt-4">Neye güvenebilirsiniz?</h2>
                @if(filled($disclosure))
                    <p class="hc-body-lg mt-4 text-muted">{{ $disclosure }}</p>
                @endif
            </div>

            <ul class="hc-taahhut__liste mt-10" data-reveal-group>
                @foreach($maddeler as $madde)
                    @php($kanit = $madde['proof'] ?? null)
                    <li class="hc-taahhut__oge">
                        <p class="hc-taahhut__rol">{{ $madde['role'] }}</p>
                        <p class="hc-taahhut__baslik">{{ $madde['name'] }}</p>
                        <p class="hc-taahhut__metin">{{ $madde['text'] }}</p>
                        @if($kanit)
                            <a class="hc-taahhut__kanit" href="{{ route('information.show', $kanit['page']) }}">
                                {{ $kanit['label'] }} <span aria-hidden="true">→</span>
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
