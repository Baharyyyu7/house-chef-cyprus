<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class MediaGenerationQueueCommandTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir()."/media queue's ".bin2hex(random_bytes(8));
        foreach (['scripts', 'storage/app', 'docs'] as $path) {
            File::makeDirectory($this->directory.'/'.$path, 0700, true);
        }
        File::copy(base_path('scripts/image-queue.php'), $this->directory.'/scripts/image-queue.php');
        File::put($this->queuePath(), json_encode([
            'total' => 1, 'completed' => 0, 'claimed' => 0, 'pending' => 1, 'blocked' => 0,
            'items' => ['test-gozleme' => [
                'slug' => 'test-gozleme', 'title' => 'Hellimli Gözleme',
                'cuisine' => 'Kıbrıs', 'category' => 'Hamur İşi',
                'target_filename' => 'recipe--test-gozleme.png', 'prompt' => 'Food',
                'status' => 'pending', 'claimed_by' => null, 'claimed_at' => null,
            ]],
        ], JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    public function test_status_runs_the_queue_script_without_mutating_the_queue(): void
    {
        $before = File::get($this->queuePath());
        $process = $this->runQueue(['action' => 'status']);

        $this->assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $this->assertStringContainsString('Toplam Eksik Tarif', $process->getOutput());
        $this->assertSame($before, File::get($this->queuePath()));
        $this->assertFileDoesNotExist($this->directory.'/storage/app/media_generation_queue.lock');
    }

    public function test_arguments_and_failure_exit_codes_reach_the_real_queue(): void
    {
        $worker = "Worker's test";
        $claim = $this->runQueue(['action' => 'claim', '--worker' => $worker]);
        $this->assertSame(0, $claim->getExitCode(), $claim->getOutput().$claim->getErrorOutput());
        $this->assertSame($worker, json_decode($claim->getOutput(), true, 512, JSON_THROW_ON_ERROR)['worker']);

        $wrongOwner = $this->runQueue(['action' => 'release', '--worker' => 'someone else', '--slug' => 'test-gozleme']);
        $this->assertSame(1, $wrongOwner->getExitCode());
        $this->assertSame('claimed', $this->queue()['items']['test-gozleme']['status']);

        $reason = "Tarifin adı 'eksik'; kimlik doğrulanamadı.";
        $blocked = $this->runQueue(['action' => 'block', '--worker' => $worker, '--slug' => 'test-gozleme', '--reason' => $reason]);
        $this->assertSame(0, $blocked->getExitCode(), $blocked->getOutput().$blocked->getErrorOutput());
        $this->assertSame('blocked', $this->queue()['items']['test-gozleme']['status']);
        $this->assertSame($reason, $this->queue()['items']['test-gozleme']['blocked_reason']);
    }

    public function test_disabled_passthru_fails_with_guidance_and_preserves_the_queue(): void
    {
        $before = File::get($this->queuePath());
        $process = $this->runQueue(['action' => 'claim', '--worker' => 'restricted'], true);

        $this->assertSame(1, $process->getExitCode());
        $this->assertStringContainsString('scripts/image-queue.php', $process->getOutput());
        $this->assertStringNotContainsString('Undefined variable', $process->getOutput());
        $this->assertSame($before, File::get($this->queuePath()));
        $this->assertFileDoesNotExist($this->directory.'/storage/app/media_generation_queue.lock');
    }

    private function runQueue(array $arguments, bool $disablePassthru = false): Process
    {
        $code = 'require '.var_export(base_path('vendor/autoload.php'), true).';'
            .'$app = require '.var_export(base_path('bootstrap/app.php'), true).';'
            .'$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();'
            .'$app->setBasePath('.var_export($this->directory, true).');'
            .'$code = Illuminate\\Support\\Facades\\Artisan::call("media:queue", '.var_export($arguments, true).');'
            .'echo Illuminate\\Support\\Facades\\Artisan::output(); exit($code);';
        $process = new Process([PHP_BINARY, '-d', 'disable_functions='.($disablePassthru ? 'passthru' : ''), '-r', $code]);
        $process->run();

        return $process;
    }

    private function queuePath(): string
    {
        return $this->directory.'/storage/app/media_generation_queue.json';
    }

    private function queue(): array
    {
        return json_decode(File::get($this->queuePath()), true, 512, JSON_THROW_ON_ERROR);
    }
}
