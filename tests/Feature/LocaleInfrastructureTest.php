<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_public_document_uses_the_turkish_locale(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="tr" dir="ltr"', false);
    }

    public function test_a_locale_that_has_not_been_editorially_enabled_cannot_be_selected(): void
    {
        $this->from('/')
            ->post(route('locale.update'), ['locale' => 'de', 'return_to' => '/'])
            ->assertRedirect('/')
            ->assertSessionHasErrors('locale');

        $this->assertSame('tr', app()->getLocale());
    }

    public function test_an_enabled_locale_is_saved_for_guests_without_accept_language_guessing(): void
    {
        config()->set('locales.supported.en.enabled', true);

        $this->post(route('locale.update'), ['locale' => 'en', 'return_to' => '/'])
            ->assertRedirect('/')
            ->assertSessionHas('hc_locale', 'en');

        $this->withSession(['hc_locale' => 'en'])
            ->get('/')
            ->assertOk()
            ->assertSee('<html lang="en" dir="ltr"', false)
            ->assertSee('content="en_US"', false);
    }

    public function test_persian_uses_rtl_only_after_that_locale_is_enabled(): void
    {
        config()->set('locales.supported.fa.enabled', true);

        $this->withSession(['hc_locale' => 'fa'])
            ->get('/')
            ->assertOk()
            ->assertSee('<html lang="fa" dir="rtl"', false)
            ->assertSee('content="fa_IR"', false);
    }

    public function test_authenticated_user_preference_is_updated_when_selecting_an_enabled_locale(): void
    {
        config()->set('locales.supported.en.enabled', true);
        $user = User::factory()->create(['preferred_language' => 'tr']);

        $this->actingAs($user)
            ->post(route('locale.update'), ['locale' => 'en', 'return_to' => '/'])
            ->assertRedirect('/');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'preferred_language' => 'en']);
    }

    public function test_external_return_paths_are_rejected(): void
    {
        $this->post(route('locale.update'), ['locale' => 'tr', 'return_to' => 'https://example.test/'])
            ->assertRedirect('/');
    }
}
