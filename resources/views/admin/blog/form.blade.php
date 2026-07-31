<x-layouts.site :title="$post->exists ? 'Blog yazısını düzenle' : 'Yeni blog yazısı'">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header eyebrow="SEO & İçerik" :title="$post->exists ? 'Blog yazısını düzenle' : 'Yeni blog yazısı'" description="Taslaklar otomatik public olmaz; yayın için tüm kalite kapıları ve açık yönetici onayı gerekir." :back="['url' => route('admin.blog.index'), 'label' => 'Blog']" />

        <form method="post" action="{{ $post->exists ? route('admin.blog.update', $post) : route('admin.blog.store') }}" class="hc-admin-form-card mt-7 grid gap-4">
            @csrf
            @if ($post->exists)
                @method('put')
            @endif

            <label>Başlık<input class="hc-input mt-1 w-full" name="title" value="{{ old('title', $post->title) }}" required></label>
            <label>Slug<input class="hc-input mt-1 w-full" name="slug" value="{{ old('slug', $post->slug) }}" placeholder="Başlıktan otomatik üretilir"></label>
            <label>Özet<textarea class="hc-input mt-1 min-h-24 w-full" name="excerpt" required>{{ old('excerpt', $post->excerpt) }}</textarea></label>
            <label>İçerik<textarea class="hc-input mt-1 min-h-64 w-full" name="body" required>{{ old('body', $post->body) }}</textarea></label>

            <div class="grid gap-4 md:grid-cols-2">
                <label>Kategori
                    <select class="hc-input mt-1 w-full" name="blog_category_id"><option value="">Kategori yok</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select>
                </label>
                <label>Yaşam döngüsü
                    <select class="hc-input mt-1 w-full" name="status">@foreach (['idea' => 'Fikir', 'research' => 'Araştırma', 'brief_ready' => 'Brief hazır', 'draft' => 'Taslak', 'fact_check' => 'Fact-check', 'editorial_review' => 'Editoryal inceleme', 'seo_review' => 'SEO inceleme', 'media_review' => 'Medya inceleme', 'approved' => 'Onaylandı', 'scheduled' => 'Planlandı'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $post->status ?: 'draft') === $value)>{{ $label }}</option>@endforeach</select>
                </label>
                <label>İçerik amacı<input class="hc-input mt-1 w-full" name="content_purpose" value="{{ old('content_purpose', $post->content_purpose) }}" placeholder="Bu yazının benzersiz kullanıcı faydası"></label>
                <label>Arama niyeti
                    <select class="hc-input mt-1 w-full" name="search_intent"><option value="">Seçin</option>@foreach (['informational' => 'Bilgilendirici', 'navigational' => 'Yönlendirici', 'commercial' => 'Ticari araştırma', 'transactional' => 'İşlemsel'] as $value => $label)<option value="{{ $value }}" @selected(old('search_intent', $post->search_intent) === $value)>{{ $label }}</option>@endforeach</select>
                </label>
                <label>Ana konu<input class="hc-input mt-1 w-full" name="primary_keyword" value="{{ old('primary_keyword', $post->primary_keyword) }}"></label>
                <label>Planlanan tarih<input class="hc-input mt-1 w-full" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $post->scheduled_at?->format('Y-m-d\\TH:i')) }}"></label>
                <label>Fact-check
                    <select class="hc-input mt-1 w-full" name="fact_check_status">@foreach (['pending' => 'Bekliyor', 'passed' => 'Geçti', 'needs_review' => 'İnceleme gerekli'] as $value => $label)<option value="{{ $value }}" @selected(old('fact_check_status', $post->fact_check_status ?: 'pending') === $value)>{{ $label }}</option>@endforeach</select>
                </label>
                <label>Özgünlük
                    <select class="hc-input mt-1 w-full" name="originality_status">@foreach (['pending' => 'Bekliyor', 'passed' => 'Geçti', 'needs_review' => 'İnceleme gerekli'] as $value => $label)<option value="{{ $value }}" @selected(old('originality_status', $post->originality_status ?: 'pending') === $value)>{{ $label }}</option>@endforeach</select>
                </label>
                <label>Editoryal durum
                    <select class="hc-input mt-1 w-full" name="editorial_status">@foreach (['draft' => 'Taslak', 'in_review' => 'İncelemede', 'approved' => 'Onaylandı', 'rejected' => 'Reddedildi'] as $value => $label)<option value="{{ $value }}" @selected(old('editorial_status', $post->editorial_status ?: 'draft') === $value)>{{ $label }}</option>@endforeach</select>
                </label>
                <label>Canonical URL<input class="hc-input mt-1 w-full" type="url" name="canonical_url" value="{{ old('canonical_url', $post->canonical_url) }}"></label>
                <label>Meta başlık<input class="hc-input mt-1 w-full" name="meta_title" maxlength="70" value="{{ old('meta_title', $post->meta_title) }}"></label>
                <label>Meta açıklama<input class="hc-input mt-1 w-full" name="meta_description" maxlength="160" value="{{ old('meta_description', $post->meta_description) }}"></label>
            </div>

            <label>Kaynak/fact-check notu<textarea class="hc-input mt-1 min-h-24 w-full" name="source_notes_text" placeholder="Kaynakları veya doğrulama notunu yazın; token ya da kişisel veri girmeyin.">{{ old('source_notes_text', data_get($post->source_notes, 'editorial_note')) }}</textarea></label>
            <label>Etiketler<select class="hc-input mt-1 w-full" name="tag_ids[]" multiple>@foreach ($tags as $tag)<option value="{{ $tag->id }}" @selected($post->tags->contains($tag))>{{ $tag->name }}</option>@endforeach</select></label>
            <label>İlgili doğrulanmış tarifler<select class="hc-input mt-1 w-full" name="recipe_ids[]" multiple>@foreach ($recipes as $recipe)<option value="{{ $recipe->id }}" @selected($post->recipes->contains($recipe))>{{ $recipe->title }}</option>@endforeach</select></label>
            <button class="hc-v3-btn hc-v3-btn--primary justify-self-start" type="submit">Kaydet</button>
        </form>
    </section>
</x-layouts.site>
