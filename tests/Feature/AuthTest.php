<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a test Gym Owner user.
     */
    private function createOwner(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'name'      => 'Test Owner',
            'email'     => 'owner@warmup.test',
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ], $overrides));
    }

    // -----------------------------------------------------------------------
    // Login Page
    // -----------------------------------------------------------------------

    public function test_login_page_loads(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_authenticated_user_is_redirected_from_login(): void
    {
        $user = $this->createOwner();
        $response = $this->actingAs($user)->get(route('login'));
        $response->assertRedirect(route('dashboard'));
    }

    // -----------------------------------------------------------------------
    // Login Attempt
    // -----------------------------------------------------------------------

    public function test_login_with_valid_credentials(): void
    {
        $user = $this->createOwner();

        $response = $this->post(route('login'), [
            'email'    => 'owner@warmup.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->createOwner();

        $response = $this->post(route('login'), [
            'email'    => 'owner@warmup.test',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect(); // back to login
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->post(route('login'), [
            'email'    => 'nobody@warmup.test',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_requires_email(): void
    {
        $response = $this->post(route('login'), [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_requires_password(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'owner@warmup.test',
        ]);

        $response->assertSessionHasErrors('password');
    }

    // -----------------------------------------------------------------------
    // Protected Routes
    // -----------------------------------------------------------------------

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_profile_requires_authentication(): void
    {
        $response = $this->get(route('profile.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = $this->createOwner();
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    // -----------------------------------------------------------------------
    // Logout
    // -----------------------------------------------------------------------

    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // -----------------------------------------------------------------------
    // Forgot Password
    // -----------------------------------------------------------------------

    public function test_forgot_password_page_loads(): void
    {
        $response = $this->get(route('password.request'));
        $response->assertStatus(200);
    }

    public function test_forgot_password_redirects_to_reset_form_for_known_email(): void
    {
        $this->createOwner();

        $response = $this->post(route('password.email'), [
            'email' => 'owner@warmup.test',
        ]);

        // FYP flow: redirects directly to the reset form
        $response->assertRedirect();
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'owner@warmup.test']);
    }

    public function test_forgot_password_shows_success_for_unknown_email(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => 'nobody@warmup.test',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'nobody@warmup.test']);
    }
}
