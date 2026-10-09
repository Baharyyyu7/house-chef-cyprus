<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeListMalformedQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_malformed_filters_do_not_crash_the_recipe_list(): void
    {
        // 9 Ekim canlı ölçümü: q[]=x, cuisine[]=x, max_minutes=abc, min_protein=zz
        // ve category[]=x sorgularının hepsi 500 dönüyordu.
        foreach (['q[]=x', 'cuisine[]=x', 'category[]=x', 'max_minutes=abc', 'min_protein=zz', 'max_calories=-5', 'page=0', 'max_minutes=99999'] as $sorgu) {
            $this->get('/tarifler?'.$sorgu)->assertOk();
        }
    }
}
