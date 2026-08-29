<?php

use App\Services\HealthService;
use App\Services\SchedulerHealthService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(SchedulerHealthService::class)->run('data_quality_scan', fn () => Artisan::call('data:quality-scan')))->name('data_quality_scan')->daily()->withoutOverlapping()->onOneServer();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('cache_warm_lookups', fn () => Artisan::call('cache:warm', ['--section' => 'lookups'])))->name('cache_warm_lookups')->daily()->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('media_cleanup_temporary', fn () => Artisan::call('media:cleanup-temporary', ['--delete' => true])))->name('media_cleanup_temporary')->daily()->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('media_scan_orphans', fn () => Artisan::call('media:scan-orphans')))->name('media_scan_orphans')->daily()->withoutOverlapping();
// These are bounded review-preparation tasks. They never select, approve or publish media.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('media_fidelity_scan', fn () => Artisan::call('media:fidelity-scan', ['--limit' => 25])))->name('media_fidelity_scan')->dailyAt('06:35')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('media_license_audit', fn () => Artisan::call('media:license-audit', ['--limit' => 100])))->name('media_license_audit')->dailyAt('06:40')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('media_attribution_audit', fn () => Artisan::call('media:attribution-audit', ['--limit' => 100])))->name('media_attribution_audit')->dailyAt('06:45')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('media_morning_brief', fn () => Artisan::call('media:morning-brief')))->name('media_morning_brief')->dailyAt('07:00')->withoutOverlapping();
// AI Kitchen brief is operational-only: it never sends provider requests or changes content state.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('ai_kitchen_morning_brief', fn () => Artisan::call('ai-kitchen:morning-brief')))->name('ai_kitchen_morning_brief')->dailyAt('07:10')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('personalization_morning_brief', fn () => Artisan::call('personalization:morning-brief')))->name('personalization_morning_brief')->dailyAt('07:20')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('health_snapshot', fn () => app(HealthService::class)->snapshot()))->name('health_snapshot')->hourly()->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('backup_cleanup', fn () => Artisan::call('backup:cleanup')))->name('backup_cleanup')->daily()->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('backup_daily', fn () => Artisan::call('backup:create', ['--type' => 'daily'])))->name('backup_daily')->dailyAt('00:20')->withoutOverlapping()->when(fn (): bool => (bool) config('observability.backup_scheduled'));
Schedule::call(fn () => app(SchedulerHealthService::class)->run('logs_cleanup', fn () => Artisan::call('logs:cleanup')))->name('logs_cleanup')->daily()->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('blog_publish_scheduled', fn () => Artisan::call('blog:publish-scheduled')))->name('blog_publish_scheduled')->everyMinute()->withoutOverlapping();
// Kuyruk işçisi ayrı bir cron kaydıyla koşuyordu ve Hostinger'ın cron
// servisi 2026-08-02'de durunca o da durdu. Zamanlayıcı artık dışarıdan
// (/_zamanlayici ucu) tetiklendiği için kuyruğu da buraya bağlıyoruz;
// yoksa tetikleme çalışsa bile kuyruk beklemeye devam ederdi.
// queue:work degil queue:drain-bounded: zamanlayici Hostinger'in olu
// cron'u yuzunden bir HTTP isteginin icinden tetikleniyor ve queue:work
// bu baglamda calismiyor - CLI'da ayni gorev 3 saniye surup isleri
// bitirirken HTTP yolunda hata vermeden, is islemeden aninda donuyor.
// Kuyruk adlari acik yazilmali: varsayilan yalniz "default" isler.
// Canlida bekleyen iki is "media" kuyrugundaydi ve bu yuzden 12 gun
// hic islenmedi.
// Süre sınırları tetikleyen HTTP isteğini asmamak için dar tutuldu:
// uç 55 saniyelik bir kilit alıyor.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('queue_drain', fn () => Artisan::call('queue:drain-bounded', ['--queue' => 'default,media,mail,nutrition', '--max-jobs' => 20, '--max-seconds' => 20, '--tries' => 3, '--job-timeout' => 15])))->name('queue_drain')->everyMinute()->withoutOverlapping();
// SEO tasks only derive local drafts, audits and summaries. They never publish content or call external providers without an explicit enabled configuration and approval.
// İçerik bütünlüğü nöbetçisi. Bu sitede arıza hep aynı biçimde geldi:
// bozuk içerik sessizce yayına girdi ve haftalarca fark edilmedi - 3.649
// makine üretimi sayfa, malzemesi "Ana Malzeme" olan 34 tarif, adımları
// yer tutucu olan 38 tarif, mercimek çorbası diye balık isteyen 13 tarif.
// Hepsi "published" ve "verified" damgası taşıyordu.
//
// Nöbetçi içerik üretmiyor, yalnız bakıyor ve raporluyor. Otomatik yayından
// alma bilerek kapalı: karar insanın.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('content_integrity_guard', fn () => Artisan::call('content:integrity-guard')))->name('content_integrity_guard')->dailyAt('01:05')->withoutOverlapping();
// Varlık teslim sondası. Sunucu tarafı kusursuz görünürken tarayıcı
// tarafı kırık olabiliyor: sunucu .avif uzantısını tanımayınca bütün AVIF
// dosyaları text/plain olarak gidiyordu ve sitedeki her görsel gri yer
// tutucuya düşüyordu. HTML doğruydu, dosyalar 200 veriyordu; hiçbir
// sunucu tarafı denetimi bunu yakalayamazdı.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('asset_delivery_check', fn () => Artisan::call('assets:delivery-check')))->name('asset_delivery_check')->dailyAt('01:09')->withoutOverlapping();
// Görsel eksiği sırası. Yayındaki tariflerin yarısına yakını onaylı
// görseli olmadığı için yer tutucuyla gösteriliyor; ziyaretçinin gördüğü
// en büyük eksiklik bu. Görsel üretimi başka ajanlara veriliyor, bu görev
// onlara verilecek sırayı sitenin kendi verisinden üretiyor.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('media_gap_queue', fn () => Artisan::call('media:gap-queue')))->name('media_gap_queue')->dailyAt('01:13')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_audit', fn () => Artisan::call('seo:audit')))->name('seo_audit')->dailyAt('01:17')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_sitemap_audit', fn () => Artisan::call('seo:sitemap-audit')))->name('seo_sitemap_audit')->dailyAt('01:25')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_content_opportunities', fn () => Artisan::call('seo:content-opportunities')))->name('seo_content_opportunities')->dailyAt('01:43')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_content_refresh', fn () => Artisan::call('seo:content-refresh')))->name('seo_content_refresh')->dailyAt('02:11')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_internal_links', fn () => Artisan::call('seo:internal-links')))->name('seo_internal_links')->dailyAt('05:17')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_social_drafts', fn () => Artisan::call('seo:social-drafts')))->name('seo_social_drafts')->dailyAt('05:43')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_morning_brief', fn () => Artisan::call('seo:morning-brief')))->name('seo_morning_brief')->dailyAt('06:59')->withoutOverlapping();
// Draft-only content operations: limits are encoded in the command/config and auto-publish is permanently disabled.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('content_factory_recipe_drafts', fn () => Artisan::call('content:factory-seed', ['--type' => 'recipe', '--daily' => true])))->name('content_factory_recipe_drafts')->dailyAt('03:00')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('content_factory_blog_drafts', fn () => Artisan::call('content:factory-seed', ['--type' => 'blog', '--daily' => true])))->name('content_factory_blog_drafts')->dailyAt('04:30')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('content_factory_recipe_enrichment', fn () => Artisan::call('content:factory-enrich', ['--type' => 'recipe'])))->name('content_factory_recipe_enrichment')->dailyAt('02:00')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('content_factory_blog_enrichment', fn () => Artisan::call('content:factory-enrich', ['--type' => 'blog'])))->name('content_factory_blog_enrichment')->dailyAt('03:30')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('content_factory_fact_check_triage', fn () => Artisan::call('content:fact-check-triage')))->name('content_factory_fact_check_triage')->dailyAt('01:00')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('editorial_operations_plan', fn () => Artisan::call('content:editorial-operations-plan')))->name('editorial_operations_plan')->dailyAt('01:30')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('editorial_source_queue_refresh', fn () => Artisan::call('content:editorial-operations-plan')))->name('editorial_source_queue_refresh')->dailyAt('02:00')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('editorial_recipe_pack_refresh', fn () => Artisan::call('content:editorial-operations-plan')))->name('editorial_recipe_pack_refresh')->dailyAt('04:30')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('editorial_blog_pack_refresh', fn () => Artisan::call('content:editorial-operations-plan')))->name('editorial_blog_pack_refresh')->dailyAt('05:15')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('editorial_media_readiness_refresh', fn () => Artisan::call('content:editorial-operations-plan')))->name('editorial_media_readiness_refresh')->dailyAt('06:00')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('editorial_workload_refresh', fn () => Artisan::call('content:editorial-operations-plan')))->name('editorial_workload_refresh')->dailyAt('06:30')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('editorial_operations_brief', fn () => Artisan::call('content:editorial-operations-brief')))->name('editorial_operations_brief')->dailyAt('07:00')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('content_factory_fact_check_morning_brief', fn () => Artisan::call('content:fact-check-morning-brief')))->name('content_factory_fact_check_morning_brief')->dailyAt('07:00')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('content_factory_morning_brief', fn () => Artisan::call('content:factory-morning-brief')))->name('content_factory_morning_brief')->dailyAt('07:00')->withoutOverlapping();
