<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use Illuminate\Support\Str;

final class BlogDraftPreparationService
{
    public function __construct(private readonly BlogPublicationReadinessService $readiness) {}

    /** @return array{changed:list<string>,issues:list<string>,ready:bool} */
    public function prepare(BlogPost $post): array
    {
        $updates = [];
        if (blank($post->meta_title) && filled($post->title)) {
            $updates['meta_title'] = Str::limit(trim((string) $post->title), 60, '');
        }

        if (blank($post->meta_description)) {
            $source = $this->plainText(filled($post->excerpt) ? (string) $post->excerpt : (string) $post->body);
            if (mb_strlen($source) >= 140) {
                $updates['meta_description'] = Str::limit($source, 158, '');
            }
        }

        if (blank($post->primary_keyword) && filled($post->title)) {
            $updates['primary_keyword'] = Str::limit($this->plainText((string) $post->title), 160, '');
        }

        if ($updates !== []) {
            $post->update($updates);
            $post->refresh();
        }

        $evaluation = $this->readiness->evaluate($post);

        return ['changed' => array_keys($updates), 'issues' => $evaluation['issues'], 'ready' => $evaluation['passed']];
    }

    /** @return array{processed:int,changed:int,ready:int,blocked:int} */
    public function prepareBatch(int $limit = 100): array
    {
        $summary = ['processed' => 0, 'changed' => 0, 'ready' => 0, 'blocked' => 0];
        $limit = max(1, min(100, $limit));

        BlogPost::query()->whereNotIn('status', ['published', 'archived'])->orderBy('id')->limit($limit)->get()
            ->each(function (BlogPost $post) use (&$summary): void {
                $result = $this->prepare($post);
                $summary['processed']++;
                $summary['changed'] += $result['changed'] === [] ? 0 : 1;
                $summary[$result['ready'] ? 'ready' : 'blocked']++;
            });

        return $summary;
    }

    private function plainText(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', strip_tags($value)));
    }
}
