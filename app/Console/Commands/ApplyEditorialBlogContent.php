<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;

/**
 * Derinleştirilmiş blog içerikleri resources/editorial/blog-content altında
 * sürüm kontrolünde durur; bu komut onları veritabanına uygular. Dry-run
 * varsayılandır ve yalnız dosyası olan yazılara dokunur.
 */
final class ApplyEditorialBlogContent extends Command
{
    protected $signature = 'blog:apply-editorial-content
        {--apply : Değişiklikleri veritabanına yaz}
        {--slug= : Yalnız tek bir yazıyı uygula}
        {--json : Makine okunabilir çıktı}';

    protected $description = 'Sürüm kontrolündeki derin blog içeriklerini yayınlanmış yazılara uygular; dry-run varsayılandır.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $only = (string) ($this->option('slug') ?? '');
        $directory = resource_path('editorial/blog-content');

        if (! is_dir($directory)) {
            $this->error('İçerik dizini yok: '.$directory);

            return self::FAILURE;
        }

        $applied = [];
        $missing = [];
        $unchanged = [];

        foreach (glob($directory.'/*.php') ?: [] as $file) {
            $slug = basename($file, '.php');
            if ($only !== '' && $slug !== $only) {
                continue;
            }

            /** @var array<string, mixed> $payload */
            $payload = require $file;
            $post = BlogPost::query()->where('slug', $slug)->first();

            if ($post === null) {
                $missing[] = $slug;

                continue;
            }

            $changes = array_filter([
                'title' => $payload['title'] ?? null,
                'excerpt' => $payload['excerpt'] ?? null,
                'body' => $payload['body'] ?? null,
                'meta_title' => $payload['meta_title'] ?? null,
                'meta_description' => $payload['meta_description'] ?? null,
                'primary_keyword' => $payload['primary_keyword'] ?? null,
                'secondary_keywords' => $payload['secondary_keywords'] ?? null,
                'faq_blocks' => $payload['faq'] ?? null,
            ], static fn (mixed $value): bool => $value !== null);

            $before = mb_strlen((string) $post->body);
            $after = mb_strlen((string) ($changes['body'] ?? $post->body));

            if ($apply) {
                $post->forceFill([...$changes, 'last_refreshed_at' => now()])->save();
            }

            if ($before === $after && ! $apply) {
                $unchanged[] = $slug;
            }

            $applied[] = ['slug' => $slug, 'before' => $before, 'after' => $after, 'faq' => count($payload['faq'] ?? [])];
        }

        $report = [
            'mode' => $apply ? 'apply' : 'dry-run',
            'processed' => count($applied),
            'missing_posts' => $missing,
            'posts' => $applied,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        foreach ($applied as $row) {
            $this->line(sprintf('  %-46s %5d → %5d karakter · %d SSS', $row['slug'], $row['before'], $row['after'], $row['faq']));
        }

        $this->info(sprintf('[%s] işlenen: %d · eşleşmeyen: %d', $report['mode'], count($applied), count($missing)));

        if ($missing !== []) {
            $this->warn('Veritabanında bulunamayan slug: '.implode(', ', $missing));
        }

        if (! $apply && $applied !== []) {
            $this->warn('Yazmak için --apply ile tekrar çalıştırın.');
        }

        return self::SUCCESS;
    }
}
