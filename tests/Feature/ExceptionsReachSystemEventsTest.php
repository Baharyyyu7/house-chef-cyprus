<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemEvent;
use App\Services\SchedulerHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

final class ExceptionsReachSystemEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_web_exception_is_recorded_once_and_counted(): void
    {
        // Eskiden yalnız API hataları sistem olaylarına düşüyordu.
        Route::middleware('web')->get('/_test-patlayan-sayfa/{id}', static fn (string $id) => throw new RuntimeException("Tarif {$id} bulunamadı"));

        $this->get('/_test-patlayan-sayfa/12')->assertStatus(500);
        $this->get('/_test-patlayan-sayfa/34')->assertStatus(500);

        // Aynı hata, farklı kimlik: tek satır, iki tekrar.
        $olaylar = SystemEvent::query()->where('event_type', 'application_error')->get();
        $this->assertCount(1, $olaylar);
        $this->assertSame(2, $olaylar[0]->metadata['occurrences']);
        $this->assertSame('web', $olaylar[0]->metadata['context']);
        $this->assertStringContainsString('RuntimeException', $olaylar[0]->metadata['summary']);
    }

    public function test_a_failing_scheduled_task_is_recorded(): void
    {
        try {
            app(SchedulerHealthService::class)->run('seo_audit', static fn () => throw new RuntimeException('denetim çöktü'));
        } catch (RuntimeException) {
        }

        $olay = SystemEvent::query()->where('event_type', 'application_error')->sole();
        $this->assertStringContainsString('denetim çöktü', $olay->metadata['summary']);
    }

    public function test_ignored_exceptions_do_not_fill_the_table(): void
    {
        // 404 gibi çerçevenin raporlamadığı istisnalar olay sayılmaz.
        Route::middleware('web')->get('/_test-yok', static fn () => throw new NotFoundHttpException);

        $this->get('/_test-yok')->assertNotFound();

        $this->assertSame(0, SystemEvent::query()->where('event_type', 'application_error')->count());
    }

    public function test_api_errors_are_not_recorded_twice(): void
    {
        Route::middleware('api')->prefix('api')->get('/_test-api-patla', static fn () => throw new RuntimeException('api'));

        $this->getJson('/api/_test-api-patla')->assertStatus(500);

        $olay = SystemEvent::query()->where('event_type', 'application_error')->sole();
        $this->assertSame(1, $olay->metadata['occurrences']);
    }
}
