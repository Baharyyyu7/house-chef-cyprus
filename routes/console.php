<?php

use App\Services\HealthService;
use App\Services\Integrity\IntegrityReportLog;
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

// Günlük yedek oluşturuluyordu ama hiç doğrulanmıyordu: deploy scripti
// create'ten sonra verify çağırdığı için yalnız deploy öncesi yedekler
// doğrulanmış görünüyor, günlük yedek `created` durumunda kalıyor ve
// yedek sağlığı her gün `degraded`'a düşüyordu.
//
// Doğrulama salt okunur: manifest, dosya varlığı, checksum ve sürücüye
// özgü yapısal kontrol. Doğrulanmamış yedek, yedek sayılmaz - bozuk
// olduğu ancak ihtiyaç anında anlaşılır.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('backup_verify', fn () => Artisan::call('backup:verify')))->name('backup_verify')->dailyAt('00:35')->withoutOverlapping()->when(fn (): bool => (bool) config('observability.backup_scheduled'));

// Doğrulama checksum'a bakıyor, içeriğe bakmıyor: bir migration yeni tablo
// eklediğinde ya da döküm bir tabloyu dışladığında checksum yine tutar ve
// yedek "doğrulanmış" görünür. Eksik olduğu geri yükleme anında anlaşılır.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('backup_completeness', fn () => Artisan::call('backup:completeness')))->name('backup_completeness')->dailyAt('00:45')->withoutOverlapping()->when(fn (): bool => (bool) config('observability.backup_scheduled'));
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
Schedule::call(fn () => app(SchedulerHealthService::class)->run('asset_delivery_check', fn () => Artisan::call('assets:delivery-check')))->name('asset_delivery_check')->dailyAt('01:10')->withoutOverlapping();
// Görsel eksiği sırası. Yayındaki tariflerin yarısına yakını onaylı
// görseli olmadığı için yer tutucuyla gösteriliyor; ziyaretçinin gördüğü
// en büyük eksiklik bu. Görsel üretimi başka ajanlara veriliyor, bu görev
// onlara verilecek sırayı sitenin kendi verisinden üretiyor.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('media_gap_queue', fn () => Artisan::call('media:gap-queue')))->name('media_gap_queue')->dailyAt('01:15')->withoutOverlapping();
// Çeviri hazırlığı. Site dört dil için hazır ama hiç çeviri yok ve
// dillerin dördü de kapalı - doğru durum, çünkü çevirisi olmayan kayıt o
// dilde var olmuyor. Tehlike sıralamada: Türkçe tarafı eksik bir tarifi
// çevirmek hatayı dille çarpar ve bu site zaten bir kez ölçekli zayıf
// içerik yüzünden ceza yedi. Bu görev hangi tarifin çevrilmeye hazır
// olduğunu ve baskın engelin ne olduğunu ölçüyor.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('translation_readiness', fn () => Artisan::call('content:translation-readiness')))->name('translation_readiness')->dailyAt('01:20')->withoutOverlapping();

// İç bağlantılar denetleniyor: 404 verenler ve 200 dönüp sıfır tarif
// listeleyen katalog filtreleri. İkincisi üretimde aylarca fark edilmedi.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('link_integrity', fn () => Artisan::call('content:link-integrity')))->name('link_integrity')->dailyAt('01:25')->withoutOverlapping();

// Kayıtlı sıfır sonuçlu aramalar güncel katalogla yeniden deneniyor:
// karşılanmış talep, karşılanmamış gibi görünüp içerik görevi üretmesin.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('zero_result_recheck', fn () => Artisan::call('search:recheck-zero-results')))->name('zero_result_recheck')->dailyAt('01:25')->withoutOverlapping();

