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
            'id' => 1,
            'gym_name' => 'WarmUp Gym',
        ]);
    }

    public function test_authenticated_user_can_update_settings()
    {
        $user = User::factory()->create();
        
        GymSetting::create([
            'id' => 1,
            'gym_name' => 'Old Gym',
            'owner_name' => 'Old Owner'
        ]);

        $response = $this->actingAs($user)->patch('/settings', [
            'gym_name' => 'New Gym Name',
            'owner_name' => 'New Owner Name',
            'contact_email' => 'contact@newgym.com',
            'contact_phone' => '1234567890',
            'address' => '123 New Street',
            'country' => 'Pakistan',
            'city' => 'Karachi',
            'currency' => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone' => 'Asia/Karachi',
            'language' => 'en',
            'theme' => 'light',
            'date_format' => 'd/m/Y',
            'time_format' => '12h'
        ]);

        $response->assertRedirect('/settings');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('gym_settings', [
            'gym_name' => 'New Gym Name',
            'owner_name' => 'New Owner Name',
            'currency' => 'PKR'
        ]);
    }

    public function test_authenticated_user_can_upload_logo()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        
        GymSetting::create([
            'id' => 1,
            'gym_name' => 'Test Gym',
            'owner_name' => 'Test Owner'
        ]);

        $file = UploadedFile::fake()->create('logo.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->patch('/settings', [
            'gym_name' => 'Test Gym',
            'owner_name' => 'Test Owner',
            'currency' => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone' => 'Asia/Karachi',
            'language' => 'en',
            'theme' => 'light',
            'date_format' => 'd/m/Y',
            'time_format' => '12h',
            'gym_logo' => $file,
        ]);

        $response->assertRedirect('/settings');

        $setting = GymSetting::first();
        $this->assertNotNull($setting->gym_logo);
        Storage::disk('public')->assertExists($setting->gym_logo);
    }
}
