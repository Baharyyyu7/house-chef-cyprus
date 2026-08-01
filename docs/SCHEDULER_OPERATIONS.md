# Scheduler Operations

Run exactly one scheduler cron: `php artisan schedule:run`. The registry uses overlap protection. Check `app:scheduler-health --json`, last task, duration and delayed/failed state before launch. A cron-only host should run every minute; a lower cadence requires explicit impact acceptance.
