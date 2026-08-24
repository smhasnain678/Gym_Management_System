<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GymSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────────────
    // Existing access / CRUD tests (unchanged behaviour)
    // ──────────────────────────────────────────────────────────────────────────

    public function test_guests_cannot_access_settings()
    {
        $response = $this->get('/settings');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_settings()
    {
        $user = User::factory()->create();

        // Let the controller create the default setting
        $response = $this->actingAs($user)->get('/settings');
        
        $response->assertStatus(200);
        $response->assertViewIs('settings.index');
        $this->assertDatabaseHas('gym_settings', [
            'id'       => 1,
            'gym_name' => 'WarmUp Gym',
        ]);
    }

    public function test_authenticated_user_can_update_settings()
    {
        $user = User::factory()->create();
        
        GymSetting::create([
            'id'         => 1,
            'gym_name'   => 'Old Gym',
            'owner_name' => 'Old Owner',
        ]);

        $response = $this->actingAs($user)->patch('/settings', [
            'gym_name'        => 'New Gym Name',
            'owner_name'      => 'New Owner Name',
            'contact_email'   => 'contact@newgym.com',
            'contact_phone'   => '03001234567',
            'address'         => '123 New Street',
            'country'         => 'Pakistan',
            'city'            => 'Karachi',
            'currency'        => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone'        => 'Asia/Karachi',
            'language'        => 'en',
            'theme'           => 'light',
            'date_format'     => 'd/m/Y',
            'time_format'     => '12h',
        ]);

        $response->assertRedirect('/settings');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('gym_settings', [
            'gym_name'   => 'New Gym Name',
            'owner_name' => 'New Owner Name',
            'currency'   => 'PKR',
        ]);
    }

    public function test_authenticated_user_can_upload_logo()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        
        GymSetting::create([
            'id'         => 1,
            'gym_name'   => 'Test Gym',
            'owner_name' => 'Test Owner',
        ]);

        $file = UploadedFile::fake()->create('logo.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->patch('/settings', [
            'gym_name'        => 'Test Gym',
            'owner_name'      => 'Test Owner',
            'currency'        => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone'        => 'Asia/Karachi',
            'language'        => 'en',
            'theme'           => 'light',
            'date_format'     => 'd/m/Y',
            'time_format'     => '12h',
            'gym_logo'        => $file,
        ]);

        $response->assertRedirect('/settings');

        $setting = GymSetting::first();
        $this->assertNotNull($setting->gym_logo);
        Storage::disk('public')->assertExists($setting->gym_logo);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Branding Color — save / persist tests
    // ──────────────────────────────────────────────────────────────────────────

    /** 1. Primary color can be saved */
    public function test_primary_color_can_be_saved()
    {
        $user = User::factory()->create();
        GymSetting::create(['id' => 1, 'gym_name' => 'Test Gym', 'owner_name' => 'Owner']);

        $this->actingAs($user)->patch('/settings', $this->basePayload([
            'primary_color' => '#FF5733',
        ]));

        $this->assertDatabaseHas('gym_settings', ['primary_color' => '#FF5733']);
    }

    /** 2. Secondary color can be saved */
    public function test_secondary_color_can_be_saved()
    {
        $user = User::factory()->create();
        GymSetting::create(['id' => 1, 'gym_name' => 'Test Gym', 'owner_name' => 'Owner']);

        $this->actingAs($user)->patch('/settings', $this->basePayload([
            'secondary_color' => '#3357FF',
        ]));

        $this->assertDatabaseHas('gym_settings', ['secondary_color' => '#3357FF']);
    }

    /** 2.5 Brand split position can be saved and applied */
    public function test_brand_split_position_can_be_saved_and_applied()
    {
        $user = User::factory()->create();
        GymSetting::create(['id' => 1, 'gym_name' => 'ShapeUp', 'owner_name' => 'Owner']);

        $response = $this->actingAs($user)->patch('/settings', $this->basePayload([
            'gym_name'             => 'ShapeUp',
            'brand_split_position' => 5,
        ]));

        $response->assertRedirect('/settings');
        $this->assertDatabaseHas('gym_settings', ['brand_split_position' => 5]);

        $response2 = $this->actingAs($user)->get('/dashboard');
        $response2->assertStatus(200);
        $response2->assertSee('Shape');
        $response2->assertSee('Up');
    }

    /** 3. Saved colors appear on the settings page */
    public function test_saved_colors_appear_in_settings_page()
    {
        $user = User::factory()->create();
        GymSetting::create([
            'id'              => 1,
            'gym_name'        => 'Test Gym',
            'owner_name'      => 'Owner',
            'primary_color'   => '#AA1122',
            'secondary_color' => '#BB3344',
        ]);

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('#AA1122');
        $response->assertSee('#BB3344');
    }

    /** 4. Sidebar receives the saved branding colors via CSS custom properties */
    public function test_sidebar_receives_saved_branding_colors()
    {
        $user = User::factory()->create();
        GymSetting::create([
            'id'              => 1,
            'gym_name'        => 'Test Gym',
            'owner_name'      => 'Owner',
            'primary_color'   => '#CC1234',
            'secondary_color' => '#DD5678',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        // The CSS custom properties block injects both colours into the page
        $response->assertSee('--gym-primary:   #CC1234', false);
        $response->assertSee('--gym-secondary: #DD5678', false);
    }

    /** 5. Gym name is rendered using the two selected colors */
    public function test_gym_name_is_rendered_with_two_colors()
    {
        $user = User::factory()->create();
        GymSetting::create([
            'id'              => 1,
            'gym_name'        => 'Ultimate Fitness',
            'owner_name'      => 'Owner',
            'primary_color'   => '#EE0011',
            'secondary_color' => '#FF2233',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        // First word uses primary color
        $response->assertSee('color: var(--gym-primary)', false);
        // Remaining words use secondary color
        $response->assertSee('color: var(--gym-secondary)', false);
        // Both parts of the name are present
        $response->assertSee('Ultimate');
        $response->assertSee('Fitness');
    }

    /** 6. Default colors are used when no custom colors are set */
    public function test_default_colors_are_used_when_no_custom_colors_set()
    {
        $user = User::factory()->create();
        GymSetting::create([
            'id'              => 1,
            'gym_name'        => 'WarmUp Gym',
            'owner_name'      => 'Owner',
            'primary_color'   => null,
            'secondary_color' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        // Fallback defaults are the original WarmUp greens
        $response->assertSee('--gym-primary:   #22C55E', false);
        $response->assertSee('--gym-secondary: #16A34A', false);
    }

    /** 7. Invalid hex color values are rejected */
    public function test_invalid_primary_color_is_rejected()
    {
        $user = User::factory()->create();
        GymSetting::create(['id' => 1, 'gym_name' => 'Test Gym', 'owner_name' => 'Owner']);

        $response = $this->actingAs($user)->patch('/settings', $this->basePayload([
            'primary_color' => 'not-a-color',
        ]));

        $response->assertSessionHasErrors('primary_color');
    }

    public function test_invalid_secondary_color_is_rejected()
    {
        $user = User::factory()->create();
        GymSetting::create(['id' => 1, 'gym_name' => 'Test Gym', 'owner_name' => 'Owner']);

        $response = $this->actingAs($user)->patch('/settings', $this->basePayload([
            'secondary_color' => '#GGGGGG',
        ]));

        $response->assertSessionHasErrors('secondary_color');
    }

    public function test_short_hex_color_is_rejected()
    {
        $user = User::factory()->create();
        GymSetting::create(['id' => 1, 'gym_name' => 'Test Gym', 'owner_name' => 'Owner']);

        $response = $this->actingAs($user)->patch('/settings', $this->basePayload([
            'primary_color' => '#FFF',  // 3-digit shorthand — not accepted
        ]));

        $response->assertSessionHasErrors('primary_color');
    }

    /** 8. Existing settings functionality still works with colors present */
    public function test_existing_settings_still_work_with_colors_present()
    {
        $user = User::factory()->create();
        GymSetting::create(['id' => 1, 'gym_name' => 'Old Name', 'owner_name' => 'Old Owner']);

        $response = $this->actingAs($user)->patch('/settings', $this->basePayload([
            'gym_name'        => 'Updated Name',
            'primary_color'   => '#22C55E',
            'secondary_color' => '#16A34A',
        ]));

        $response->assertRedirect('/settings');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('gym_settings', [
            'gym_name'        => 'Updated Name',
            'primary_color'   => '#22C55E',
            'secondary_color' => '#16A34A',
        ]);
    }

    /** 9. Logo upload still works alongside color saving */
    public function test_logo_upload_works_alongside_color_saving()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        GymSetting::create(['id' => 1, 'gym_name' => 'Test Gym', 'owner_name' => 'Owner']);

        $file = UploadedFile::fake()->create('logo.png', 50, 'image/png');

        $response = $this->actingAs($user)->patch('/settings', $this->basePayload([
            'gym_logo'        => $file,
            'primary_color'   => '#AABBCC',
            'secondary_color' => '#112233',
        ]));

        $response->assertRedirect('/settings');

        $setting = GymSetting::first();
        $this->assertNotNull($setting->gym_logo);
        $this->assertEquals('#AABBCC', $setting->primary_color);
        $this->assertEquals('#112233', $setting->secondary_color);
        Storage::disk('public')->assertExists($setting->gym_logo);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Sidebar branding tests (carried forward from previous implementation)
    // ──────────────────────────────────────────────────────────────────────────

    public function test_layout_shows_dynamic_gym_name_in_sidebar()
    {
        $user = User::factory()->create();

        GymSetting::create([
            'gym_name'   => 'Iron Paradise',
            'owner_name' => 'Test Owner',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Iron');
        $response->assertSee('Paradise');
    }

    public function test_layout_falls_back_when_no_settings_exist()
    {
        $user = User::factory()->create();

        // No GymSetting row — the view composer returns null,
        // the settings.index controller then creates a default 'WarmUp Gym' record.
        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('WarmUp Gym');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helper
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Minimal valid settings payload, with optional overrides.
     */
    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'gym_name'        => 'Test Gym',
            'owner_name'      => 'Test Owner',
            'currency'        => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone'        => 'Asia/Karachi',
            'language'        => 'en',
            'theme'           => 'light',
            'date_format'     => 'd/m/Y',
            'time_format'     => '12h',
        ], $overrides);
    }
}
