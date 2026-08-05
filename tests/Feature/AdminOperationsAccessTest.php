<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class AdminOperationsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_named_admin_route_has_an_authenticated_server_side_operations_gate(): void
    {
        $allowedGates = [
            'can:access-admin',
            'can:access-media-operations',
            'can:access-content-factory',
            'can:access-editorial-operations',
            'can:access-production-content-operations',
        ];

        $adminRoutes = collect(Route::getRoutes())
            ->filter(fn ($route): bool => str_starts_with((string) $route->getName(), 'admin.'));

        $this->assertNotEmpty($adminRoutes);

        $adminRoutes->each(function ($route) use ($allowedGates): void {
            $middleware = $route->gatherMiddleware();

            $this->assertContains('auth', $middleware, "{$route->getName()} auth middleware eksik.");
            $this->assertTrue(
                count(array_intersect($allowedGates, $middleware)) > 0,
                "{$route->getName()} için server-side yönetim gate'i eksik.",
            );
        });
    }

    public function test_a_verified_member_is_forbidden_from_representative_admin_operation_routes(): void
    {
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        foreach ([
            route('admin.dashboard'),
            route('admin.backups.index'),
            route('admin.contact-requests.index'),
            route('admin.media-operations.index'),
        ] as $url) {
            $this->actingAs($member)->get($url)->assertForbidden();
        }
    }
}
