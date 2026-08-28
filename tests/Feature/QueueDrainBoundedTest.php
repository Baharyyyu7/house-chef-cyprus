<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class QueueDrainBoundedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('queue.default', 'database');
    }

    public function test_it_processes_a_queued_job(): void
    {
        SayacIsi::dispatch()->onQueue('media');

        $this->assertSame(1, DB::table('jobs')->count());

        $sonuc = $this->calistir('default,media');

        $this->assertSame(1, $sonuc['processed']);
        $this->assertSame(0, $sonuc['remaining']);
        $this->assertSame(1, Cache::get('sayac-isi', 0));
    }

    public function test_a_queue_that_is_not_listed_is_not_drained(): void
    {
        // Canlıda tam bu oldu: bekleyen işler "media" kuyruğundaydı,
        // işçi ise yalnız "default" işliyordu ve kuyruk 12 gün durdu.
        SayacIsi::dispatch()->onQueue('media');

        $sonuc = $this->calistir('default');

        $this->assertSame(0, $sonuc['processed']);
        $this->assertSame(1, $sonuc['remaining']);
    }

    public function test_it_stops_at_the_job_limit(): void
    {
        // Tetikleyen taraf bir HTTP isteği; sınırsız çalışmak isteği asardı.
        for ($i = 0; $i < 5; $i++) {
            SayacIsi::dispatch()->onQueue('media');
        }

        $sonuc = $this->calistir('media', ['--max-jobs' => 2]);

        $this->assertSame(2, $sonuc['processed']);
        $this->assertSame(3, $sonuc['remaining']);
    }

    public function test_an_empty_queue_finishes_immediately(): void
    {
        // Döngü boş kuyrukta kendini sonlandırmalı; aksi halde her tetikleme
        // süre sınırına kadar boşuna beklerdi.
        $baslangic = microtime(true);

        $sonuc = $this->calistir('default,media,mail,nutrition', ['--max-seconds' => 20]);

        $this->assertSame(0, $sonuc['processed']);
        $this->assertLessThan(5, microtime(true) - $baslangic);
    }

    /**
     * @param  array<string, mixed>  $ekstra
     * @return array<string, mixed>
     */
    private function calistir(string $kuyruklar, array $ekstra = []): array
    {
        Artisan::call('queue:drain-bounded', ['--queue' => $kuyruklar, '--json' => true] + $ekstra);

        return (array) json_decode(trim(Artisan::output()), true);
    }
}

final class SayacIsi implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function handle(): void
    {
        Cache::increment('sayac-isi');
    }
}
