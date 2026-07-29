<?php

declare(strict_types=1);

namespace App\Contracts\Media;

use App\Services\Media\ProviderImageResult;

interface RecipeImageProviderInterface
{
    public function name(): string;

    /** @return array{name:string,configured:bool,enabled:bool,image_capability:string,model:?string,safe_message:string} */
    public function health(): array;

    public function generate(string $prompt, string $negativePrompt, string $aspectRatio): ProviderImageResult;
}
