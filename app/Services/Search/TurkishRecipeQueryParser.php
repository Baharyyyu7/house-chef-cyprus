<?php

declare(strict_types=1);

namespace App\Services\Search;

/**
 * A bounded, explainable local parser. Ambiguous tokens remain unresolved;
 * they are never converted into a dietary, allergy, or medical assertion.
 */
final class TurkishRecipeQueryParser
{
    /** @var array<string, string> */
    private const EXCLUSIONS = [
        'yumurtasiz' => 'yumurta',
        'sutsuz' => 'sut',
        'etsiz' => 'et',
        'tavuksuz' => 'tavuk',
        'sogansiz' => 'sogan',
        'sarımsaksız' => 'sarımsak',
        'sarimsaksiz' => 'sarimsak',
    ];

    /** @var array<string, string> */
    private const ALIASES = [
        'yogurt' => 'yogurt',
        'kiyma' => 'kiyma',
        'patlican' => 'patlican',
        'badimcan' => 'patlican',
        'salca' => 'salca',
        'pul biber' => 'pul biber',
        'kirmizi biber' => 'pul biber',
    ];

    /** @var list<string> */
    private const INGREDIENT_TERMS = [
        'tavuk', 'patates', 'nohut', 'yogurt', 'kiyma', 'patlican', 'kabak',
        'domates', 'biber', 'yumurta', 'makarna', 'pirinc', 'bulgur',
        'mercimek', 'fasulye', 'mantar', 'sogan', 'sarimsak', 'sut', 'et',
    ];

    public function __construct(private readonly TurkishSearchNormalizer $normalizer) {}

    /** @return array{original_query:string,normalized_query:string,included_ingredients:list<string>,excluded_ingredients:list<string>,max_minutes:?int,method:?string,meal_type:?string,cuisine:?string,difficulty:?string,matched_tokens:list<string>,ignored_tokens:list<string>,confidence:int} */
    public function parse(string $query): array
    {
        $normalized = $this->normalizer->normalize($query);
        $tokens = $this->normalizer->tokens($query);
        $matched = [];
        $excluded = [];
        foreach (self::EXCLUSIONS as $token => $ingredient) {
            if (str_contains($normalized, $token)) {
                $excluded[] = $ingredient;
                $matched[] = $token;
            }
        }
        $method = null;
        foreach (['firin' => 'oven', 'tava' => 'pan', 'izgara' => 'grill', 'hasla' => 'boiling'] as $token => $value) {
            if (str_contains($normalized, $token)) {
                $method = $value;
                $matched[] = $token;
                break;
            }
        }
        $mealType = str_contains($normalized, 'kahvalti') ? 'kahvaltı' : null;
        if ($mealType !== null) {
            $matched[] = 'kahvalti';
        }
        $cuisine = str_contains($normalized, 'kibris') ? 'kıbrıs' : null;
        if ($cuisine !== null) {
            $matched[] = 'kibris';
        }
        $maxMinutes = preg_match('/\b(\d{1,3})\s*dakika(?:da|dan|ya(?:\s+kadar)?)?\b/u', $normalized, $matches) === 1 ? max(1, min(360, (int) $matches[1])) : null;
        if ($maxMinutes !== null) {
            $matched[] = $matches[1].' dakika';
        }

        $structural = ['evde', 'icin', 'ile', 've', 'var', 'yemek', 'tarif', 'yapilacak', 'pratik', 'kolay', 'dakika', 'dakikada', 'kadar', 'fırında', 'firinda', 'etsiz', 'yumurtasiz', 'sutsuz', 'kahvalti', 'kibris', 'mutfagindan'];
        $ingredients = [];
        foreach ($tokens as $token) {
            if (in_array($token, $structural, true) || ctype_digit($token) || in_array($token, array_keys(self::EXCLUSIONS), true)) {
                continue;
            }
            $canonical = self::ALIASES[$token] ?? $token;
            if (! in_array($canonical, self::INGREDIENT_TERMS, true)) {
                continue;
            }
            $ingredients[] = $canonical;
        }
        $ingredients = array_values(array_unique(array_diff($ingredients, $excluded)));
        $ignored = array_values(array_diff($tokens, $matched, $ingredients));

        return [
            'original_query' => $query,
            'normalized_query' => $normalized,
            'included_ingredients' => $ingredients,
            'excluded_ingredients' => array_values(array_unique($excluded)),
            'max_minutes' => $maxMinutes,
            'method' => $method,
            'meal_type' => $mealType,
            'cuisine' => $cuisine,
            'difficulty' => str_contains($normalized, 'kolay') ? 'easy' : null,
            'matched_tokens' => array_values(array_unique($matched)),
            'ignored_tokens' => $ignored,
            'confidence' => min(100, count($matched) * 20 + count($ingredients) * 15),
        ];
    }
}
