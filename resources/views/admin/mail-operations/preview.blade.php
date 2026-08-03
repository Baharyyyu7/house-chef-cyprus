<x-layouts.site :title="$preview['subject'].' · Mail Önizleme'" robots="noindex,nofollow">
    <section class="hc-container py-8 sm:py-10">
        <a class="text-sm font-bold text-orange-700 underline dark:text-orange-300" href="{{ route('admin.mail-operations.index') }}">← E-posta operasyonlarına dön</a>
        <x-admin.page-header eyebrow="Sentetik önizleme" :title="$preview['subject']" description="Gerçek kullanıcı verisi, credential, token veya canlı gönderim içermez." />
        <article class="mx-auto mt-8 max-w-2xl rounded-3xl border-t-4 border-orange-500 bg-white p-6 shadow-xl dark:bg-stone-900 sm:p-10">
            <p class="text-xs font-extrabold uppercase tracking-[.18em] text-orange-600">House Chef Cyprus · {{ $preview['category'] }}</p>
            <p class="mt-3 rounded-xl bg-orange-50 p-3 text-xs text-stone-600 dark:bg-stone-800 dark:text-stone-300">Preview text: {{ $preview['preheader'] }}</p>
            <h1 class="mt-7 text-3xl font-extrabold tracking-tight text-stone-950 dark:text-white">{{ $preview['heading'] }}</h1>
            <p class="mt-5 leading-7 text-stone-700 dark:text-stone-300">{{ $preview['body'] }}</p>
            <span class="mt-7 inline-flex rounded-xl bg-orange-600 px-5 py-3 font-bold text-white" aria-label="Örnek e-posta eylemi">{{ $preview['action'] }}</span>
            <p class="mt-8 border-t pt-5 text-xs leading-6 text-stone-500">House Chef Cyprus · Yasal Bilgilendirme · Gizlilik · info@housechefcyprus.com</p>
        </article>
    </section>
</x-layouts.site>
