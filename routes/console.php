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
