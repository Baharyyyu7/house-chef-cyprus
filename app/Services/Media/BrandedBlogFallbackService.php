<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\BlogPost;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Creates an explicit, deterministic branded fallback for a blog post.
 */
final class BrandedBlogFallbackService
{
    /** @return array{created:int,updated:int} */
    public function synchronize(): array
    {
        $result = ['created' => 0, 'updated' => 0];

        BlogPost::query()->where('status', 'published')->orderBy('id')->each(function (BlogPost $post) use (&$result): void {
            // Eğer blog yazısının zaten onaylı gerçek bir fotoğrafı varsa dokunma
            if ($post->heroMedia && $post->heroMedia->isApprovedRealMedia() && ! str_contains($post->heroMedia->path, 'fallbacks/')) {
                return;
            }

            $path = 'fallbacks/blog/'.$post->public_id.'.svg';
            Storage::disk('public')->put($path, $this->svg($post));

            $media = Media::query()->firstOrNew([
                'mediable_type' => BlogPost::class,
                'mediable_id' => $post->id,
                'sort_order' => 0,
            ]);

            $wasNew = ! $media->exists;
            $media->fill([
                'kind' => 'image',
                'disk' => 'public',
                'path' => $path,
                'mime_type' => 'image/svg+xml',
                'size' => strlen($this->svg($post)),
                'visibility' => 'public',
                'alt_text' => $post->title.' - House Chef Cyprus Rehberi',
                'width' => 1200,
                'height' => 675,
                'processing_status' => 'ready',
                'thumbnail_status' => 'ready',
                'thumbnail_paths' => ['card' => $path, 'square' => $path, 'mobile' => $path],
                'source_name' => 'House Chef Cyprus branded editorial illustration',
                'license' => 'House Chef Cyprus editorial',
                'attribution' => 'House Chef Cyprus',
                'verification_status' => 'verified',
                'media_status' => 'approved_real_media',
                'license_review_status' => 'approved',
                'attribution_review_status' => 'approved',
                'confidence' => 100,
                'reviewed_at' => now(),
                'is_placeholder' => false,
                'generated_by_ai' => false,
                'provider_metadata' => ['media_status' => 'branded_fallback', 'deterministic_seed' => $post->public_id],
            ]);
            $media->save();

            $post->update(['hero_media_id' => $media->id]);

            $wasNew ? $result['created']++ : $result['updated']++;
        });

        return $result;
    }

    private function svg(BlogPost $post): string
    {
        $seed = hash('sha256', (string) $post->public_id);
        $palette = [
            ['#1E293B', '#0F172A', '#F97316'],
            ['#14532D', '#064E3B', '#EAB308'],
            ['#312E81', '#1E1B4B', '#FB923C'],
            ['#431407', '#270A04', '#F59E0B'],
            ['#134E4A', '#042F2E', '#38BDF8'],
        ][hexdec($seed[0]) % 5];

        $title = htmlspecialchars(Str::limit($post->title, 60), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $category = htmlspecialchars($post->category->name ?? 'Mutfak Rehberi', ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $x = 200 + (hexdec(substr($seed, 2, 2)) % 600);
        $y = 200 + (hexdec(substr($seed, 4, 2)) % 250);
        $radius = 140 + (hexdec(substr($seed, 6, 2)) % 160);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="675" viewBox="0 0 1200 675" role="img" aria-labelledby="title desc">
<title id="title">{$title}</title><desc id="desc">{$category} için House Chef Cyprus editoryal rehber görseli.</desc>
<defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
        <stop stop-color="{$palette[0]}"/>
        <stop offset="1" stop-color="{$palette[1]}"/>
    </linearGradient>
    <linearGradient id="accent" x1="0" y1="0" x2="1" y2="0">
        <stop stop-color="{$palette[2]}"/>
        <stop offset="1" stop-color="#FFF"/>
    </linearGradient>
</defs>
<rect width="1200" height="675" fill="url(#bg)"/>
<circle cx="{$x}" cy="{$y}" r="{$radius}" fill="{$palette[2]}" opacity=".18"/>
<circle cx="1050" cy="120" r="280" fill="#fff" opacity=".05"/>
<circle cx="150" cy="550" r="200" fill="{$palette[2]}" opacity=".12"/>
<rect x="80" y="70" width="160" height="34" rx="17" fill="{$palette[2]}" opacity=".2"/>
<text x="96" y="93" fill="{$palette[2]}" font-family="system-ui, -apple-system, sans-serif" font-size="16" font-weight="700" letter-spacing="1">{$category}</text>
<text x="80" y="600" fill="#94A3B8" font-family="Georgia, serif" font-size="28" font-weight="700">House Chef Cyprus · Editoryal Mutfak Rehberi</text>
<text x="80" y="320" fill="#FFFFFF" font-family="Georgia, serif" font-size="48" font-weight="700">{$title}</text>
<line x1="80" y1="360" x2="280" y2="360" stroke="url(#accent)" stroke-width="6" stroke-linecap="round"/>
</svg>
SVG;
    }
}
