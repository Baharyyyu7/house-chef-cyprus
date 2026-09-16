<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class MediaGenerationQueueCommand extends Command
{
    protected $signature = 'media:queue
        {action=status : Action to perform: status, claim, complete, release, block, renew, init}
        {--worker= : Name of the AI worker claiming or completing}
        {--slug= : Recipe slug for complete or release}
        {--reason= : Reason for blocking an invalid recipe}
        {--json : Output in JSON format}';

    protected $description = 'Görsel üretim dağıtık kilit ve koordinasyon kuyruğunu yönetir (5 AI Worker uyumlu).';

    public function handle(): int
    {
        $action = (string) $this->argument('action');
        $worker = (string) ($this->option('worker') ?: 'Worker-' . getmypid());
        $slug = $this->option('slug');

        $args = [escapeshellarg($action)];
        if ($this->option('worker')) {
            $args[] = '--worker=' . escapeshellarg($worker);
        }
        if ($slug) {
            $args[] = '--slug=' . escapeshellarg((string) $slug);
        }

        if ($this->option('reason')) {
            $args[] = '--reason=' . escapeshellarg((string) $this->option('reason'));
        }

        $script = escapeshellarg(base_path('scripts/image-queue.php'));
        $cmd = 'php ' . $script . ' ' . implode(' ', $args);
        passthru($cmd, $exitCode);

        return $exitCode;
    }
}
