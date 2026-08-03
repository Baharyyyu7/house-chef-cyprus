<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Search\TurkishRecipeQueryParser;
use App\Services\Search\TurkishSearchNormalizer;
use Tests\TestCase;

final class TurkishRecipeQueryParserTest extends TestCase
{
    public function test_normalizer_handles_turkish_characters_and_safe_plural_suffixes(): void
    {
        $normalizer = app(TurkishSearchNormalizer::class);

        $this->assertSame('kibris corbasi', $normalizer->normalize('KIBRIS Çorbası'));
        $this->assertSame(['tavuk', 'patates'], $normalizer->tokens('Tavuklu patatesler'));
    }

    public function test_parser_extracts_explainable_time_method_exclusion_and_ingredient_intent(): void
    {
        $plan = app(TurkishRecipeQueryParser::class)->parse('30 dakikada fırında yumurtasız tavuklu yemek');

        $this->assertSame(30, $plan['max_minutes']);
        $this->assertSame('oven', $plan['method']);
        $this->assertSame(['yumurta'], $plan['excluded_ingredients']);
        $this->assertContains('tavuk', $plan['included_ingredients']);
        $this->assertGreaterThan(0, $plan['confidence']);
    }

    public function test_parser_does_not_turn_ingredient_absence_into_a_dietary_or_allergy_claim(): void
    {
        $plan = app(TurkishRecipeQueryParser::class)->parse('yumurtasız kahvaltı');

        $this->assertSame(['yumurta'], $plan['excluded_ingredients']);
        $this->assertSame('kahvaltı', $plan['meal_type']);
        $this->assertArrayNotHasKey('dietary_label', $plan);
        $this->assertArrayNotHasKey('allergy_safe', $plan);
    }
}
