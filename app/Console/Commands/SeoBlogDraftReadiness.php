<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\Seo\BlogPublicationReadinessService;
use Illuminate\Console\Command;

final class SeoBlogDraftReadiness extends Command
{
    protected $signature = 'seo:blog-draft-readiness {--json : Makine okunabilir çıktı} {--limit=0 : Ayrıntı satırı sınırı}';

    protected $description = 'Blog taslaklarının yayın hazırlığını hiçbir kayıt değiştirmeden raporlar.';

    public function handle(BlogPublicationReadinessService $readiness): int
    {
        $rows = BlogPost::query()->where('status', 'draft')->orderBy('id')->get()
            ->map(function (BlogPost $post) use ($readiness): array {
                $assessment = $readiness->evaluate($post);

                return ['id' => $post->id, 'slug' => $post->slug] + $assessment;
            });
        $limit = max(0, (int) $this->option('limit'));
        $result = [
            'drafts' => $rows->count(),
            'ready' => $rows->where('passed', true)->count(),
            'blocked' => $rows->where('passed', false)->count(),
            'posts' => ($limit > 0 ? $rows->take($limit) : $rows)->values()->all(),
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("Taslak {$result['drafts']} | hazır {$result['ready']} | engelli {$result['blocked']}");
        }

        return self::SUCCESS;
    }
}
