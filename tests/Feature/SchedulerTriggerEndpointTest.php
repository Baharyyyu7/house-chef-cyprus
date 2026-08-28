<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class SchedulerTriggerEndpointTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-belirteci-1234567890abcdef';

    public function test_the_endpoint_is_closed_when_no_token_is_configured(): void
    {
        // Varsayılan kapalı: token tanımlı değilse uç yokmuş gibi davranır.
        config()->set('app.scheduler_trigger_token', '');

        $this->get('/_zamanlayici/herhangi-bir-sey')->assertNotFound();
    }

    public function test_a_wrong_token_reveals_nothing(): void
    {
        // 403 dönmek ucun var olduğunu doğrulardı; 404 hiçbir şey söylemiyor.
        config()->set('app.scheduler_trigger_token', self::TOKEN);

        $this->get('/_zamanlayici/yanlis-belirtec')->assertNotFound();
    }

    public function test_the_correct_token_runs_the_scheduler(): void
    {
        config()->set('app.scheduler_trigger_token', self::TOKEN);

        $this->get('/_zamanlayici/'.self::TOKEN)->assertNoContent();
    }

    public function test_an_empty_token_in_the_url_does_not_match_an_empty_config(): void
    {
        // Boş token'la boş yapılandırmanın eşleşmesi ucu herkese açardı.
        config()->set('app.scheduler_trigger_token', '');

        $this->get('/_zamanlayici/')->assertNotFound();
    }

    public function test_a_second_call_while_one_is_running_is_refused(): void
    {
        // Uç dışarıya açık; art arda çağrılırsa zamanlayıcı paralel koşardı.
        config()->set('app.scheduler_trigger_token', self::TOKEN);

        $kilit = \Illuminate\Support\Facades\Cache::lock('scheduler-trigger', 55);
        $kilit->get();

        $this->get('/_zamanlayici/'.self::TOKEN)->assertStatus(429);

        $kilit->release();
    }

    public function test_the_endpoint_is_rate_limited(): void
    {
        config()->set('app.scheduler_trigger_token', self::TOKEN);

        for ($i = 0; $i < 6; $i++) {
            $this->get('/_zamanlayici/'.self::TOKEN);
        }

        $this->get('/_zamanlayici/'.self::TOKEN)->assertStatus(429);
    }

    public function test_the_scheduler_command_is_actually_invoked(): void
    {
        config()->set('app.scheduler_trigger_token', self::TOKEN);

        Artisan::shouldReceive('call')
            ->once()
            ->with('schedule:run', ['--no-interaction' => true]);
        // Uç, hangi görevin koştuğunu loglayabilmek için çıktıyı da okuyor.
        Artisan::shouldReceive('output')->andReturn('');
        // Kuyruk ayrıca ve doğrudan boşaltılıyor; zamanlamadaki görev bu
        // bağlamda üretimde koşmuyor.
        Artisan::shouldReceive('call')->once()->with('queue:drain-bounded', \Mockery::type('array'));

        $this->get('/_zamanlayici/'.self::TOKEN)->assertNoContent();
    }
}
