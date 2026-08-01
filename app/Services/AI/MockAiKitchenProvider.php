<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Contracts\AI\AiKitchenProviderInterface;

/** Local deterministic provider for safe, database-grounded kitchen guidance. */
final class MockAiKitchenProvider implements AiKitchenProviderInterface
{
    public function name(): string
    {
        return 'mock';
    }

    public function health(): array
    {
        return ['provider' => $this->name(), 'status' => 'healthy', 'configured' => true, 'model' => (string) config('ai_kitchen.providers.mock.model'), 'billable' => false];
    }

    public function respond(array $request): array
    {
        $purpose = (string) ($request['purpose'] ?? 'assistant');
        $question = trim((string) ($request['question'] ?? ''));
        $text = match ($purpose) {
            'cooking_coach' => 'Tarifteki adımları sırayla uygulayın; ısı ve kıvamı küçük ayarlamalarla kontrol edin.',
            'nutrition' => 'Besin değerleri yalnız kaynak ve güven düzeyiyle birlikte gösterilir. Bu yanıt tıbbi veya diyetisyen tavsiyesi değildir.',
            'substitute' => 'Alternatif malzeme, tat ve doku sonucunu değiştirebilir. Alerjen ve diyet uygunluğunu ayrıca kontrol edin.',
            'leftover' => 'Artan malzemeleri güvenli saklama süresini dikkate alarak değerlendirin; şüpheli gıdayı kullanmayın.',
            default => $question === '' ? 'Size yardımcı olmak için tarif, malzeme veya planlama hedefinizi yazın.' : 'Tarif kataloğu ve dolap verinize göre güvenli bir başlangıç önerisi hazırladım.',
        };

        return ['status' => 'completed', 'text' => $text, 'input_tokens' => 0, 'output_tokens' => 0, 'cost_cents' => 0, 'safe_error_code' => null];
    }
}
