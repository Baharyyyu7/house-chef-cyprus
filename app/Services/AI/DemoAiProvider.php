<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Contracts\AI\AiProviderInterface;
use App\Models\Recipe;

final class DemoAiProvider implements AiProviderInterface
{
    public function answer(string $question, ?Recipe $recipe = null, array $context = []): string
    {
        $name = $recipe?->title ?? 'seçtiğiniz tarif';

        return "Demo AI Şef: {$name} için sorunu anladım. Malzeme değişikliklerinde kıvamı küçük adımlarla ayarlayın ve alerjenleri mutlaka kontrol edin. Bu yanıt tıbbi veya diyetisyen tavsiyesi değildir.";
    }
}
