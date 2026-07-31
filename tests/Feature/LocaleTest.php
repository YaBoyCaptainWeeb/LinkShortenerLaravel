<?php

namespace Tests\Feature;

use App\Actions\Fortify\CreateNewUser;
use App\Enums\AppLocale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_is_used_by_default(): void
    {
        $this->get('/')
            ->assertOk();

        $this->assertSame(AppLocale::English->value, App::currentLocale());
    }

    public function test_accept_language_is_used_when_no_preference_is_saved(): void
    {
        $this->withHeader('Accept-Language', 'ru-RU,ru;q=0.9,en;q=0.8')
            ->get('/')
            ->assertOk();

        $this->assertSame(AppLocale::Russian->value, App::currentLocale());
    }

    public function test_session_locale_has_priority_over_accept_language(): void
    {
        $this->withSession(['locale' => AppLocale::English->value])
            ->withHeader('Accept-Language', 'ru-RU,ru;q=0.9')
            ->get('/')
            ->assertOk();

        $this->assertSame(AppLocale::English->value, App::currentLocale());
    }

    public function test_authenticated_user_locale_has_priority_over_session_and_accept_language(): void
    {
        $user = User::factory()->create([
            'locale' => AppLocale::Russian,
        ]);

        $this->actingAs($user)
            ->withSession(['locale' => AppLocale::English->value])
            ->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get('/')
            ->assertRedirect('/panel/links');

        $this->assertSame(AppLocale::Russian->value, App::currentLocale());
    }

    public function test_guest_can_save_locale_in_session(): void
    {
        $this->from('/')
            ->post('/locale/ru')
            ->assertRedirect('/')
            ->assertSessionHas('locale', AppLocale::Russian->value);
    }

    public function test_authenticated_user_can_save_locale_in_session_and_database(): void
    {
        $user = User::factory()->create([
            'locale' => AppLocale::English,
        ]);

        $this->actingAs($user)
            ->from('/panel/links')
            ->post('/locale/ru')
            ->assertRedirect('/panel/links')
            ->assertSessionHas('locale', AppLocale::Russian->value);

        $this->assertSame(AppLocale::Russian, $user->refresh()->locale);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => AppLocale::Russian->value,
        ]);
    }

    public function test_selected_locale_is_preserved_after_fortify_logout(): void
    {
        $user = User::factory()->create([
            'locale' => AppLocale::Russian,
        ]);

        $this->actingAs($user)
            ->withHeader('Accept-Language', 'ru-RU,ru;q=0.9')
            ->post('/locale/en')
            ->assertRedirect()
            ->assertSessionHas('locale', AppLocale::English->value);

        $this->post('/logout')
            ->assertRedirect('/')
            ->assertSessionHas('locale', AppLocale::English->value);

        $this->assertGuest();

        $this->get('/')
            ->assertOk();

        $this->assertSame(AppLocale::English->value, App::currentLocale());
    }

    public function test_selected_locale_is_preserved_after_filament_logout(): void
    {
        $user = User::factory()->create([
            'locale' => AppLocale::Russian,
        ]);

        $this->actingAs($user)
            ->withHeader('Accept-Language', 'ru-RU,ru;q=0.9')
            ->post('/locale/en')
            ->assertRedirect()
            ->assertSessionHas('locale', AppLocale::English->value);

        $this->post(route('filament.admin.auth.logout'))
            ->assertRedirect()
            ->assertSessionHas('locale', AppLocale::English->value);

        $this->assertGuest();

        $this->get('/panel')
            ->assertOk();

        $this->assertSame(AppLocale::English->value, App::currentLocale());
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $this->post('/locale/de')
            ->assertNotFound()
            ->assertSessionMissing('locale');
    }

    public function test_new_user_inherits_current_application_locale(): void
    {
        App::setLocale(AppLocale::Russian->value);

        $user = app(CreateNewUser::class)->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertSame(AppLocale::Russian, $user->locale);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => AppLocale::Russian->value,
        ]);
    }

}
