<?php

declare(strict_types=1);

namespace App\Services\Search;

/**
 * Deterministic query normalization only. It never mutates public content and
 * deliberately avoids guessing a food-safety or dietary meaning.
 */
final class TurkishSearchNormalizer
{
    public function normalize(string $value): string
    {
        $value = strtr($value, ['I' => 'ı', 'İ' => 'i']);
        $value = mb_strtolower($value, 'UTF-8');
        $value = strtr($value, ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u']);
        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    /** @return list<string> */
    public function tokens(string $value): array
    {
        return array_values(array_filter(array_map(fn (string $token): string => $this->singularize($token), explode(' ', $this->normalize($value))), fn (string $token): bool => $token !== ''));
    }

    private function singularize(string $token): string
    {
        if (mb_strlen($token, 'UTF-8') >= 6 && (str_ends_with($token, 'lar') || str_ends_with($token, 'ler'))) {
            return mb_substr($token, 0, -3, 'UTF-8');
        }
        if (mb_strlen($token, 'UTF-8') >= 6 && preg_match('/(li|li|lu|lu)$/u', $token) === 1) {
            return mb_substr($token, 0, -2, 'UTF-8');
        }

        return $token;
    }
}
