<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\MemberRegistered;
use App\Listeners\SendNewMemberAdminNotification;
use App\Mail\NewMemberRegisteredMail;
use App\Models\MemberRegistrationNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use RuntimeException;
use Tests\TestCase;

final class NewMemberRegistrationNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_is_after_commit_queued_and_sends_exactly_one_internal_mail(): void
    {
        Mail::fake();
        config()->set('mail.registration_admin_recipient', 'info@housechefcyprus.com');
        $member = User::factory()->create(['first_name' => 'Ada', 'last_name' => 'Lefkoşa']);
        $listener = app(SendNewMemberAdminNotification::class);

        $this->assertInstanceOf(ShouldQueueAfterCommit::class, $listener);
        $listener->handle(new MemberRegistered($member));
        $listener->handle(new MemberRegistered($member));

        Mail::assertSent(NewMemberRegisteredMail::class, 1);
        Mail::assertSent(NewMemberRegisteredMail::class, fn (NewMemberRegisteredMail $mail): bool => $mail->hasTo('info@housechefcyprus.com'));
        $this->assertDatabaseHas('member_registration_notifications', [
            'user_id' => $member->id,
            'status' => 'sent',
            'attempt_count' => 1,
        ]);
    }

    public function test_successful_registration_dispatches_the_internal_listener_to_the_queue(): void
    {
        Queue::fake();
        RateLimiter::clear('registration|127.0.0.1');

        Volt::test('pages.auth.register')
            ->set('first_name', 'Ela')
            ->set('last_name', 'Kaya')
            ->set('email', 'ela@example.com')
            ->set('password', 'StrongPass123!')
            ->set('password_confirmation', 'StrongPass123!')
            ->set('terms_and_privacy_acceptance', true)
            ->call('register');

        $this->assertDatabaseHas('users', ['email' => 'ela@example.com']);
        Queue::assertPushed(CallQueuedListener::class, fn (CallQueuedListener $job): bool => $job->class === SendNewMemberAdminNotification::class);
    }

    public function test_internal_mail_uses_optional_fallbacks_and_excludes_sensitive_values(): void
    {
        $member = User::factory()->create([
            'first_name' => 'Deniz',
            'last_name' => 'Kaya',
            'phone' => null,
            'country' => null,
            'city_or_region' => null,
            'registration_source' => null,
            'password' => 'NeverRenderThisSecret123!',
            'remember_token' => 'never-render-token',
        ]);

        $html = (new NewMemberRegisteredMail($member))->render();

        $this->assertStringContainsString('Belirtilmedi', $html);
        $this->assertStringContainsString((string) $member->public_id, $html);
        $this->assertStringNotContainsString('NeverRenderThisSecret123!', $html);
        $this->assertStringNotContainsString('never-render-token', $html);
        $this->assertStringNotContainsString('session', mb_strtolower(strip_tags($html)));
    }

    public function test_registration_persists_normalized_profile_and_does_not_imply_marketing_consent(): void
    {
        RateLimiter::clear('registration|127.0.0.1');

        Volt::test('pages.auth.register')
            ->set('first_name', '  Ayşe ')
            ->set('last_name', ' Yılmaz  ')
            ->set('email', ' AYSE@EXAMPLE.COM ')
            ->set('phone', '+90 555 111 22 33')
            ->set('country', 'Kıbrıs')
            ->set('city_or_region', 'Lefkoşa')
            ->set('password', 'StrongPass123!')
            ->set('password_confirmation', 'StrongPass123!')
            ->set('terms_and_privacy_acceptance', true)
            ->call('register')
            ->assertRedirect(route('dashboard', absolute: false));

        $member = User::query()->where('email', 'ayse@example.com')->firstOrFail();
        $this->assertSame('Ayşe Yılmaz', $member->name);
        $this->assertSame('Kıbrıs', $member->country);
        $this->assertNotNull($member->terms_accepted_at);
        $this->assertNull($member->marketing_consent_at);
    }

    public function test_terms_are_required_and_optional_fields_are_not(): void
    {
        RateLimiter::clear('registration|127.0.0.1');

        Volt::test('pages.auth.register')
            ->set('first_name', 'Mert')
            ->set('last_name', 'Kaya')
            ->set('email', 'mert@example.com')
            ->set('password', 'StrongPass123!')
            ->set('password_confirmation', 'StrongPass123!')
            ->call('register')
            ->assertHasErrors('terms_and_privacy_acceptance');

        $this->assertDatabaseMissing('users', ['email' => 'mert@example.com']);
    }

    public function test_mail_failure_cannot_cancel_committed_registration(): void
    {
        config()->set('queue.default', 'sync');
        config()->set('mail.registration_admin_recipient', 'info@housechefcyprus.com');
        Mail::shouldReceive('to')->andThrow(new RuntimeException('smtp-secret must not escape'));
        RateLimiter::clear('registration|127.0.0.1');

        Volt::test('pages.auth.register')
            ->set('first_name', 'Selin')
            ->set('last_name', 'Ada')
            ->set('email', 'selin@example.com')
            ->set('password', 'StrongPass123!')
            ->set('password_confirmation', 'StrongPass123!')
            ->set('terms_and_privacy_acceptance', true)
            ->call('register')
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', ['email' => 'selin@example.com']);
        $this->assertDatabaseHas('member_registration_notifications', [
            'status' => 'failed',
            'failure_code' => 'REGISTRATION_MAIL_DELIVERY_FAILED',
        ]);
    }

    public function test_verification_and_profile_updates_do_not_resend_registration_mail(): void
    {
        Mail::fake();
        $member = User::factory()->unverified()->create();
        $listener = app(SendNewMemberAdminNotification::class);
        $listener->handle(new MemberRegistered($member));

        $member->markEmailAsVerified();
        $member->update(['name' => 'Güncellenen Üye']);

        Mail::assertSent(NewMemberRegisteredMail::class, 1);
        $this->assertSame(1, MemberRegistrationNotification::query()->where('user_id', $member->id)->count());
    }

    public function test_admin_can_filter_registration_profile_and_member_cannot_open_detail(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'role' => 'member',
            'phone' => '+357 99111222',
            'country' => 'Kıbrıs',
            'city_or_region' => 'Girne',
            'registration_source' => 'organic',
        ]);

        $this->actingAs($admin)->get(route('admin.users.index', ['country' => 'Kıbrıs', 'source' => 'organic']))
            ->assertOk()->assertSee('+357 99111222')->assertSee('Girne');
        $this->actingAs($admin)->get(route('admin.users.show', ['user' => $member->public_id]))
            ->assertOk()->assertSee('Kıbrıs')->assertSee('organic');
        auth()->logout();
        $this->get(route('admin.users.show', ['user' => $member->public_id]))
            ->assertRedirect(route('login'));
        $this->actingAs($member)->get(route('admin.users.show', ['user' => $member->public_id]))
            ->assertForbidden();
    }
}
