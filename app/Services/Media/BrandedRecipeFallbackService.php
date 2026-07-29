<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Support\Facades\Storage;

/**
 * Creates an explicit, deterministic branded fallback for a recipe.
 *
 * These assets are intentionally illustrations, not food photography. They
 * keep public cards stable when an approved licensed image is unavailable.
 */
final class BrandedRecipeFallbackService
{
    /** @return array{created:int,updated:int} */
    public function synchronize(): array
    {
        $result = ['created' => 0, 'updated' => 0];

        Recipe::query()->where('status', 'published')->orderBy('id')->each(function (Recipe $recipe) use (&$result): void {
            $media = Media::query()->firstOrNew([
                'mediable_type' => Recipe::class,
                'mediable_id' => $recipe->id,
                'sort_order' => 0,
            ]);
            $path = 'fallbacks/recipes/'.$recipe->public_id.'.svg';
            Storage::disk('public')->put($path, $this->svg($recipe));

            $wasNew = ! $media->exists;
            $media->fill([
                'kind' => 'image',
                'disk' => 'public',
                'path' => $path,
                'mime_type' => 'image/svg+xml',
                'size' => strlen($this->svg($recipe)),
                'visibility' => 'public',
                'alt_text' => $recipe->title.' için markalı temsili görsel',
                'width' => 1200,
                'height' => 900,
                'processing_status' => 'ready',
                'thumbnail_status' => 'ready',
                'thumbnail_paths' => ['card' => $path, 'square' => $path, 'mobile' => $path],
                'source_name' => 'House Chef Cyprus branded fallback',
                'license' => 'House Chef Cyprus branded fallback',
                'attribution' => 'House Chef Cyprus',
                'verification_status' => 'branded_fallback',
                'confidence' => 100,
                'reviewed_at' => now(),
                'is_placeholder' => false,
                'generated_by_ai' => false,
                'provider_metadata' => ['media_status' => 'branded_fallback', 'deterministic_seed' => $recipe->public_id],
            ]);
            $media->save();
            $wasNew ? $result['created']++ : $result['updated']++;
        });

        return $result;
    }

    private function svg(Recipe $recipe): string
    {
        $seed = hash('sha256', (string) $recipe->public_id);
        $palette = [
            ['#163D2B', '#4D6B4A', '#D8A24C'],
            ['#254B3D', '#6E8354', '#E3B45C'],
            ['#2C4031', '#7F935C', '#C98248'],
            ['#173B35', '#5E7B6A', '#DBAA53'],
        ][hexdec($seed[0]) % 4];
        $title = htmlspecialchars($recipe->title, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $cuisine = htmlspecialchars($recipe->cuisine->name, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $x = 180 + (hexdec(substr($seed, 2, 2)) % 720);
        $y = 230 + (hexdec(substr($seed, 4, 2)) % 320);
        $radius = 120 + (hexdec(substr($seed, 6, 2)) % 180);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="900" viewBox="0 0 1200 900" role="img" aria-labelledby="title desc">
<title id="title">{$title}</title><desc id="desc">{$cuisine} için House Chef Cyprus markalı temsili görsel.</desc>
<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="{$palette[0]}"/><stop offset="1" stop-color="{$palette[1]}"/></linearGradient></defs>
<rect width="1200" height="900" fill="url(#g)"/><circle cx="{$x}" cy="{$y}" r="{$radius}" fill="{$palette[2]}" opacity=".24"/><circle cx="960" cy="170" r="230" fill="#fff" opacity=".08"/>
<path d="M300 510c95-175 505-175 600 0-40 180-190 275-300 275S340 690 300 510Z" fill="#fff" opacity=".92"/><path d="M430 510c58-82 282-82 340 0" fill="none" stroke="{$palette[0]}" stroke-width="24" stroke-linecap="round"/>
<text x="92" y="105" fill="#fff" font-family="Georgia,serif" font-size="42" font-weight="700">House Chef Cyprus</text><text x="92" y="164" fill="#fff" font-family="Arial,sans-serif" font-size="25" opacity=".88">{$cuisine}</text>
<text x="92" y="830" fill="#fff" font-family="Georgia,serif" font-size="52" font-weight="700">{$title}</text>
</svg>
SVG;
    }
}
