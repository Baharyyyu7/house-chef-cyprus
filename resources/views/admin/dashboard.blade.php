<x-layouts.site title="Yönetim">
    <section class="mx-auto max-w-6xl px-5 py-12">
        <p class="font-bold text-orange-700">YÖNETİM PANELİ</p>
        <h1 class="mt-2 text-4xl font-black">Sistem durumu</h1>

        @if (session('status'))
            <p class="mt-4 rounded-xl bg-emerald-50 p-3 text-emerald-900">{{ session('status') }}</p>
        @endif

        <div class="mt-8 grid gap-5 md:grid-cols-3">
            <div class="rounded-2xl bg-white p-6 shadow-sm"><p class="text-stone-500">Toplam tarif</p><strong class="text-4xl">{{ $recipeCount }}</strong></div>
            <div class="rounded-2xl bg-white p-6 shadow-sm"><p class="text-stone-500">Kullanıcılar</p><strong class="text-4xl">{{ $userCount }}</strong></div>
            <div class="rounded-2xl bg-white p-6 shadow-sm"><p class="text-stone-500">Veri kaynakları</p><strong class="text-4xl">{{ $sourceCount }}</strong></div>
        </div>

        <p class="mt-6 text-sm text-stone-500">Son yenileme: {{ $lastRefreshAt ?? 'Henüz manuel yenileme yapılmadı' }}</p>
        <section class="mt-4 rounded-2xl bg-white p-6 shadow"><div class="flex justify-between"><h2 class="text-xl font-black">Health: {{ $health['status'] }}</h2><a class="font-bold text-orange-700" href="{{ route('admin.system-events.index') }}">Sistem olayları →</a></div><div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">@foreach($health['components'] as $component)<div class="rounded border p-3"><strong>{{ $component['name'] }}</strong><span class="block text-sm text-stone-600">{{ $component['status'] }} · {{ $component['safe_message'] }}</span></div>@endforeach</div></section>
        <section class="mt-4 rounded-2xl bg-white p-6 shadow"><div class="flex gap-3"><h2 class="text-xl font-black">Metrikler · {{ $metrics['period_days'] }} gün</h2><a href="?period=1">24s</a><a href="?period=7">7g</a><a href="?period=30">30g</a></div><dl class="mt-3 grid grid-cols-3 gap-3"><dt>Request <b>{{ $metrics['requests'] }}</b></dt><dt>Hata <b>{{ $metrics['errors'] }}</b></dt><dt>Ort. süre <b>{{ $metrics['average_duration_ms'] }} ms</b></dt></dl></section>

        <section class="mt-4 grid gap-5 lg:grid-cols-3">
            <article class="rounded-2xl bg-white p-6 shadow">
                <div class="flex items-start justify-between gap-3"><h2 class="text-xl font-black">Queue</h2><span class="rounded-full bg-stone-100 px-2 py-1 text-xs">{{ $queueHealth['overall_status'] }}</span></div>
                <dl class="mt-4 grid grid-cols-2 gap-2 text-sm"><dt>Pending</dt><dd>{{ $queueHealth['pending_jobs'] }}</dd><dt>Reserved</dt><dd>{{ $queueHealth['reserved_jobs'] }}</dd><dt>Failed</dt><dd>{{ $queueHealth['failed_jobs'] }}</dd><dt>Stuck</dt><dd>{{ $queueHealth['stuck_jobs'] }}</dd><dt>Tekrarlayan hata</dt><dd>{{ $queueHealth['repeated_failures'] }}</dd><dt>Worker</dt><dd>{{ $queueHealth['worker_count'] }}</dd><dt>Sağlıklı</dt><dd>{{ $queueHealth['healthy_workers'] }}</dd><dt>Ulaşılamayan</dt><dd>{{ $queueHealth['unavailable_workers'] }}</dd></dl>
                <div class="mt-5 flex flex-wrap gap-3"><form method="post" action="{{ route('admin.system-status.refresh-queue') }}">@csrf<button class="font-bold text-orange-700">Queue yenile</button></form><a class="font-bold text-orange-700" href="{{ route('admin.failed-jobs.index') }}">Başarısız işler →</a></div>
            </article>
            <article class="rounded-2xl bg-white p-6 shadow">
                <div class="flex items-start justify-between gap-3"><h2 class="text-xl font-black">Scheduler</h2><span class="rounded-full bg-stone-100 px-2 py-1 text-xs">{{ $schedulerHealth['status'] }}</span></div>
                <dl class="mt-4 grid grid-cols-2 gap-2 text-sm"><dt>Kayıtlı görev</dt><dd>{{ $schedulerHealth['task_count'] }}</dd><dt>Son görev</dt><dd>{{ $schedulerHealth['last_task_name'] ?? 'Henüz yok' }}</dd><dt>Son süre</dt><dd>{{ isset($schedulerHealth['last_duration_ms']) ? $schedulerHealth['last_duration_ms'].' ms' : '—' }}</dd><dt>Son çalışma</dt><dd>{{ $schedulerHealth['last_completed_at'] ?? '—' }}</dd></dl>
                <form class="mt-5" method="post" action="{{ route('admin.system-status.refresh-scheduler') }}">@csrf<button class="font-bold text-orange-700">Scheduler yenile</button></form>
            </article>
            <article class="rounded-2xl bg-white p-6 shadow">
                <h2 class="text-xl font-black">Cache</h2><dl class="mt-4 grid grid-cols-2 gap-2 text-sm"><dt>Sürücü</dt><dd>{{ $cacheHealth['driver'] }}</dd><dt>Durum</dt><dd>{{ $cacheHealth['status'] }}</dd></dl><form class="mt-5" method="post" action="{{ route('admin.system-status.refresh-cache') }}">@csrf<button class="font-bold text-orange-700">Cache yenile</button></form>
            </article>
            <article class="rounded-2xl bg-white p-6 shadow">
                <div class="flex items-start justify-between gap-3"><h2 class="text-xl font-black">Medya sağlığı</h2><a class="text-sm font-bold text-orange-700" href="{{ route('admin.media.index') }}">Medya →</a></div>
                <dl class="mt-4 grid grid-cols-2 gap-2 text-sm"><dt>Public yazılabilir</dt><dd>{{ $mediaHealth['public_writable'] ? 'Evet' : 'Hayır' }}</dd><dt>Private yazılabilir</dt><dd>{{ $mediaHealth['private_writable'] ? 'Evet' : 'Hayır' }}</dd><dt>Başarısız işleme</dt><dd>{{ $mediaHealth['failed_processing'] }}</dd><dt>Bekleyen thumbnail</dt><dd>{{ $mediaHealth['pending_thumbnails'] }}</dd><dt>Son cleanup</dt><dd>{{ $mediaHealth['last_cleanup'] ?? '—' }}</dd></dl>
            </article>
        </section>

        <a class="mt-8 inline-block rounded-xl bg-orange-700 px-5 py-3 font-bold text-white" href="{{ route('admin.recipes.index') }}">Tarifleri yönet</a>
    </section>
</x-layouts.site>
