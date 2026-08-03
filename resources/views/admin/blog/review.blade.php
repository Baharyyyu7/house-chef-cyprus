<x-layouts.site :title="$post->title.' · Editoryal karşılaştırma'" robots="noindex,nofollow">
    @php($pack = (array) $revision->changed_seo_fields)
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header eyebrow="Private editorial review" :title="$post->title" description="Bu ekran bir yayın veya onay işlemi yapmaz. Mevcut public sürüm ile özel revision önerisini karşılaştırır." :back="['url'=>route('admin.blog.index'),'label'=>'Blog']" />
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach(['Durum'=>data_get($pack,'status'),'Fact-check'=>data_get($pack,'fact_check_state'),'Medya'=>data_get($pack,'media_state'),'Otomatik yayın'=>data_get($pack,'auto_publish') ? 'Açık' : 'Kapalı'] as $label=>$value)
                <div class="hc-v3-card p-4"><p class="hc-kicker">{{ $label }}</p><strong class="mt-2 block break-words">{{ $value }}</strong></div>
            @endforeach
        </div>
        <div class="mt-8 grid gap-6 xl:grid-cols-2">
            <article class="hc-admin-form-card"><p class="hc-kicker">Mevcut public sürüm</p><h2 class="hc-heading-3 mt-2">{{ $post->title }}</h2><div class="mt-5 whitespace-pre-line text-sm leading-7">{{ $revision->previous_body }}</div></article>
            <article class="hc-admin-form-card"><p class="hc-kicker">Önerilen private revision</p><h2 class="hc-heading-3 mt-2">{{ data_get($pack,'proposed_meta_title') }}</h2><div class="mt-5 whitespace-pre-line text-sm leading-7">{{ $revision->proposed_body }}</div></article>
        </div>
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="hc-admin-form-card"><h2 class="hc-heading-3">Kaynak ve fact-check</h2><pre class="mt-4 overflow-auto whitespace-pre-wrap text-xs">{{ json_encode(data_get($pack,'fact_check_sources'), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre></section>
            <section class="hc-admin-form-card"><h2 class="hc-heading-3">Konuya özel medya briefi</h2><pre class="mt-4 overflow-auto whitespace-pre-wrap text-xs">{{ json_encode(data_get($pack,'media_brief'), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre></section>
        </div>
        <p class="mt-6 rounded-2xl border border-orange-300 bg-orange-50 p-4 text-sm text-orange-950 dark:bg-orange-950/30 dark:text-orange-100">Uygulama, fact-check ve medya onayı ayrı yetkili insan kararlarıdır. Bu revision otomatik olarak public içeriğe uygulanamaz.</p>
    </section>
</x-layouts.site>
