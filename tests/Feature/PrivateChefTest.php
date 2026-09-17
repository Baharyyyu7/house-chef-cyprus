<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\ContactRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class PrivateChefTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_chef_landing_page_renders_successfully(): void
    {
        $response = $this->get(route('private-chef.index'));

        $response->assertOk()
            ->assertSee('Private Chef Cyprus')
            ->assertSee('Villanızda Gurme Şölen')
            ->assertSee('FoodService')
            ->assertSee('FAQPage')
            ->assertSee('name="subject" value="private_chef"', false);
    }

    public function test_ozel_sef_redirects_to_private_chef(): void
    {
        $response = $this->get('/ozel-sef');

        $response->assertRedirect('/private-chef')
            ->assertStatus(301);
    }

    public function test_private_chef_inquiry_can_be_submitted_via_contact_pipeline(): void
    {
        Mail::fake();
        config(['contact-requests.captcha.enabled' => false]);

        $payload = [
            'first_name' => 'Can',
            'last_name' => 'Akdeniz',
            'email' => 'can.akdeniz@example.com',
            'phone' => '+90 533 888 7766',
            'subject' => 'private_chef',
            'message' => 'Girne Bellapais villamız için 8 kişilik kleftiko ve meze akşam yemeği menüsü ve şef teklifi rica ediyoruz.',
            'privacy_consent' => '1',
            'source_page' => route('private-chef.index'),
        ];

        $response = $this->post(route('legal.center.contact.store'), $payload);

        $response->assertRedirect(route('legal.center').'#iletisim-ve-geri-bildirim');
        $this->assertDatabaseHas('contact_requests', [
            'first_name' => 'Can',
            'last_name' => 'Akdeniz',
            'email' => 'can.akdeniz@example.com',
            'subject' => 'private_chef',
            'status' => 'new',
        ]);
        Mail::assertQueued(ContactRequestNotification::class, 1);
    }

    public function test_sitemap_includes_private_chef_route(): void
    {
        $response = $this->get(route('sitemap.pages'));

        $response->assertOk()
            ->assertSee(url('private-chef'));
    }
}
