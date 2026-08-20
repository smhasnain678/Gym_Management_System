<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipPlanTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createOwner(): User
    {
        return User::factory()->create([
            'name'      => 'Test Owner',
            'email'     => 'owner@warmup.test',
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);
    }

    private function createPlan(array $overrides = []): MembershipPlan
    {
        return MembershipPlan::create(array_merge([
            'name'          => 'Monthly Plan',
            'duration_days' => 30,
            'price'         => 2000.00,
            'description'   => 'A standard monthly plan.',
            'color'         => '#22C55E',
            'sort_order'    => 0,
            'is_active'     => true,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // 1. Guest access is denied
    // -------------------------------------------------------------------------

    public function test_guests_cannot_access_membership_plans_index(): void
    {
        $response = $this->get('/membership-plans');
        $response->assertRedirect('/login');
    }

    public function test_guests_cannot_access_create_form(): void
    {
        $response = $this->get('/membership-plans/create');
        $response->assertRedirect('/login');
    }

    public function test_guests_cannot_post_store(): void
    {
        $response = $this->post('/membership-plans', []);
        $response->assertRedirect('/login');
    }

    public function test_guests_cannot_access_edit_form(): void
    {
        $plan = $this->createPlan();
        $response = $this->get("/membership-plans/{$plan->id}/edit");
        $response->assertRedirect('/login');
    }

    public function test_guests_cannot_submit_update(): void
    {
        $plan = $this->createPlan();
        $response = $this->put("/membership-plans/{$plan->id}", []);
        $response->assertRedirect('/login');
    }

    public function test_guests_cannot_delete(): void
    {
        $plan = $this->createPlan();
        $response = $this->delete("/membership-plans/{$plan->id}");
        $response->assertRedirect('/login');
    }

    // -------------------------------------------------------------------------
    // 2. Authenticated owner can view plans
    // -------------------------------------------------------------------------

    public function test_authenticated_owner_can_view_index(): void
    {
        $user = $this->createOwner();
        $plan = $this->createPlan(['name' => 'Silver Plan']);

        $response = $this->actingAs($user)->get('/membership-plans');

        $response->assertStatus(200);
        $response->assertViewIs('membership-plans.index');
        $response->assertSee('Silver Plan');
    }

    // -------------------------------------------------------------------------
    // 3. Owner can open the create form
    // -------------------------------------------------------------------------

    public function test_owner_can_open_create_form(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->get('/membership-plans/create');

        $response->assertStatus(200);
        $response->assertViewIs('membership-plans.create');
    }

    // -------------------------------------------------------------------------
    // 4. Valid plan can be created
    // -------------------------------------------------------------------------

    public function test_owner_can_create_a_valid_plan(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->post('/membership-plans', [
            'name'          => 'Gold Plan',
            'duration_days' => 90,
            'price'         => 5000.00,
            'description'   => 'Three month premium plan.',
            'color'         => '#F59E0B',
            'sort_order'    => 1,
            'is_active'     => 1,
        ]);

        $response->assertRedirect(route('membership-plans.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('membership_plans', [
            'name'          => 'Gold Plan',
            'duration_days' => 90,
            'is_active'     => 1,
        ]);
    }

    // -------------------------------------------------------------------------
    // 5. Invalid input is rejected
    // -------------------------------------------------------------------------

    public function test_missing_required_fields_are_rejected(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->post('/membership-plans', []);

        $response->assertSessionHasErrors(['name', 'duration_days', 'price']);
    }

    public function test_duplicate_plan_name_is_rejected(): void
    {
        $user = $this->createOwner();
        $this->createPlan(['name' => 'Yearly Plan']);

        $response = $this->actingAs($user)->post('/membership-plans', [
            'name'          => 'Yearly Plan',
            'duration_days' => 365,
            'price'         => 10000.00,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_negative_price_is_rejected(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->post('/membership-plans', [
            'name'          => 'Cheap Plan',
            'duration_days' => 30,
            'price'         => -50,
        ]);

        $response->assertSessionHasErrors(['price']);
    }

    public function test_invalid_duration_is_rejected(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->post('/membership-plans', [
            'name'          => 'Invalid Duration Plan',
            'duration_days' => 0,
            'price'         => 500,
        ]);

        $response->assertSessionHasErrors(['duration_days']);
    }

    public function test_invalid_color_format_is_rejected(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->post('/membership-plans', [
            'name'          => 'Bad Color Plan',
            'duration_days' => 30,
            'price'         => 500,
            'color'         => 'not-a-color',
        ]);

        $response->assertSessionHasErrors(['color']);
    }

    // -------------------------------------------------------------------------
    // 6. Existing plan can be edited
    // -------------------------------------------------------------------------

    public function test_owner_can_open_edit_form(): void
    {
        $user = $this->createOwner();
        $plan = $this->createPlan(['name' => 'Platinum Plan']);

        $response = $this->actingAs($user)->get("/membership-plans/{$plan->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('membership-plans.edit');
        $response->assertSee('Platinum Plan');
    }

    public function test_owner_can_update_a_plan(): void
    {
        $user = $this->createOwner();
        $plan = $this->createPlan(['name' => 'Old Name']);

        $response = $this->actingAs($user)->put("/membership-plans/{$plan->id}", [
            'name'          => 'Updated Name',
            'duration_days' => 60,
            'price'         => 3500.00,
            'description'   => 'Updated description.',
            'color'         => '#3B82F6',
            'sort_order'    => 2,
            'is_active'     => 1,
        ]);

        $response->assertRedirect(route('membership-plans.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('membership_plans', [
            'id'            => $plan->id,
            'name'          => 'Updated Name',
            'duration_days' => 60,
        ]);
    }

    public function test_update_rejects_duplicate_name_for_different_plan(): void
    {
        $user  = $this->createOwner();
        $planA = $this->createPlan(['name' => 'Plan A']);
        $planB = $this->createPlan(['name' => 'Plan B']);

        // Try to rename Plan B to Plan A's name
        $response = $this->actingAs($user)->put("/membership-plans/{$planB->id}", [
            'name'          => 'Plan A',
            'duration_days' => 30,
            'price'         => 1000,
            'is_active'     => 1,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_allows_same_name_for_same_plan(): void
    {
        $user = $this->createOwner();
        $plan = $this->createPlan(['name' => 'Same Name Plan']);

        $response = $this->actingAs($user)->put("/membership-plans/{$plan->id}", [
            'name'          => 'Same Name Plan',
            'duration_days' => 45,
            'price'         => 2500.00,
            'is_active'     => 1,
        ]);

        $response->assertRedirect(route('membership-plans.index'));
        $response->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // 7. Plan can be activated / deactivated
    // -------------------------------------------------------------------------

    public function test_active_plan_can_be_deactivated_via_toggle(): void
    {
        $user = $this->createOwner();
        $plan = $this->createPlan(['is_active' => true]);

        $response = $this->actingAs($user)
            ->patch("/membership-plans/{$plan->id}/toggle-status");

        $response->assertRedirect(route('membership-plans.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('membership_plans', [
            'id'        => $plan->id,
            'is_active' => false,
        ]);
    }

    public function test_inactive_plan_can_be_activated_via_toggle(): void
    {
        $user = $this->createOwner();
        $plan = $this->createPlan(['is_active' => false]);

        $response = $this->actingAs($user)
            ->patch("/membership-plans/{$plan->id}/toggle-status");

        $response->assertRedirect(route('membership-plans.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('membership_plans', [
            'id'        => $plan->id,
            'is_active' => true,
        ]);
    }

    public function test_plan_can_be_deactivated_via_update(): void
    {
        $user = $this->createOwner();
        $plan = $this->createPlan(['is_active' => true]);

        $this->actingAs($user)->put("/membership-plans/{$plan->id}", [
            'name'          => $plan->name,
            'duration_days' => $plan->duration_days,
            'price'         => $plan->price,
            'is_active'     => 0,
        ]);

        $this->assertDatabaseHas('membership_plans', [
            'id'        => $plan->id,
            'is_active' => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // 8. Plan without member assignments can be deleted
    // -------------------------------------------------------------------------

    public function test_plan_without_assignments_can_be_deleted(): void
    {
        $user = $this->createOwner();
        $plan = $this->createPlan(['name' => 'Deletable Plan']);

        $response = $this->actingAs($user)
            ->delete("/membership-plans/{$plan->id}");

        $response->assertRedirect(route('membership-plans.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('membership_plans', ['id' => $plan->id]);
    }

    // -------------------------------------------------------------------------
    // 9. Plan with existing member memberships cannot be deleted
    // -------------------------------------------------------------------------

    public function test_plan_with_member_memberships_cannot_be_deleted(): void
    {
        $user = $this->createOwner();
        $plan = $this->createPlan(['name' => 'In Use Plan']);

        // Create a member and assign a membership to this plan
        $member = Member::create([
            'name'         => 'Test Member',
            'phone'        => '03001234567',
            'email'        => 'member@test.com',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        MemberMembership::create([
            'member_id'          => $member->id,
            'membership_plan_id' => $plan->id,
            'start_date'         => now()->toDateString(),
            'end_date'           => now()->addDays(30)->toDateString(),
            'total_amount'       => $plan->price,
            'paid_amount'        => $plan->price,
            'remaining_amount'   => 0,
            'status'             => 'active',
        ]);

        $response = $this->actingAs($user)
            ->delete("/membership-plans/{$plan->id}");

        $response->assertRedirect(route('membership-plans.index'));
        $response->assertSessionHas('error');

        // Plan must still exist
        $this->assertDatabaseHas('membership_plans', ['id' => $plan->id]);
    }

    // -------------------------------------------------------------------------
    // 10. Flash messages
    // -------------------------------------------------------------------------

    public function test_success_flash_shown_on_create(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->post('/membership-plans', [
            'name'          => 'Flash Test Plan',
            'duration_days' => 30,
            'price'         => 1500,
            'is_active'     => 1,
        ]);

        $response->assertSessionHas('success');
    }

    public function test_error_flash_shown_when_delete_blocked(): void
    {
        $user   = $this->createOwner();
        $plan   = $this->createPlan(['name' => 'Blocked Plan']);
        $member = Member::create([
            'name'         => 'Member 2',
            'phone'        => '03009876543',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        MemberMembership::create([
            'member_id'          => $member->id,
            'membership_plan_id' => $plan->id,
            'start_date'         => now()->toDateString(),
            'end_date'           => now()->addDays(30)->toDateString(),
            'total_amount'       => $plan->price,
            'paid_amount'        => 0,
            'remaining_amount'   => $plan->price,
            'status'             => 'active',
        ]);

        $response = $this->actingAs($user)->delete("/membership-plans/{$plan->id}");
        $response->assertSessionHas('error');
    }

    // -------------------------------------------------------------------------
    // 11. Search and filter
    // -------------------------------------------------------------------------

    public function test_search_filters_plans_by_name(): void
    {
        $user = $this->createOwner();
        $this->createPlan(['name' => 'Gold Plan']);
        $this->createPlan(['name' => 'Silver Plan']);

        $response = $this->actingAs($user)->get('/membership-plans?search=Gold');

        $response->assertStatus(200);
        $response->assertSee('Gold Plan');
        $response->assertDontSee('Silver Plan');
    }

    public function test_status_filter_shows_only_active_plans(): void
    {
        $user = $this->createOwner();
        $this->createPlan(['name' => 'Active Plan',   'is_active' => true]);
        $this->createPlan(['name' => 'Inactive Plan', 'is_active' => false]);

        $response = $this->actingAs($user)->get('/membership-plans?status=active');

        $response->assertStatus(200);
        $response->assertSee('Active Plan');
        $response->assertDontSee('Inactive Plan');
    }

    public function test_status_filter_shows_only_inactive_plans(): void
    {
        $user = $this->createOwner();
        $this->createPlan(['name' => 'Active Plan',   'is_active' => true]);
        $this->createPlan(['name' => 'Inactive Plan', 'is_active' => false]);

        $response = $this->actingAs($user)->get('/membership-plans?status=inactive');

        $response->assertStatus(200);
        $response->assertSee('Inactive Plan');
        $response->assertDontSee('Active Plan');
    }
}
