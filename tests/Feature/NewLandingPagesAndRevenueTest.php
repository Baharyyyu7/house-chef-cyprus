<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NewLandingPagesAndRevenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_catering_cyprus_landing_page_renders_successfully(): void
    {
        $response = $this->get(route('catering.index'));

        $response->assertOk()
            ->assertSee('Catering Cyprus')
            ->assertSee('Kuzey Kıbrıs Profesyonel Catering')
            ->assertSee('FoodService')
            ->assertSee('FAQPage');

    }

    public function test_cooking_classes_landing_page_renders_successfully(): void
    {
        $response = $this->get(route('cooking-classes.index'));

        $response->assertOk()
            ->assertSee('Cooking Classes Cyprus')
            ->assertSee('Hellim Peyniri Yapımı')
            ->assertSee('EducationalOrganization')
            ->assertSee('Course');
    }

    public function test_meal_plans_landing_page_renders_successfully(): void
    {
        $response = $this->get(route('meal-plans.index'));

        $response->assertOk()
            ->assertSee('Meal Planning Cyprus')
            ->assertSee('Klasik Akdeniz Sağlık Planı')
            ->assertSee('Kıbrıs Yüksek Protein Planı')
            ->assertSee('WebPage')
            ->assertSee('FAQPage');
    }

    public function test_landing_page_redirects_are_permanent(): void
    {
        $this->get('/catering')->assertRedirect('/catering-cyprus')->assertStatus(301);
        $this->get('/yemek-kursu')->assertRedirect('/cooking-classes')->assertStatus(301);
        $this->get('/diyet-plani')->assertRedirect('/meal-plans')->assertStatus(301);
        $this->get('/yemek-plani')->assertRedirect('/meal-plans')->assertStatus(301);
    }
}
