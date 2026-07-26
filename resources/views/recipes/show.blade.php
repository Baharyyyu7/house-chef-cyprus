<x-layouts.site :title="$recipe->title">
    <section class="mx-auto max-w-5xl px-5 pt-12">
        <a class="text-sm font-bold text-orange-700" href="{{ route('recipes.index') }}">← Tariflere dön</a>
        <div class="mt-5 rounded-3xl bg-gradient-to-br from-orange-200 via-amber-100 to-lime-100 p-8 md:p-12">
            <p class="font-bold text-orange-800">{{ $recipe->cuisine?->name }} · {{ $recipe->category?->name }}</p>
            <h1 class="mt-3 text-4xl font-black md:text-6xl">{{ $recipe->title }}</h1>
            <p class="mt-5 max-w-2xl text-lg text-stone-700">{{ $recipe->excerpt }}</p>
            <div class="mt-8 grid gap-3 text-sm font-bold sm:grid-cols-2 lg:grid-cols-5">
                <span class="rounded-full bg-white px-4 py-2">Hazırlık {{ $recipe->prep_minutes }} dk</span>
                <span class="rounded-full bg-white px-4 py-2">Pişirme {{ $recipe->cook_minutes }} dk</span>
                <span class="rounded-full bg-white px-4 py-2">Dinlenme {{ $recipe->rest_minutes }} dk</span>
                <span class="rounded-full bg-white px-4 py-2">Toplam {{ $recipe->totalMinutes() }} dk</span>
                <span class="rounded-full bg-white px-4 py-2">{{ $recipe->servings }} porsiyon · {{ $recipe->difficulty }}</span>
            </div>
        </div>
    </section>

    <section class="mx-auto grid max-w-5xl gap-10 px-5 py-12 lg:grid-cols-[.75fr_1.25fr]">
        <aside class="space-y-5">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
                <h2 class="text-xl font-black">Malzemeler</h2>
                <form class="mt-3 flex items-center gap-2" method="get"><label class="text-sm">Porsiyon</label><input class="w-20 rounded-lg border-stone-300" type="number" min="1" name="servings" value="{{ $servings }}"><button class="text-sm font-bold text-orange-700">Hesapla</button></form>
                <ul class="mt-5 space-y-3">@foreach($recipe->ingredients as $ingredient)<li class="flex justify-between border-b border-stone-100 pb-2"><span>{{ $ingredient->name }}@if($ingredient->pivot->is_optional) <em class="text-xs text-stone-500">(isteğe bağlı)</em>@endif</span><strong>{{ rtrim(rtrim(number_format(($ingredient->pivot->quantity ?? 0) * $servings / $recipe->servings, 2), '0'), '.') }} {{ $ingredient->pivot->unit }}</strong></li>@endforeach</ul>
                @auth
                    <form class="mt-6" method="post" action="{{ route('favorites.toggle', $recipe) }}">@csrf<button class="w-full rounded-xl border border-orange-700 py-3 font-bold text-orange-700">♡ Favorilere ekle</button></form>
                    <form class="mt-3" method="post" action="{{ route('cooking-sessions.start', $recipe) }}">@csrf<input type="hidden" name="servings" value="{{ $servings }}"><button class="w-full rounded-xl bg-orange-700 py-4 text-lg font-black text-white">Yemek yapmaya başla</button></form>
                @else
                    <a class="mt-6 block rounded-xl bg-orange-700 py-4 text-center text-lg font-black text-white" href="{{ route('login') }}">Yemek yapmak için giriş yapın</a>
                @endauth
            </div>
            @if($match)<div class="rounded-2xl bg-emerald-100 p-5 text-emerald-900"><strong>{{ $match['status'] }}</strong><p class="mt-1 text-sm">{{ $match['available'] }}/{{ $match['required'] }} zorunlu malzeme dolabınızda.</p></div>@endif
        </aside>
        <div>
            <h2 class="text-2xl font-black">Yapım adımları</h2>
            <div class="mt-5 space-y-4">@foreach($recipe->steps as $step)<article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200"><div class="flex flex-wrap items-start justify-between gap-3"><div><p class="font-bold text-orange-700">ADIM {{ $step->position }} @if($step->is_optional)<span class="ml-2 rounded-full bg-stone-100 px-2 py-1 text-xs text-stone-600">İsteğe bağlı</span>@endif</p><h3 class="mt-1 text-xl font-black">{{ $step->title }}</h3></div>@if($step->duration_seconds)<span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-bold">{{ intdiv($step->duration_seconds, 60) }} dk @if($step->timer_required)· ⏱ Zamanlayıcı@endif</span>@endif</div><p class="mt-3 leading-7 text-stone-700">{{ $step->instruction }}</p>@if($step->equipment)<p class="mt-3 text-sm"><strong>Ekipman:</strong> {{ $step->equipment }}</p>@endif@if($step->temperature_value)<p class="mt-2 text-sm"><strong>Sıcaklık:</strong> {{ $step->temperature_value }}°{{ $step->temperature_unit === 'fahrenheit' ? 'F' : 'C' }}</p>@endif@if($step->chef_tip)<p class="mt-3 rounded-xl bg-amber-50 p-3 text-sm text-amber-900"><strong>Şef püf noktası:</strong> {{ $step->chef_tip }}</p>@endif@if($step->safety_note)<p class="mt-3 rounded-xl bg-red-50 p-3 text-sm text-red-900"><strong>Güvenlik notu:</strong> {{ $step->safety_note }}</p>@endif</article>@endforeach</div>
            <div class="mt-8 rounded-2xl bg-stone-900 p-6 text-white" x-data="{question:'', answer:''}"><h2 class="text-xl font-black">AI Şef</h2><p class="mt-1 text-sm text-stone-300">Malzeme ve pişirme sorularınız için demo asistan.</p><form class="mt-4 flex gap-2" @submit.prevent="fetch('{{ route('recipes.ai', $recipe) }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({question})}).then(r=>r.json()).then(r=>answer=r.answer)"><input x-model="question" class="min-w-0 flex-1 rounded-xl text-stone-900" placeholder="Örn. fırın olmadan nasıl yaparım?"><button class="rounded-xl bg-orange-500 px-4 font-bold">Sor</button></form><p x-show="answer" x-text="answer" class="mt-4 text-sm leading-6"></p></div>
        </div>
    </section>
</x-layouts.site>
