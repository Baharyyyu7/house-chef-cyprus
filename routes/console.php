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
Schedule::call(fn () => app(SchedulerHealthService::class)->run('health_snapshot', fn () => app(HealthService::class)->snapshot()))->name('health_snapshot')->hourly()->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('backup_cleanup', fn () => Artisan::call('backup:cleanup')))->name('backup_cleanup')->daily()->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('logs_cleanup', fn () => Artisan::call('logs:cleanup')))->name('logs_cleanup')->daily()->withoutOverlapping();
Schedule::call(fn () => app(SchedulerHealthService::class)->run('blog_publish_scheduled', fn () => Artisan::call('blog:publish-scheduled')))->name('blog_publish_scheduled')->everyMinute()->withoutOverlapping();
// SEO tasks only derive local drafts, audits and summaries. They never publish content or call external providers without an explicit enabled configuration and approval.
Schedule::call(fn () => app(SchedulerHealthService::class)->run('seo_audit', fn () => Artisan::call('seo:audit')))->name('seo_audit')->dailyAt('01:17')->withoutOverlapping();
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
