<?php

namespace Tests\Feature;

use App\Models\GymSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    private function createOwner(): User
    {
        return User::factory()->create([
            'name'     => 'Test Owner',
            'email'    => 'owner@warmup.test',
            'password' => bcrypt('password123'),
        ]);
    }

    private function createSettings(string $language = 'en'): GymSetting
    {
        return GymSetting::create([
            'gym_name'        => 'Test Gym',
            'owner_name'      => 'Test Owner',
            'currency'        => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone'        => 'Asia/Karachi',
            'language'        => $language,
            'theme'           => 'light',
            'date_format'     => 'd/m/Y',
            'time_format'     => '12h',
        ]);
    }

    public function test_english_locale_is_applied_when_language_is_en(): void
    {
        $user = $this->createOwner();
        $this->createSettings('en');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // HTML should have lang="en"
        $response->assertSee('lang="en"', false);
    }

    public function test_urdu_locale_is_applied_when_language_is_ur(): void
    {
        $user = $this->createOwner();
        $this->createSettings('ur');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // HTML should have lang="ur" and dir="rtl"
        $response->assertSee('lang="ur"', false);
        $response->assertSee('dir="rtl"', false);
    }

    public function test_sindhi_locale_is_applied_when_language_is_sd(): void
    {
        $user = $this->createOwner();
        $this->createSettings('sd');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // HTML should have lang="sd" and dir="rtl"
        $response->assertSee('lang="sd"', false);
        $response->assertSee('dir="rtl"', false);
    }

    public function test_ltr_direction_for_english(): void
    {
        $user = $this->createOwner();
        $this->createSettings('en');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertSee('dir="ltr"', false);
    }

    public function test_locale_middleware_does_not_affect_guests(): void
    {
        // Guests are redirected before locale can be applied
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_locale_falls_back_to_default_when_no_settings_exist(): void
    {
        $user = $this->createOwner();
        // No GymSetting created — middleware should silently skip

        $response = $this->actingAs($user)->get(route('settings.index'));
        // Should not throw; settings controller auto-creates default
        $response->assertStatus(200);
    }

    public function test_language_setting_can_be_changed_to_urdu(): void
    {
        $user = $this->createOwner();
        GymSetting::create([
            'gym_name'        => 'Test Gym',
            'owner_name'      => 'Test Owner',
            'currency'        => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone'        => 'Asia/Karachi',
            'language'        => 'en',
            'theme'           => 'light',
            'date_format'     => 'd/m/Y',
            'time_format'     => '12h',
        ]);

        // Update language to Urdu
        $response = $this->actingAs($user)->patch(route('settings.update'), [
            'gym_name'        => 'Test Gym',
            'owner_name'      => 'Test Owner',
            'currency'        => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone'        => 'Asia/Karachi',
            'language'        => 'ur',
            'theme'           => 'light',
            'date_format'     => 'd/m/Y',
            'time_format'     => '12h',
        ]);

        $response->assertRedirect(route('settings.index'));
        $this->assertDatabaseHas('gym_settings', ['language' => 'ur']);
    }
}
