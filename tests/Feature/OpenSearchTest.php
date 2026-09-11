<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OpenSearchTest extends TestCase
{
    use RefreshDatabase;
    public function test_opensearch_xml_returns_valid_description(): void
    {
        $response = $this->get('/opensearch.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/opensearchdescription+xml; charset=UTF-8')
            ->assertSee('<OpenSearchDescription', false)
            ->assertSee('<ShortName>House Chef</ShortName>', false)
            ->assertSee('{searchTerms}', false);
    }

    public function test_webmanifest_route_serves_manifest(): void
    {
        $response = $this->get('/manifest.webmanifest');

        $response->assertOk()
            ->assertSee('House Chef Cyprus')
            ->assertSee('standalone');
    }
}
