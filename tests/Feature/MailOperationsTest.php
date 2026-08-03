<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\ContactRequestNotification;
use App\Mail\ContactRequestReplyMail;
use App\Mail\NewMemberRegisteredMail;
use App\Models\ContactRequest;
use App\Models\MemberRegistrationNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MailOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_safe_mail_health_but_member_cannot(): void
    {
        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp.host', 'smtp.example.test');
        config()->set('mail.mailers.smtp.username', 'mailer');
        config()->set('mail.mailers.smtp.password', 'must-not-leak');
        config()->set('mail.from.address', 'info@housechefcyprus.com');
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($admin)->get(route('admin.mail-operations.index'))
            ->assertOk()->assertSee('E-posta operasyon merkezi')->assertDontSee('must-not-leak');
        $this->actingAs($member)->get(route('admin.mail-operations.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.mail-operations.preview', 'verification'))->assertForbidden();
    }

    public function test_admin_can_preview_templates_with_synthetic_content_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.mail-operations.preview', 'verification'))
            ->assertOk()
            ->assertSee('E-posta adresinizi doğrulayın')
            ->assertSee('Sentetik önizleme')
            ->assertDontSee('password')
            ->assertDontSee('token=');
        $this->actingAs($admin)->get(route('admin.mail-operations.preview', 'unknown'))->assertNotFound();
    }

    public function test_mail_operations_reports_registration_delivery_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        MemberRegistrationNotification::query()->create([
            'user_id' => $member->id,
            'idempotency_key' => hash('sha256', 'mail-ops-test'),
            'recipient' => 'info@housechefcyprus.com',
            'status' => 'failed',
            'attempt_count' => 1,
            'failed_at' => now(),
            'failure_code' => 'MAIL_SEND_FAILED',
        ]);

        $this->actingAs($admin)->get(route('admin.mail-operations.index'))->assertOk()->assertSee('Hatalı')->assertSee('1');
    }

    public function test_transactional_mailables_have_plain_text_fallbacks(): void
    {
        $member = User::factory()->create();
        $contact = ContactRequest::query()->create([
            'first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'ada@example.test',
            'subject' => 'other', 'message' => 'Merhaba', 'status' => 'new', 'privacy_consent_at' => now(), 'submitted_at' => now(),
        ]);

        $this->assertSame('mail.internal.new-member-registered-text', (new NewMemberRegisteredMail($member))->content()->text);
        $this->assertSame('mail.contact-request-notification-text', (new ContactRequestNotification($contact))->content()->text);
        $this->assertInstanceOf(ShouldQueue::class, new ContactRequestNotification($contact));
        $this->assertSame('mail.contact-request-reply-text', (new ContactRequestReplyMail($contact, 'Yanıt'))->content()->text);
    }

    public function test_dns_readiness_uses_cached_mail_configuration(): void
    {
        config()->set('mail.dns', [
            'spf_verified' => true,
            'dkim_verified' => true,
            'dmarc_verified' => true,
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.mail-operations.index'))
            ->assertOk()
            ->assertSee('SPF')
            ->assertSee('DKIM')
            ->assertSee('DMARC');
    }
}
