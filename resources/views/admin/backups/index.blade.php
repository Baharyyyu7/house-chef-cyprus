<x-layouts.site title="Yedekler">
    @php
        /**
         * Sayfa ham bir döküm hâlindeydi: ULID'ler, ISO zaman damgaları,
         * "verified" gibi ham durum değerleri ve İngilizce düğmeler alt alta
         * yüzlerce satır. Oysa buraya bakan kişinin tek bir sorusu var:
         * son yedek sağlam mı ve ne kadar eski?
         *
         * O cevap artık en üstte duruyor. Liste altında kalıyor ve karar için
         * gerekmeyen alanlar (ULID, sürüm hash'i) ikincil satıra iniyor.
         */
        $sonYedek = $backupSummary['latest'];
        $dogrulanmis = $backupSummary['verified'];
        $sonZaman = $sonYedek ? \Illuminate\Support\Carbon::parse($sonYedek['created_at']) : null;
        $sonSaglam = ($sonYedek['status'] ?? null) === 'verified';
        $bayat = $sonZaman?->lt(now()->subDay()) ?? true;

        $boyut = fn ($bayt) => $bayt >= 1048576
            ? number_format($bayt / 1048576, 1, ',', '.').' MB'
            : number_format($bayt / 1024, 0, ',', '.').' KB';
    @endphp

    <section class="hc-admin-dashboard py-10">
        <p class="hc-admin-breadcrumb"><a href="{{ route('admin.dashboard') }}">Yönetim</a> <span aria-hidden="true">/</span> <span>Yedekler</span></p>

        <div class="hc-admin-page-header mt-1">
            <div>
                <h1 class="hc-heading-1">Yedekler</h1>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">Her deploy öncesi otomatik yedek alınır. Canlıya geri yükleme bu ekrandan yapılamaz.</p>
            </div>
            <form method="post" action="{{ route('admin.backups.create') }}">
                @csrf
                <button class="hc-btn hc-btn-primary">Şimdi yedek al</button>
            </form>
        </div>

        @if(session('status'))
            <div class="hc-alert hc-alert-success mt-5" role="status">{{ session('status') }}</div>
        @endif

        {{-- Sayfanın cevabı: tek bakışta durum. --}}
        <div class="hc-v3-card mt-7 p-5 sm:p-6">
            @if($sonYedek)
                <p class="hc-overline" style="color: var(--hc-text-muted)">Son yedek</p>
                <div class="mt-2 flex flex-wrap items-end gap-x-4 gap-y-2">
                    <span class="hc-heading-2">{{ $sonZaman->diffForHumans() }}</span>
                    <span @class([
                        'hc-v3-badge',
                        'hc-v3-badge--success' => $sonSaglam,
                        'hc-v3-badge--warning' => ! $sonSaglam,
                    ])>{{ $sonSaglam ? 'Doğrulandı' : 'Doğrulanmadı' }}</span>
                    @if($bayat)
                        <span class="hc-v3-badge hc-v3-badge--warning">24 saatten eski</span>
                    @endif
                </div>
                <p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)">
                    {{ $sonZaman->translatedFormat('d F Y, H:i') }} · {{ $boyut($sonYedek['database_size'] ?? 0) }} ·
                    toplam {{ $backupSummary['total'] }} yedek, {{ $dogrulanmis }} tanesi doğrulanmış
                </p>
            @else
                <p class="hc-heading-3">Henüz yedek yok.</p>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">İlk yedeği yukarıdaki düğmeyle alabilirsiniz.</p>
            @endif
        </div>

        @if($backups->isNotEmpty())
            <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
                <h2 class="hc-heading-3">Geçmiş</h2>
                <form method="post" action="{{ route('admin.backups.cleanup') }}">
                    @csrf
                    <button class="hc-btn hc-btn-outline">Eskileri temizle (deneme)</button>
                </form>
            </div>

            <div class="mt-4 grid gap-2">
                @foreach($backups as $backup)
                    @php($zaman = \Illuminate\Support\Carbon::parse($backup['created_at']))
                    @php($saglam = ($backup['status'] ?? null) === 'verified')
                    <article data-backup-row class="hc-v3-card flex flex-wrap items-center gap-x-5 gap-y-3 p-4">
                        <span class="min-w-[9rem] flex-1">
                            <span class="block font-bold">{{ $zaman->translatedFormat('j M Y, H:i') }}</span>
                            <span class="hc-caption block" style="color: var(--hc-text-muted)">{{ $zaman->diffForHumans() }}</span>
                        </span>

                        <span @class([
                            'hc-v3-badge shrink-0',
                            'hc-v3-badge--success' => $saglam,
                            'hc-v3-badge--warning' => ! $saglam,
                        ])>{{ $saglam ? 'Doğrulandı' : 'Doğrulanmadı' }}</span>

                        <span class="hc-body-sm shrink-0 tabular-nums" style="color: var(--hc-text-secondary)">{{ $boyut($backup['database_size'] ?? 0) }}</span>

                        <span class="hc-caption min-w-0 flex-1 truncate" style="color: var(--hc-text-muted)" title="{{ $backup['backup_id'] }}">
                            {{ $backup['source_environment'] ?? '—' }} · {{ \Illuminate\Support\Str::limit($backup['app_version'] ?? '—', 12, '') }}
                        </span>

                        <span class="flex shrink-0 items-center gap-2">
                            @unless($saglam)
                                <form method="post" action="{{ route('admin.backups.verify', $backup['backup_id']) }}">
                                    @csrf
                                    <button class="hc-btn hc-btn-outline hc-btn--sm">Doğrula</button>
                                </form>
                            @endunless
                            <a class="hc-btn hc-btn-outline hc-btn--sm" href="{{ route('admin.backups.download', $backup['backup_id']) }}">İndir</a>
                        </span>
                    </article>
                @endforeach
            </div>

            @if($backups->hasPages())
                <div class="mt-6">{{ $backups->links() }}</div>
            @endif
        @endif
    </section>
</x-layouts.site>
