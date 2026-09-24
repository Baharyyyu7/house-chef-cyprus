<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class MediaGenerationQueueTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/image-queue-test-' . bin2hex(random_bytes(8));
        foreach (['scripts', 'storage/app', 'docs', 'bin'] as $path) {
            mkdir($this->directory . '/' . $path, 0700, true);
        }
        copy(dirname(__DIR__, 2) . '/scripts/image-queue.php', $this->directory . '/scripts/image-queue.php');
        file_put_contents($this->directory . '/bin/ssh', "#!/bin/sh\nexit 0\n");
        chmod($this->directory . '/bin/ssh', 0700);
        $items = [];
        foreach (['first', 'second', 'third'] as $slug) {
            $items[$slug] = ['slug' => $slug, 'title' => $slug, 'cuisine' => 'Cyprus', 'category' => 'Main',
                'target_filename' => "recipe--{$slug}.png", 'prompt' => 'Food', 'status' => 'pending',
                'claimed_by' => null, 'claimed_at' => null];
        }
        $this->write(['items' => $items]);
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $entry) {
            $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir($this->directory);
    }

    private function write(array $queue): void
    {
        file_put_contents($this->directory . '/storage/app/media_generation_queue.json', json_encode($queue));
    }

    private function read(): array
    {
        return json_decode(file_get_contents($this->directory . '/storage/app/media_generation_queue.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    private function start(array $args): array
    {
        $process = proc_open([PHP_BINARY, $this->directory . '/scripts/image-queue.php', ...$args],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null,
            ['PATH' => $this->directory . '/bin:' . getenv('PATH')]);
        fclose($pipes[0]);
        return [$process, $pipes];
    }

    private function finish(array $running): array
    {
        [$process, $pipes] = $running;
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return [proc_close($process), json_decode($output, true), $error];
    }

    private function runQueue(string ...$args): array
    {
        return $this->finish($this->start($args));
    }

    public function test_concurrent_workers_claim_different_items_and_resume_their_own(): void
    {
        $one = $this->start(['claim', '--worker=one']);
        $two = $this->start(['claim', '--worker=two']);
        [$codeOne, $resultOne] = $this->finish($one);
        [$codeTwo, $resultTwo] = $this->finish($two);
        self::assertSame(0, $codeOne);
        self::assertSame(0, $codeTwo);
        self::assertNotSame($resultOne['recipe']['slug'], $resultTwo['recipe']['slug']);
        self::assertSame($resultOne['recipe']['slug'], $this->runQueue('claim', '--worker=one')[1]['recipe']['slug']);
        self::assertSame(2, $this->read()['claimed']);
    }

    public function test_only_owner_can_mutate_claim_and_completed_jobs_cannot_be_released(): void
    {
        $this->runQueue('claim', '--worker=owner');
        foreach (['complete', 'release', 'block', 'renew'] as $action) {
            self::assertSame(1, $this->runQueue($action, '--slug=first', '--worker=other', '--reason=invalid')[0]);
        }
        self::assertSame(1, $this->runQueue('release', '--slug=first')[0]);
        self::assertSame('claimed', $this->read()['items']['first']['status']);
        self::assertSame(0, $this->runQueue('complete', '--slug=first', '--worker=owner')[0]);
        self::assertSame(1, $this->runQueue('release', '--slug=first', '--worker=owner')[0]);
    }

    public function test_block_requires_reason_and_next_claim_skips_blocked_item(): void
    {
        $this->runQueue('claim', '--worker=owner');
        self::assertSame(1, $this->runQueue('block', '--slug=first', '--worker=owner')[0]);
        self::assertSame(0, $this->runQueue('block', '--slug=first', '--worker=owner', '--reason=Incomplete dish identity')[0]);
        self::assertSame(1, $this->read()['blocked']);
        self::assertSame(2, $this->read()['pending']);
        self::assertSame('second', $this->runQueue('claim', '--worker=owner')[1]['recipe']['slug']);
        self::assertStringContainsString('Incomplete dish identity', file_get_contents($this->directory . '/docs/GORSEL_URETIM_KUYRUK_LISTESI.md'));
    }

    public function test_old_claims_are_not_silently_stolen_and_can_be_renewed(): void
    {
        $this->runQueue('claim', '--worker=owner');
        $queue = $this->read();
        $queue['items']['first']['claimed_at'] = '2000-01-01T00:00:00+00:00';
        $this->write($queue);
        self::assertSame('second', $this->runQueue('claim', '--worker=other')[1]['recipe']['slug']);
        self::assertSame(0, $this->runQueue('renew', '--slug=first', '--worker=owner')[0]);
        self::assertNotSame('2000-01-01T00:00:00+00:00', $this->read()['items']['first']['claimed_at']);
    }

    public function test_invalid_json_fails_without_overwriting_and_upload_is_required(): void
    {
        $this->runQueue('claim', '--worker=owner');
        file_put_contents($this->directory . '/bin/ssh', "#!/bin/sh\nexit 1\n");
        self::assertSame(1, $this->runQueue('complete', '--slug=first', '--worker=owner', '--force')[0]);
        self::assertSame('claimed', $this->read()['items']['first']['status']);
        file_put_contents($this->directory . '/storage/app/media_generation_queue.json', '{broken');
        self::assertNotSame(0, $this->runQueue('claim', '--worker=owner')[0]);
        self::assertSame('{broken', file_get_contents($this->directory . '/storage/app/media_generation_queue.json'));
    }
}