// Koşu geçmişi trend için tutuluyor, arşiv için değil: üç aylık pencere
// panelin gösterdiği her şeyi karşılıyor.
Schedule::call(fn () => app(IntegrityReportLog::class)->prune(90))->name('integrity_report_prune')->weeklyOn(1, '02:10')->withoutOverlapping();
// Salt okunur sözleşme muhafızı yalnız tablo + sütun genişliği + sabit değer
// birlikte çözülebildiğinde bulgu üretir; belirsiz/dinamik yazımlar manşete girmez.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('database_schema_contract', fn () => Artisan::call('db:schema-contract')))->name('database_schema_contract')->dailyAt('01:30')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_audit', fn () => Artisan::call('seo:audit')))->name('seo_audit')->dailyAt('01:35')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_sitemap_audit', fn () => Artisan::call('seo:sitemap-audit')))->name('seo_sitemap_audit')->dailyAt('01:25')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_content_opportunities', fn () => Artisan::call('seo:content-opportunities')))->name('seo_content_opportunities')->dailyAt('01:40')->withoutOverlapping();
Schedule::command('seo:growth-refresh', ['--limit' => 1000])->name('seo_growth_refresh')->dailyAt('01:45')->withoutOverlapping();
Schedule::command('seo:backlinks-verify', ['--limit' => 20])->name('seo_backlinks_verify')->dailyAt('02:15')->withoutOverlapping();
Schedule::command('seo:search-console-import')->name('seo_search_console_import')->dailyAt('06:45')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_content_refresh', fn () => Artisan::call('seo:content-refresh')))->name('seo_content_refresh')->dailyAt('02:10')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_internal_links', fn () => Artisan::call('seo:internal-links')))->name('seo_internal_links')->dailyAt('05:20')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_social_drafts', fn () => Artisan::call('seo:social-drafts')))->name('seo_social_drafts')->dailyAt('05:40')->withoutOverlapping();
// IndexNow: Bing ve Yandex'e "şu adresler değişti" der. Komut vardı ama
// hiç zamanlanmamıştı; yani "anında bildirir" diyen bir araç kimse
// çağırmadığı için hiç çalışmıyordu.
//
// Yapılandırma kapalıyken sessizce atlanır. Dış servise istek gitmesi
// yapılandırma kararı, kod kararı değil: INDEXNOW_ENABLED=true yeter.
// Otonom Çok Dilli İçerik İçe Alma: Teslim edilen ve doğrulanan çevirileri
// her gece otomatik olarak veritabanına işler; sitemap ve IndexNow anında bunları görür.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('content_import_translations', fn () => Artisan::call('content:import-translations', ['--apply' => true])))->name('content_import_translations')->dailyAt('02:20')->withoutOverlapping();

Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_indexnow_submit', fn () => Artisan::call('seo:indexnow-submit')))->name('seo_indexnow_submit')->dailyAt('02:30')->withoutOverlapping()->when(fn (): bool => (bool) config('seo.indexnow.enabled'));

// IP→ülke veritabanı (DB-IP, CC BY 4.0) ayda bir güncellenir. Komut yeni
// dosyayı doğrulamadan eskisinin yerine koymaz; indirme başarısız olursa
// ülke sayacı eski dosyayla çalışmaya devam eder.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('geoip_update', fn () => Artisan::call('geoip:update')))->name('geoip_update')->monthlyOn(4, '04:40')->withoutOverlapping();

// Blog özgünlüğü: birebir tekrar eden paragrafları olan yazılar noindex olur
// ve site haritasından çıkar (içerik silinmez). Sitemap ve IndexNow'dan önce
// çalışır ki tekrar içerik arama motoruna bildirilmesin.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_originality_scan', fn () => Artisan::call('seo:originality-scan', ['--apply' => true])))->name('seo_originality_scan')->dailyAt('01:35')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_recipe_quality_scan', fn () => Artisan::call('seo:recipe-quality-scan', ['--apply' => true])))->name('seo_recipe_quality_scan')->dailyAt('02:05')->withoutOverlapping();
// Aynı başlıkla iki sayfa aynı sorguda yarışırsa ikisi de zayıflar.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_blog_duplicate_title', fn () => Artisan::call('seo:blog-duplicate-title-consolidation', ['--apply' => true])))->name('seo_blog_duplicate_title')->dailyAt('02:20')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_ranked_url_guard', fn () => Artisan::call('seo:ranked-url-guard')))->name('seo_ranked_url_guard')->dailyAt('07:00')->withoutOverlapping();
// Büyüme iddiası ancak bir önceki ölçümle yan yana konunca doğrulanabilir;
// aynı koşu ilk yirmide görünüp tıklanmayan sayfalar için iş kaydı açar.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_daily_growth_report', fn () => Artisan::call('seo:daily-growth-report')))->name('seo_daily_growth_report')->dailyAt('07:05')->withoutOverlapping();
// Üretim verisinde bir daha olmaması gereken durumlar. Test paketi kodu
// koruyor; bu kurallar veriyi koruyor - hiçbiri testleri kırmadan oluştu.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('app_invariants', fn () => Artisan::call('app:invariants')))->name('app_invariants')->dailyAt('07:10')->withoutOverlapping();

