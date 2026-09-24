<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\ContactRequestNotification;
use App\Mail\ContactRequestReplyMail;
use App\Models\ContactRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class ContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_commercial_request_is_captured_without_creating_a_sale(): void
    {
        Mail::fake();
        config(['contact-requests.captcha.enabled' => false]);
        $this->post(route('legal.center.contact.store'), [
            ...$this->payload(), 'subject' => 'menu_content', 'source_page' => route('contact.index'),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contact_requests', ['subject' => 'menu_content', 'source_page' => route('contact.index')]);
        $this->assertDatabaseCount('digital_orders', 0);
        $this->assertDatabaseCount('user_subscriptions', 0);
    }

    public function test_contact_form_renders_enabled_captcha_and_selected_commercial_subject(): void
    {
        config(['contact-requests.captcha.enabled' => true, 'contact-requests.captcha.site_key' => 'test-site-key']);
        $this->get(route('contact.index', ['subject' => 'hospitality']))->assertOk()
            ->assertSee('data-response-field-name="captcha_token"', false)
            ->assertSee('value="hospitality" selected', false)
            ->assertSee('name="source_page" value="'.route('contact.index').'"', false);
    }

    public function test_guest_can_submit_a_privacy_consented_contact_request_without_registration(): void
    {
        Mail::fake();
        $response = $this->post(route('legal.center.contact.store'), $this->payload());

        $response->assertRedirect(route('legal.center').'#iletisim-ve-geri-bildirim');
        $this->assertDatabaseCount('contact_requests', 1);
        $this->assertDatabaseHas('contact_requests', ['email' => 'contact@housechefcyprus.com', 'status' => 'new', 'priority' => 'normal']);
        Mail::assertQueued(ContactRequestNotification::class, 1);
    }

    public function test_contact_request_validates_required_fields_privacy_and_honeypot(): void
    {
        $this->from(route('legal.center'))->post(route('legal.center.contact.store'), [])
            ->assertRedirect(route('legal.center'))
            ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'subject', 'message', 'privacy_consent']);

        $this->post(route('legal.center.contact.store'), [...$this->payload(), 'website' => 'https://spam.invalid'])
            ->assertStatus(422);
        $this->assertDatabaseCount('contact_requests', 0);
    }

    public function test_contact_request_rate_limit_is_enforced(): void
    {
        Mail::fake();
        foreach (range(1, 5) as $attempt) {
            $this->post(route('legal.center.contact.store'), [...$this->payload(), 'message' => 'Güvenli test mesajı '.$attempt]);
        }
        $this->post(route('legal.center.contact.store'), $this->payload())->assertStatus(429);
    }

    public function test_admin_can_manage_request_reply_and_add_internal_note_while_member_is_forbidden(): void
    {
        Mail::fake();
        $contact = ContactRequest::create([...$this->payload(), 'status' => 'new', 'priority' => 'normal', 'privacy_consent_at' => now(), 'submitted_at' => now(), 'last_activity_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->get(route('admin.contact-requests.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.contact-requests.show', $contact))->assertOk()->assertSee($contact->public_id);
        $this->actingAs($admin)->put(route('admin.contact-requests.update', $contact), ['status' => 'in_review', 'priority' => 'high', 'assigned_admin_id' => $admin->id])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.contact-requests.notes.store', $contact), ['body' => 'Yalnız yöneticiye görünen not.'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.contact-requests.reply', $contact), ['body' => 'Talebinizi değerlendirmeye aldık.'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contact_requests', ['id' => $contact->id, 'status' => 'answered', 'priority' => 'high']);
        $this->assertDatabaseCount('contact_request_notes', 1);
        $this->assertDatabaseCount('contact_request_replies', 1);
        Mail::assertSent(ContactRequestReplyMail::class, 1);
        $this->assertDatabaseHas('audit_logs', ['action' => 'contact_request.replied']);
    }

    /** @return array<string, string> */
    private function payload(): array
    {
        return ['first_name' => 'Ada', 'last_name' => 'Yılmaz', 'email' => 'contact@housechefcyprus.com', 'phone' => '+90 555 123 4567', 'subject' => 'general_feedback', 'message' => 'House Chef Cyprus için güvenli iletişim test mesajı.', 'privacy_consent' => '1'];
    }
}
