<?php

declare(strict_types=1);

namespace App\Services\AI;

final class AiSafetyService
{
    /** @return list<array{code:string,message:string}> */
    public function events(string $question, string $purpose): array
    {
        $value = mb_strtolower($question);
        $events = [];
        if (preg_match('/(alerji|anafilaksi|zehirlenme|hamile|ilaç|tedavi|hastalık|bebek)/u', $value) === 1) {
            $events[] = ['code' => 'HEALTH_OR_SAFETY_CONTEXT', 'message' => 'Sağlık, alerjen ve gıda güvenliği kararları için uzman veya resmi kaynağa danışın.'];
        }
        if (in_array($purpose, ['nutrition', 'meal_plan'], true)) {
            $events[] = ['code' => 'NUTRITION_NOT_MEDICAL_ADVICE', 'message' => 'Beslenme çıktısı bilgilendirme amaçlıdır; doğrulanmış klinik tavsiye değildir.'];
        }

        return $events;
    }
}