// Claude Coni: SEO, GEO ve backlink çalışanı. Her açık dilde ölçer,
// süresi dolan değişiklikleri önce/sonra penceresiyle değerlendirir ve
// odağı olmayan dile tek sayfa seçip konu komşularından iç bağlantı verir.
// Servis doğrudan çağrılıyor: tetikleme ucu HTTP içinden koşuyor ve orada
// özel Artisan komutları her zaman bulunamıyor. onOneServer aynı dakikada
// iki tetiklemenin görevi iki kez koşturmasını da engelliyor; 07:05
// görevi bu yüzden her sabah iki kez koşuyordu.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('claude_coni', fn () => app(\App\Services\Coni\ClaudeConi::class)->calis()))->name('claude_coni')->dailyAt('07:20')->withoutOverlapping()->onOneServer();

// Dil dil büyüme. Sabah brifingden hemen önce çalışır ki brifing taze
// tabloyu görsün. Açık ama içeriği olmayan bir dil toplamların içinde
// kaybolur; burada kaybolmuyor.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_locale_growth', fn () => Artisan::call('seo:locale-growth')))->name('seo_locale_growth')->dailyAt('06:50')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_autonomous_growth', fn () => Artisan::call('seo:autonomous-growth')))->name('seo_autonomous_growth')->dailyAt('02:45')->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_morning_brief', fn () => Artisan::call('seo:morning-brief')))->name('seo_morning_brief')->dailyAt('06:55')->withoutOverlapping();
Schedule::call(fn () => app(\App\Services\Seo\GrowthSystem::class)->dispatch())->name('seo_growth_system')->hourly()->withoutOverlapping();
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

// SEO Auto Drip: ortak kalite kapısını geçen ve insan onayı kayıtlı blogları günde en fazla 3 yayınlar.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_auto_drip', fn () => Artisan::call('seo:auto-drip', ['--limit' => 3])))->name('seo_auto_drip')->dailyAt('08:00')->withoutOverlapping();

// GBP Content Drip: Haftalık Google Business Profile içerik taslakları üretir.
// storage/app/content-import/gbp-posts/ klasörüne JSON yazar. Otomatik yayın yok.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_gbp_content_drip', fn () => Artisan::call('seo:gbp-content-drip', ['--week' => 3])))->name('seo_gbp_content_drip')->weeklyOn(1, '08:30')->withoutOverlapping();

// Backlink Outreach Queue: Haftalık yüksek otoriteli yayıncılar için kişiselleştirilmiş iş birliği dosyaları hazırlar.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_backlink_outreach_queue', fn () => Artisan::call('seo:backlink-outreach-queue', ['--limit' => 20, '--export-md' => true])))->name('seo_backlink_outreach_queue')->weeklyOn(2, '04:00')->withoutOverlapping();

// Keyword Cluster Build: Semantik Hub-Spoke iç bağlantı haritasını ve küme otoritesini otomatik günceller.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_keyword_cluster_build', fn () => Artisan::call('seo:keyword-cluster-build', ['--export-json' => true])))->name('seo_keyword_cluster_build')->weeklyOn(3, '04:15')->withoutOverlapping();
