@extends('layouts.app')

@section('title', 'Kıbrıs Yemek Tarifleri - Geleneksel Kıbrıs Mutfağı')
@section('meta-description', 'Kıbrıs Yemek Tarifleri: Geleneksel Kıbrıs Mutfağı lezzetleri. Hellim, şeftali kebabı, molehiya, kolokas ve 65+ doğrulanmış tarif.')
@section('canonical-url', route('pages.cyprus-cuisine'))
@section('robots', 'index,follow')
@section('structured-data', $geoSchema)

@section('content')
<section class="hc-container py-14" itemscope itemtype="https://schema.org/CollectionPage">
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider mb-6" style="color: var(--hc-text-muted)">
        <a href="{{ route('home') }}" class="hover:text-orange-600 transition-colors">Ana Sayfa</a>
        <span aria-hidden="true" class="opacity-50">/</span>
        <a href="{{ route('cuisines.index') }}" class="hover:text-orange-600 transition-colors">Mutfaklar</a>
        <span aria-hidden="true" class="opacity-50">/</span>
        <span class="text-charcoal-900 dark:text-charcoal-100 font-medium">Kıbrıs Yemek Tarifleri</span>
    </nav>

    <header class="mb-10">
        <h1 class="hc-display-xl mt-4 leading-tight" itemprop="name">Kıbrıs Yemek Tarifleri - Geleneksel Kıbrıs Mutfağı</h1>
        <h2 class="hc-display-lg mt-4 font-display text-2xl font-semibold" itemprop="description">Kıbrıs Yemekleri</h2>
        <p class="hc-body-lg mt-5 max-w-3xl leading-relaxed text-muted">
            Kıbrıs, Akdeniz'in en zengin mutfak kültürlerinden birine ev sahipliği yapar. <strong>Kıbrıs Yemek Tarifleri</strong>, <strong>Kıbrıs Yemekleri</strong> ve <strong>Geleneksel Kıbrıs Tarifleri</strong> arasında hellim, şeftali kebabı, molehiya, kolokas, magarina bulli ve pirohu gibi ada mutfağına özgü lezzetler bulunur. House Chef Cyprus arşivi, bu mirası 680+ doğrulanmış tarifle belgeler.
        </p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="rounded-2xl border border-gold-100 bg-gold-50/50 p-6">
            <div class="text-3xl font-bold text-gold-700">680+</div>
            <div class="hc-body-sm mt-2 text-muted">Doğrulanmış Tarif</div>
        </div>
        <div class="rounded-2xl border border-gold-100 bg-gold-50/50 p-6">
            <div class="text-3xl font-bold text-gold-700">14</div>
            <div class="hc-body-sm mt-2 text-muted">Mutfak Kültürü</div>
        </div>
        <div class="rounded-2xl border border-gold-100 bg-gold-50/50 p-6">
            <div class="text-3xl font-bold text-gold-700">22</div>
            <div class="hc-body-sm mt-2 text-muted">Kategori</div>
        </div>
    </div>

    <section class="mb-12 max-w-3xl">
        <h2 class="font-display text-xl font-semibold mb-4">Kıbrıs Yemekleri - Adanın Lezzet Mirası</h2>
        <p class="hc-body-sm leading-relaxed text-muted">
            Kıbrıs Mutfağı, Akdeniz'ün zeytinyağlı ve ızgara geleneklerini birleştiren eşsiz bir mutfağıdır. <strong>Kıbrıs Yemekleri Nasıl Yapılır</strong> sorusunun cevabı, her tarifin ölçülü gramajlı ve adımlı anlatımıdır.
        </p>
    </section>

    <section class="mb-12">
        <h2 class="font-display text-xl font-semibold mb-6">Popüler Kıbrıs Yemek Tarifleri</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach($topRecipes as $recipe)
                <x-recipe-card-v3 :recipe="$recipe" :show-favorite="false" />
            @endforeach
        </div>
    </section>

    <section class="mb-12 max-w-3xl">
        <h2 class="font-display text-xl font-semibold mb-4">Kıbrıs Pazarı Yemekleri</h2>
        <p class="hc-body-sm leading-relaxed text-muted">
            Kıbrıs Pazarı Yemekleri, adanın taze sebze ve yabanı otlarını kullanan zeytinyağlı ve ızgara yemeklerdir.
        </p>
    </section>

    <section class="mb-12 max-w-3xl">
        <h2 class="font-display text-xl font-semibold mb-4">Kıbrıs Mutfağı Tarihi</h2>
        <p class="hc-body-sm leading-relaxed text-muted">
            Kıbrıs Mutfağı'nın tarihi, adanın binlerce yıllık uygarlık mirasına dayanır. Fenikelilerden Roma'ya, Bizans'dan Osmanlı hakimiyetine kadar her dönemin etkisi yemek kültürinde hissedilir.
        </p>
    </section>

    <section class="mb-12 max-w-3xl">
        <h2 class="font-display text-xl font-semibold mb-4">Kıbrıs Yemekleri Nasıl Yapılır?</h2>
        <p class="hc-body-sm leading-relaxed text-muted">
            Her <strong>Kıbrıs Yemekleri</strong> tarifimiz ölçülü ağırlıklarla, kesin sürelerle ve adım adım anlatımla hazırlanmıştır.
        </p>
    </section>

    <section class="max-w-3xl">
        <h2 class="font-display text-xl font-semibold mb-4">Sık Sorulan Sorular</h2>
        <dl class="space-y-4">
            <div class="rounded-xl border border-charcoal-100 bg-white p-4">
                <dt class="font-semibold">Kıbrıs Yemekleri nelerdir?</dt>
                <dd class="hc-body-sm mt-2 leading-relaxed text-muted">Kıbrıs Yemekleri; hellim, şeftali kebabı, molehiya, kolokas ve 65+ ada lezzeti ile toplam 680+ doğrulanmış Kıbrıs Mutfağı tarifini kapsar.</dd>
            </div>
            <div class="rounded-xl border border-charcoal-100 bg-white p-4">
                <dt class="font-semibold">Geleneksel Kıbrıs Tarifleri hangileridir?</dt>
                <dd class="hc-body-sm mt-2 leading-relaxed text-muted">Hellim kızartması, şeftali kebabı, molehiya, kolokas, magarina bulli, pirohu ve pilavuna öne çıkar.</dd>
            </div>
        </dl>
    </section>
</section>
@endsection
