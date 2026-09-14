<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CanonicalHostTest extends TestCase
{
    use RefreshDatabase;
    public function test_www_requests_are_redirected_to_non_www_canonical_domain_with_301(): void
    {
        $response = $this->get('https://www.housechefcyprus.com/tarifler');

        $response->assertStatus(301);
        $response->assertRedirect('https://housechefcyprus.com/tarifler');
    }

    public function test_non_www_requests_pass_through_normally(): void
    {
        $response = $this->get('https://housechefcyprus.com/');

        $response->assertStatus(200);
    }
}
