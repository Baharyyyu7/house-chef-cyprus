<x-admin-layout title="Kaynak Evidence Paketi">
    <div class="space-y-6">
        <a class="hc-text-link" href="{{ route('admin.editorial-operations.sources.index') }}">← Kaynak kuyruğu</a>
        <section class="hc-admin-hero"><p class="hc-eyebrow">{{ $item->risk_level }} · {{ $item->risk_domain }}</p><h1 class="hc-heading-1">Kaynak evidence paketi</h1><p class="mt-3 text-muted">{{ $item->claim }}</p><p class="mt-3 text-sm text-muted">Referans kaydı kaynak metnini kopyalamaz; yalnız kısa özgün özet ve URL saklanır. Evidence-ready olmak final karar veya yayın değildir.</p></section>
        <form method="post" action="{{ route('admin.editorial-operations.sources.update',$item) }}" class="hc-admin-form-card grid gap-4 md:grid-cols-2">
            @csrf @method('PUT')
            <label class="block text-sm font-semibold">Kaynak başlığı<input class="hc-input mt-1" name="candidate_source_title" value="{{ old('candidate_source_title',$item->candidate_source_title) }}" required></label>
            <label class="block text-sm font-semibold">Yayıncı / kurum<input class="hc-input mt-1" name="publisher" value="{{ old('publisher',$item->publisher) }}" required></label>
            <label class="block text-sm font-semibold md:col-span-2">Resmî URL<input type="url" class="hc-input mt-1" name="url" value="{{ old('url',$item->url) }}" required></label>
            <label class="block text-sm font-semibold">Yayın tarihi<input type="date" class="hc-input mt-1" name="publication_date" value="{{ old('publication_date',$item->publication_date?->format('Y-m-d')) }}"></label>
            <label class="block text-sm font-semibold">Kaynak türü<input class="hc-input mt-1" name="source_type" value="{{ old('source_type',$item->source_type ?: $item->required_source_type) }}" required></label>
            <label class="block text-sm font-semibold">Güven skoru (0–100)<input type="number" min="0" max="100" class="hc-input mt-1" name="confidence_score" value="{{ old('confidence_score',$item->confidence_score) }}" required></label>
            <label class="block text-sm font-semibold">Tazelik gereksinimi<input class="hc-input mt-1" name="freshness_requirement" value="{{ old('freshness_requirement',$item->freshness_requirement) }}"></label>
            <label class="block text-sm font-semibold md:col-span-2">Desteklenen iddia<textarea class="hc-input mt-1" name="supported_claim" rows="3" required>{{ old('supported_claim',$item->supported_claim) }}</textarea></label>
            <label class="block text-sm font-semibold md:col-span-2">Kısa özgün evidence özeti<textarea class="hc-input mt-1" name="evidence_summary" rows="5" required>{{ old('evidence_summary',$item->evidence_summary) }}</textarea></label>
            <label class="block text-sm font-semibold md:col-span-2">Çelişen evidence (varsa)<textarea class="hc-input mt-1" name="conflicting_evidence" rows="3">{{ old('conflicting_evidence',$item->conflicting_evidence) }}</textarea></label>
            <button class="hc-v3-btn hc-v3-btn--primary md:col-span-2">Evidence referansını kaydet</button>
        </form>
        @can('assign-editorial-work')
            <form method="post" action="{{ route('admin.editorial-operations.sources.assign',$item) }}" class="hc-admin-form-card flex flex-wrap items-end gap-3">
                @csrf
                <label class="block text-sm font-semibold">Reviewer public ID<input class="hc-input mt-1" name="reviewer_public_id" value="{{ $item->reviewer?->public_id }}" placeholder="Gerçek reviewer public ID"></label>
                <button class="hc-v3-btn hc-v3-btn--ghost">Atamayı kaydet</button>
            </form>
        @endcan
    </div>
</x-admin-layout>
