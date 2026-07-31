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
