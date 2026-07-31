<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use Illuminate\Support\Str;

final class ContentSimilarityService
{
    /** @return array{status:string,score:float,matches:list<array{public_id:string,title:string}>} */
    public function inspect(BlogPost $post): array
    {
        $needle = $this->normalise($post->title.' '.$post->excerpt);
        $matches = BlogPost::query()->whereKeyNot($post->getKey())->get(['id', 'public_id', 'title', 'excerpt'])
            ->filter(fn (BlogPost $candidate): bool => $this->similarity($needle, $this->normalise($candidate->title.' '.$candidate->excerpt)) >= 0.82)
            ->map(fn (BlogPost $candidate): array => ['public_id' => $candidate->public_id, 'title' => $candidate->title])
            ->values()->all();

        return ['status' => $matches === [] ? 'passed' : 'needs_review', 'score' => $matches === [] ? 0.0 : 0.85, 'matches' => $matches];
    }

    private function normalise(string $value): string
    {
        return trim((string) Str::of($value)->lower()->replaceMatches('/[^\pL\pN]+/u', ' ')->squish());
    }

    private function similarity(string $left, string $right): float
    {
        if ($left === '' || $right === '') {
            return 0.0;
        }
        similar_text($left, $right, $percent);

        return $percent / 100;
    }
}
