<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class TriggerDrainsTheQueueDirectlyTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-belirteci-1234567890abcdef';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.scheduler_trigger_token', self::TOKEN);
        config()->set('queue.default', 'database');
    }

    public function test_the_trigger_processes_a_queued_job(): void
    {
        // Zamanlamadaki queue_drain görevi CLI'da çalışıyor ama bu uçtan
        // çağrıldığında üretimde koşmuyor: aynı istekte blog_publish
        // çalışırken queue_drain çalışmıyor. Uç artık kuyruğu doğrudan
        // boşaltıyor, o davranışa bağımlı değil.
        IsaretleyenIs::dispatch()->onQueue('media');

        $this->assertSame(1, DB::table('jobs')->count());

        $this->get('/_zamanlayici/'.self::TOKEN)->assertNoContent();

        $this->assertSame(0, DB::table('jobs')->count());
        $this->assertSame(1, Cache::get('isaretleyen-is', 0));
    }

    public function test_a_wrong_token_does_not_touch_the_queue(): void
    {
        // Kuyruğu boşaltmak bir yan etki; yetkisiz çağrı bunu tetiklememeli.
        IsaretleyenIs::dispatch()->onQueue('media');

        $this->get('/_zamanlayici/yanlis-belirtec')->assertNotFound();

        $this->assertSame(1, DB::table('jobs')->count());
    }

    public function test_an_empty_queue_still_returns_no_content(): void
    {
        $this->get('/_zamanlayici/'.self::TOKEN)->assertNoContent();
    }

    public function test_jobs_on_unlisted_queues_are_left_alone(): void
    {
        // Uçtaki kuyruk listesi ile uygulamanın kullandığı kuyruklar
        // ayrışırsa işler sessizce beklemeye devam eder - canlıda tam
        // olarak bu oldu.
        IsaretleyenIs::dispatch()->onQueue('bilinmeyen-kuyruk');

        $this->get('/_zamanlayici/'.self::TOKEN)->assertNoContent();

        $this->assertSame(1, DB::table('jobs')->count());
    }
}

final class IsaretleyenIs implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function handle(): void
    {
        Cache::increment('isaretleyen-is');
    }
}
