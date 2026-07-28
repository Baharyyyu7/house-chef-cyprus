<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Recipe;
use Illuminate\Support\Str;

final class RecipeImagePromptBuilder
{
    public function __construct(private readonly RecipeImageFidelityGate $fidelity) {}

    /** @return array{prompt:string,negative_prompt:string,alt_text:string,prompt_version:string,ready:bool,blocking_reasons:list<string>} */
    public function build(Recipe $recipe, string $variation = 'bright_ivory_editorial'): array
    {
        $assessment = $this->fidelity->assess($recipe);
        if (! $assessment['ready']) {
            return [
                'prompt' => 'PROMPT BLOCKED: recipe ingredient fidelity must be corrected before any image generation request.',
                'negative_prompt' => 'Do not generate an image while the recipe fidelity check is blocked.',
                'alt_text' => $recipe->title.' için görsel üretimi veri doğrulaması bekliyor',
                'prompt_version' => (string) config('ai_images.prompt_version'),
                'ready' => false,
                'blocking_reasons' => ['Tarif adıyla uyumlu zorunlu malzeme kaydı eksik: '.implode(', ', $assessment['missing']).'.'],
            ];
        }
        $ingredients = $recipe->ingredients->map(fn ($ingredient): string => (string) ($ingredient->canonical_name ?: $ingredient->name))->filter()->unique()->take(8)->implode(', ');
        $cuisine = $recipe->cuisine->name ?? 'Mediterranean';
        $category = $recipe->category->name ?? 'recipe';
        $camera = match ($variation) {
            'modern_overhead' => '90-degree overhead composition',
            'close_up_texture' => 'close-up texture-focused composition',
            'traditional_cyprus' => '45-degree traditional Cyprus serving composition',
            'dark_premium_studio' => '45-degree premium plated composition',
            default => '45-degree editorial plated composition',
        };
        $surface = match ($variation) {
            'dark_premium_studio' => 'dark charcoal stone surface',
            'rustic_mediterranean', 'traditional_cyprus' => 'natural warm wood and stone surface',
            default => 'warm ivory stone surface',
        };

        $prompt = trim(implode("\n", [
            'Use case: photorealistic-natural',
            'Asset type: House Chef Cyprus recipe hero image',
            "Primary request: {$recipe->title}, a {$cuisine} {$category} recipe.",
            "Recipe ingredients to represent faithfully: {$ingredients}.",
            'Style/medium: premium editorial food photography, realistic home portion, warm Mediterranean light.',
            "Composition/framing: {$camera}; 4:3 landscape, one plated serving, no people or hands.",
            "Scene/backdrop: {$surface}; restrained orange accent only as a small napkin or secondary accessory.",
            'Constraints: preserve the food\'s natural colour and texture; include only ingredients supported by the recipe data; no text, logo, watermark, packaging, or extra dish.',
        ]));

        return [
            'prompt' => $prompt,
            'negative_prompt' => 'text, logo, watermark, people, hands, faces, packaging, plastic food, oversaturation, duplicate plates, invented ingredients, distorted cutlery, raw food when cooked food is expected',
            'alt_text' => Str::limit($recipe->title.' için yapay zekâ üretimli tarif görseli', 250, ''),
            'prompt_version' => (string) config('ai_images.prompt_version'),
            'ready' => true,
            'blocking_reasons' => [],
        ];
    }
}
