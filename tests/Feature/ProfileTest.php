<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createOwner(): User
    {
        return User::factory()->create([
            'name'     => 'Test Owner',
            'email'    => 'owner@warmup.test',
            'password' => bcrypt('password123'),
            'phone'    => '03001234567',
        ]);
    }

    // -----------------------------------------------------------------------
    // Profile Page
    // -----------------------------------------------------------------------

    public function test_profile_page_loads_for_authenticated_user(): void
    {
        $user = $this->createOwner();
        $response = $this->actingAs($user)->get(route('profile.edit'));
        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    // -----------------------------------------------------------------------
    // Profile Update
    // -----------------------------------------------------------------------

    public function test_owner_can_update_name_and_phone(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name'  => 'Updated Owner Name',
            'email' => 'owner@warmup.test',
            'phone' => '03009876543',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('profile_updated');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Updated Owner Name',
            'phone' => '03009876543',
        ]);
    }

    public function test_profile_update_requires_name(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name'  => '',
            'email' => 'owner@warmup.test',
        ]);

        $response->assertSessionHasErrors('name', null, 'updateProfile');
    }

    public function test_profile_update_requires_valid_email(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name'  => 'Test Owner',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email', null, 'updateProfile');
    }

    // -----------------------------------------------------------------------
    // Password Change
    // -----------------------------------------------------------------------

    public function test_owner_can_change_password(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password'      => 'password123',
            'password'              => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        // After password change, user is logged out and redirected to login
        $response->assertRedirect(route('login'));
        $this->assertGuest();

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword456', $user->password));
    }

    public function test_password_change_fails_with_wrong_current_password(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password'      => 'wrongpassword',
            'password'              => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertSessionHasErrors('current_password', null, 'updatePassword');
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_change_requires_confirmation_match(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password'      => 'password123',
            'password'              => 'newpassword456',
            'password_confirmation' => 'doesnotmatch',
        ]);

        $response->assertSessionHasErrors('password', null, 'updatePassword');
    }

    public function test_password_change_requires_minimum_length(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password'      => 'password123',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password', null, 'updatePassword');
    }
}
