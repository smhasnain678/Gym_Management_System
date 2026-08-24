<?php

namespace Tests\Feature;

use App\Models\FeePayment;
use App\Models\Member;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

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
            'name'          => 'Test Plan',
            'duration_days' => 30,
            'price'         => 1000,
            'is_active'     => true,
        ], $overrides));
    }

    private function createMember(array $overrides = []): Member
    {
        return Member::create(array_merge([
            'name'         => 'John Doe',
            'phone'        => '03001234567',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ], $overrides));
    }

    private function assignMembership(Member $member, MembershipPlan $plan, array $overrides = []): MemberMembership
    {
        $startDate = Carbon::parse($overrides['start_date'] ?? '2025-01-01');
        $paid = $overrides['paid_amount'] ?? 0;
        $total = (float) $plan->price;

        $mm = MemberMembership::create([
            'member_id'          => $member->id,
            'membership_plan_id' => $plan->id,
            'start_date'         => $startDate->toDateString(),
            'end_date'           => $startDate->copy()->addDays($plan->duration_days)->toDateString(),
            'total_amount'       => $total,
            'paid_amount'        => $paid,
            'remaining_amount'   => max(0, $total - $paid),
            'status'             => $overrides['status'] ?? 'active',
        ]);

        if ($paid > 0) {
            FeePayment::create([
                'member_id'            => $member->id,
                'member_membership_id' => $mm->id,
                'amount_paid'          => $paid,
                'payment_date'         => $startDate->toDateString(),
                'payment_method'       => 'cash',
            ]);
        }

        return $mm;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Auth protection
    // ─────────────────────────────────────────────────────────────────────────
    public function test_guests_cannot_access_members(): void
    {
        $this->get('/members')->assertRedirect('/login');
        $this->get('/members/create')->assertRedirect('/login');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. View member list
    // ─────────────────────────────────────────────────────────────────────────
    public function test_authenticated_owner_can_view_member_list(): void
    {
        $this->createMember(['name' => 'Alice']);
        $this->createMember(['name' => 'Bob']);

        $response = $this->actingAs($this->createOwner())->get('/members');
        $response->assertStatus(200);
        $response->assertSee('Alice');
        $response->assertSee('Bob');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. Create page loads
    // ─────────────────────────────────────────────────────────────────────────
    public function test_create_member_page_loads(): void
    {
        $response = $this->actingAs($this->createOwner())->get('/members/create');
        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Search works
    // ─────────────────────────────────────────────────────────────────────────
    public function test_search_filters_members(): void
    {
        $this->createMember(['name' => 'Alice Smith']);
        $this->createMember(['name' => 'Bob Jones']);

        $response = $this->actingAs($this->createOwner())->get('/members?search=Alice');
        $response->assertSee('Alice Smith');
        $response->assertDontSee('Bob Jones');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. Status filtering
    // ─────────────────────────────────────────────────────────────────────────
    public function test_status_filter_works(): void
    {
        $this->createMember(['name' => 'Active Member', 'status' => 'active']);
        $this->createMember(['name' => 'Suspended Member', 'status' => 'suspended']);

        $response = $this->actingAs($this->createOwner())->get('/members?status=suspended');
        $response->assertSee('Suspended Member');
        $response->assertDontSee('Active Member');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. Create member without plan
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_create_member_without_plan(): void
    {
        $response = $this->actingAs($this->createOwner())->post('/members', [
            'name'         => 'Jane Doe',
            'phone'        => '03110001111',
            'gender'       => 'female',
            'joining_date' => '2025-01-01',
            'status'       => 'active',
        ]);

        $member = Member::where('name', 'Jane Doe')->first();
        $this->assertNotNull($member);
        $response->assertRedirect(route('members.show', $member));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. Create member with immediate plan assignment + full payment
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_create_member_with_immediate_plan_assignment(): void
    {
        $plan = $this->createPlan();
        
        $response = $this->actingAs($this->createOwner())->post('/members', [
            'name'                  => 'Jane Plan',
            'phone'                 => '03110001111',
            'gender'                => 'female',
            'joining_date'          => '2025-01-01',
            'status'                => 'active',
            'membership_plan_id'    => $plan->id,
            'membership_start_date' => '2025-01-01',
            'paid_amount'           => '1000',
        ]);

        $member = Member::where('name', 'Jane Plan')->first();
        $this->assertNotNull($member);
        
        // Assert membership assigned with full payment
        $this->assertDatabaseHas('member_memberships', [
            'member_id'          => $member->id,
            'membership_plan_id' => $plan->id,
            'paid_amount'        => 1000,
            'remaining_amount'   => 0,
        ]);
        
        // Assert initial fee payment created
        $this->assertDatabaseHas('fee_payments', [
            'member_id'   => $member->id,
            'amount_paid' => 1000,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8. Create member with partial payment
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_create_member_with_partial_payment(): void
    {
        $plan = $this->createPlan(['price' => 2000]);
        
        $this->actingAs($this->createOwner())->post('/members', [
            'name'                  => 'Partial Payer',
            'phone'                 => '03111111111',
            'gender'                => 'male',
            'joining_date'          => '2025-01-01',
            'status'                => 'active',
            'membership_plan_id'    => $plan->id,
            'membership_start_date' => '2025-01-01',
            'paid_amount'           => '500',
        ]);

        $member = Member::where('name', 'Partial Payer')->first();
        $mm = MemberMembership::where('member_id', $member->id)->first();

        $this->assertEquals(2000, $mm->total_amount);
        $this->assertEquals(500, $mm->paid_amount);
        $this->assertEquals(1500, $mm->remaining_amount);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 9. Invalid creation rejected
    // ─────────────────────────────────────────────────────────────────────────
    public function test_invalid_member_creation_rejected(): void
    {
        $response = $this->actingAs($this->createOwner())->post('/members', []);
        $response->assertSessionHasErrors(['name', 'phone', 'gender', 'joining_date', 'status']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 10. Member show page loads
    // ─────────────────────────────────────────────────────────────────────────
    public function test_details_page_loads(): void
    {
        $member = $this->createMember();
        $response = $this->actingAs($this->createOwner())->get("/members/{$member->id}");
        $response->assertStatus(200);
        $response->assertSee($member->name);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 11. Edit page loads with existing values
    // ─────────────────────────────────────────────────────────────────────────
    public function test_edit_member_page_loads(): void
    {
        $member = $this->createMember(['name' => 'Edit Me']);
        $response = $this->actingAs($this->createOwner())->get("/members/{$member->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Edit Me');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 12. Update member
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_update_member(): void
    {
        $member = $this->createMember(['name' => 'Old Name']);

        $response = $this->actingAs($this->createOwner())->put("/members/{$member->id}", [
            'name'         => 'New Name',
            'phone'        => '03001234567',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        $this->assertDatabaseHas('members', ['id' => $member->id, 'name' => 'New Name']);
        $response->assertRedirect(route('members.show', $member));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 13. Safe deletion (soft delete)
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_soft_delete_member(): void
    {
        $member = $this->createMember();
        $response = $this->actingAs($this->createOwner())->delete("/members/{$member->id}");
        
        $response->assertRedirect('/members');
        $this->assertSoftDeleted('members', ['id' => $member->id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 14. Soft-deleted member is NOT visible in member list
    // ─────────────────────────────────────────────────────────────────────────
    public function test_soft_deleted_member_not_in_list(): void
    {
        $member = $this->createMember(['name' => 'Gone Member']);
        $member->delete();

        $response = $this->actingAs($this->createOwner())->get('/members');
        $response->assertDontSee('Gone Member');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 15. Membership assignment
    // ─────────────────────────────────────────────────────────────────────────
    public function test_assign_membership_to_existing_member(): void
    {
        $member = $this->createMember();
        $plan = $this->createPlan();

        $response = $this->actingAs($this->createOwner())->post("/members/{$member->id}/assign-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-01-01',
            'paid_amount'        => '500', // partial payment
        ]);

        $response->assertRedirect(route('members.show', $member));
        
        $membership = MemberMembership::where('member_id', $member->id)->first();
        $this->assertNotNull($membership);
        $this->assertEquals($plan->id, $membership->membership_plan_id);
        $this->assertEquals(1000, $membership->total_amount);
        $this->assertEquals(500, $membership->paid_amount);
        $this->assertEquals(500, $membership->remaining_amount);
        $this->assertEquals('2025-01-01', $membership->start_date->format('Y-m-d'));
        $this->assertEquals('2025-01-31', $membership->end_date->format('Y-m-d'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 16. Invalid assignment rejected
    // ─────────────────────────────────────────────────────────────────────────
    public function test_invalid_membership_assignment_rejected(): void
    {
        $member = $this->createMember();
        
        $response = $this->actingAs($this->createOwner())->post("/members/{$member->id}/assign-membership", [
            // missing plan id and start date
        ]);

        $response->assertSessionHasErrors(['membership_plan_id', 'start_date']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 17. Renewal creates new record and marks old as expired
    // ─────────────────────────────────────────────────────────────────────────
    public function test_renew_membership_creates_new_record_and_preserves_old(): void
    {
        $member = $this->createMember();
        $plan = $this->createPlan();
        $owner = $this->createOwner();

        // Assign first membership
        $this->actingAs($owner)->post("/members/{$member->id}/assign-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-01-01',
            'paid_amount'        => '1000', 
        ]);

        $oldMembership = MemberMembership::where('member_id', $member->id)->first();
        $this->assertNotNull($oldMembership);

        // Now renew
        $response = $this->actingAs($owner)->post("/members/{$member->id}/renew-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-02-01',
            'paid_amount'        => '1000', 
        ]);

        $response->assertRedirect(route('members.show', $member));

        // Member should have 2 membership records
        $this->assertEquals(2, $member->memberships()->count());

        // Old membership should be marked expired
        $oldMembership->refresh();
        $this->assertEquals('expired', $oldMembership->status);

        // New membership should be active
        $newMembership = MemberMembership::where('member_id', $member->id)->orderByDesc('id')->first();
        $this->assertEquals('2025-02-01', $newMembership->start_date->format('Y-m-d'));
        $this->assertEquals('active', $newMembership->status);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 18. Renewal with zero payment (allowed — pay later)
    // ─────────────────────────────────────────────────────────────────────────
    public function test_renew_membership_with_zero_payment_is_allowed(): void
    {
        $member = $this->createMember();
        $plan = $this->createPlan();
        $owner = $this->createOwner();

        $this->actingAs($owner)->post("/members/{$member->id}/assign-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-01-01',
            'paid_amount'        => '1000',
        ]);

        $response = $this->actingAs($owner)->post("/members/{$member->id}/renew-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-02-01',
            'paid_amount'        => '0', // no payment yet
        ]);

        $response->assertRedirect(route('members.show', $member));

        $newMembership = MemberMembership::where('member_id', $member->id)->orderByDesc('id')->first();
        $this->assertEquals(0, $newMembership->paid_amount);
        $this->assertEquals(1000, $newMembership->remaining_amount);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 19. Renewal date defaults — new start is day after old end
    // ─────────────────────────────────────────────────────────────────────────
    public function test_renewal_date_is_day_after_old_end(): void
    {
        $member = $this->createMember();
        $plan = $this->createPlan(['duration_days' => 30]);
        $owner = $this->createOwner();

        // Old membership ends 2025-01-31 (start 2025-01-01 + 30 days)
        $this->actingAs($owner)->post("/members/{$member->id}/assign-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-01-01',
            'paid_amount'        => '1000',
        ]);

        // Renew starting 2025-02-01 (the day after)
        $this->actingAs($owner)->post("/members/{$member->id}/renew-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-02-01',
            'paid_amount'        => '0',
        ]);

        $newMembership = MemberMembership::where('member_id', $member->id)->orderByDesc('id')->first();
        $this->assertEquals('2025-02-01', $newMembership->start_date->format('Y-m-d'));
        // End should be 2025-02-01 + 30 days = 2025-03-03
        $this->assertEquals('2025-03-03', $newMembership->end_date->format('Y-m-d'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 20. Record Payment updates membership amounts correctly
    // ─────────────────────────────────────────────────────────────────────────
    public function test_record_payment_updates_membership_amounts(): void
    {
        $member = $this->createMember();
        $plan = $this->createPlan();
        $owner = $this->createOwner();

        // Assign plan with 0 paid
        $this->actingAs($owner)->post("/members/{$member->id}/assign-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-01-01',
            'paid_amount'        => '0', 
        ]);

        $membership = $member->memberships()->first();
        $this->assertEquals(0, $membership->paid_amount);
        $this->assertEquals(1000, $membership->remaining_amount);

        // Record partial payment
        $response = $this->actingAs($owner)->post("/members/{$member->id}/record-payment", [
            'member_membership_id' => $membership->id,
            'amount_paid'          => '400',
            'payment_date'         => '2025-01-15',
            'payment_method'       => 'cash',
        ]);

        $response->assertRedirect(route('members.show', $member));

        $membership->refresh();
        $this->assertEquals(400, $membership->paid_amount);
        $this->assertEquals(600, $membership->remaining_amount);
        
        $this->assertDatabaseHas('fee_payments', [
            'member_id'            => $member->id,
            'member_membership_id' => $membership->id,
            'amount_paid'          => 400,
            'payment_method'       => 'cash',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 21. Payment cannot exceed remaining balance
    // ─────────────────────────────────────────────────────────────────────────
    public function test_payment_cannot_exceed_remaining_balance(): void
    {
        $member = $this->createMember();
        $plan = $this->createPlan(['price' => 1000]);
        $owner = $this->createOwner();

        $this->actingAs($owner)->post("/members/{$member->id}/assign-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-01-01',
            'paid_amount'        => '500', // 500 remaining
        ]);

        $membership = $member->memberships()->first();

        // Try to pay more than remaining
        $response = $this->actingAs($owner)->post("/members/{$member->id}/record-payment", [
            'member_membership_id' => $membership->id,
            'amount_paid'          => '600', // exceeds 500 remaining
            'payment_date'         => '2025-01-15',
            'payment_method'       => 'cash',
        ]);

        $response->assertSessionHasErrors(['amount_paid']);

        // Membership amounts unchanged
        $membership->refresh();
        $this->assertEquals(500, $membership->paid_amount);
        $this->assertEquals(500, $membership->remaining_amount);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 22. Payment cannot be zero or negative
    // ─────────────────────────────────────────────────────────────────────────
    public function test_payment_cannot_be_zero_or_negative(): void
    {
        $member = $this->createMember();
        $plan = $this->createPlan();
        $owner = $this->createOwner();

        $this->actingAs($owner)->post("/members/{$member->id}/assign-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-01-01',
            'paid_amount'        => '0',
        ]);

        $membership = $member->memberships()->first();

        // Try zero payment
        $response = $this->actingAs($owner)->post("/members/{$member->id}/record-payment", [
            'member_membership_id' => $membership->id,
            'amount_paid'          => '0',
            'payment_date'         => '2025-01-15',
            'payment_method'       => 'cash',
        ]);
        $response->assertSessionHasErrors(['amount_paid']);

        // Try negative payment
        $response2 = $this->actingAs($owner)->post("/members/{$member->id}/record-payment", [
            'member_membership_id' => $membership->id,
            'amount_paid'          => '-100',
            'payment_date'         => '2025-01-15',
            'payment_method'       => 'cash',
        ]);
        $response2->assertSessionHasErrors(['amount_paid']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 23. Full payment clears remaining balance
    // ─────────────────────────────────────────────────────────────────────────
    public function test_full_payment_clears_remaining_balance(): void
    {
        $member = $this->createMember();
        $plan = $this->createPlan(['price' => 1000]);
        $owner = $this->createOwner();

        $this->actingAs($owner)->post("/members/{$member->id}/assign-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-01-01',
            'paid_amount'        => '0',
        ]);

        $membership = $member->memberships()->first();

        $this->actingAs($owner)->post("/members/{$member->id}/record-payment", [
            'member_membership_id' => $membership->id,
            'amount_paid'          => '1000',
            'payment_date'         => '2025-01-15',
            'payment_method'       => 'easypaisa',
        ]);

        $membership->refresh();
        $this->assertEquals(1000, $membership->paid_amount);
        $this->assertEquals(0, $membership->remaining_amount);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 24. Payment history accumulates correctly (multiple payments)
    // ─────────────────────────────────────────────────────────────────────────
    public function test_multiple_payments_accumulate_correctly(): void
    {
        $member = $this->createMember();
        $plan = $this->createPlan(['price' => 1000]);
        $owner = $this->createOwner();

        $this->actingAs($owner)->post("/members/{$member->id}/assign-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-01-01',
            'paid_amount'        => '0',
        ]);

        $membership = $member->memberships()->first();

        // First payment
        $this->actingAs($owner)->post("/members/{$member->id}/record-payment", [
            'member_membership_id' => $membership->id,
            'amount_paid'          => '300',
            'payment_date'         => '2025-01-05',
            'payment_method'       => 'cash',
        ]);

        // Second payment
        $this->actingAs($owner)->post("/members/{$member->id}/record-payment", [
            'member_membership_id' => $membership->id,
            'amount_paid'          => '400',
            'payment_date'         => '2025-01-15',
            'payment_method'       => 'bank_transfer',
        ]);

        $membership->refresh();
        $this->assertEquals(700, $membership->paid_amount);
        $this->assertEquals(300, $membership->remaining_amount);

        // Two payment records in DB
        $this->assertEquals(2, FeePayment::where('member_membership_id', $membership->id)->count());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 25. Suspend member
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_suspend_member(): void
    {
        $member = $this->createMember(['status' => 'active']);
        $owner = $this->createOwner();

        $response = $this->actingAs($owner)->patch("/members/{$member->id}/toggle-status");

        $response->assertRedirect(route('members.show', $member));
        $this->assertEquals('suspended', $member->fresh()->status);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 26. Activate suspended member
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_activate_suspended_member(): void
    {
        $member = $this->createMember(['status' => 'suspended']);
        $owner = $this->createOwner();

        $response = $this->actingAs($owner)->patch("/members/{$member->id}/toggle-status");

        $response->assertRedirect(route('members.show', $member));
        $this->assertEquals('active', $member->fresh()->status);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 27. Suspended member still exists in database (not hard deleted)
    // ─────────────────────────────────────────────────────────────────────────
    public function test_suspended_member_remains_in_database(): void
    {
        $member = $this->createMember(['status' => 'active']);
        $owner = $this->createOwner();

        $this->actingAs($owner)->patch("/members/{$member->id}/toggle-status");

        $this->assertDatabaseHas('members', ['id' => $member->id, 'status' => 'suspended']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 28. Membership history is preserved after renewal
    // ─────────────────────────────────────────────────────────────────────────
    public function test_membership_history_preserved_after_renewal(): void
    {
        $member = $this->createMember();
        $plan = $this->createPlan();
        $owner = $this->createOwner();

        // First membership
        $this->actingAs($owner)->post("/members/{$member->id}/assign-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-01-01',
            'paid_amount'        => '1000',
        ]);

        // Second (renewal)
        $this->actingAs($owner)->post("/members/{$member->id}/renew-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-02-01',
            'paid_amount'        => '800',
        ]);

        // Third (another renewal)
        $this->actingAs($owner)->post("/members/{$member->id}/renew-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-03-03',
            'paid_amount'        => '0',
        ]);

        // All 3 memberships are preserved
        $this->assertEquals(3, $member->memberships()->count());

        // Oldest memberships should be expired, newest active
        $memberships = $member->memberships()->orderBy('id')->get();
        $this->assertEquals('expired', $memberships[0]->status);
        $this->assertEquals('expired', $memberships[1]->status);
        $this->assertEquals('active', $memberships[2]->status);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 29. Payment is linked to correct membership
    // ─────────────────────────────────────────────────────────────────────────
    public function test_payment_is_linked_to_correct_membership(): void
    {
        $member = $this->createMember();
        $plan = $this->createPlan();
        $owner = $this->createOwner();

        $this->actingAs($owner)->post("/members/{$member->id}/assign-membership", [
            'membership_plan_id' => $plan->id,
            'start_date'         => '2025-01-01',
            'paid_amount'        => '0',
        ]);

        $membership = $member->memberships()->first();

        $this->actingAs($owner)->post("/members/{$member->id}/record-payment", [
            'member_membership_id' => $membership->id,
            'amount_paid'          => '500',
            'payment_date'         => '2025-01-10',
            'payment_method'       => 'jazzcash',
        ]);

        $this->assertDatabaseHas('fee_payments', [
            'member_id'            => $member->id,
            'member_membership_id' => $membership->id,
            'amount_paid'          => 500,
            'payment_method'       => 'jazzcash',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 30. Gender filter works
    // ─────────────────────────────────────────────────────────────────────────
    public function test_gender_filter_works(): void
    {
        $this->createMember(['name' => 'Male Member', 'gender' => 'male']);
        $this->createMember(['name' => 'Female Member', 'gender' => 'female', 'phone' => '03001234568']);

        $response = $this->actingAs($this->createOwner())->get('/members?gender=female');
        $response->assertSee('Female Member');
        $response->assertDontSee('Male Member');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 31. Photo Upload Tests
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_create_member_with_profile_photo(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->createOwner())->post('/members', [
            'name'         => 'Test Photo Member',
            'phone'        => '03001234567',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
            'profile_photo'=> $file,
        ]);

        $response->assertRedirect();
        $member = Member::where('name', 'Test Photo Member')->first();
        $this->assertNotNull($member->profile_photo);
        Storage::disk('public')->assertExists($member->profile_photo);
    }

    public function test_owner_can_update_member_profile_photo(): void
    {
        Storage::fake('public');
        $member = $this->createMember(['name' => 'Photo Update Test']);
        $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->createOwner())->put("/members/{$member->id}", [
            'name'         => 'Photo Update Test',
            'phone'        => '03001234567',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
            'profile_photo'=> $file,
        ]);

        $response->assertRedirect(route('members.show', $member));
        $member->refresh();
        $this->assertNotNull($member->profile_photo);
        Storage::disk('public')->assertExists($member->profile_photo);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 32. Email uniqueness ignores soft-deleted members
    // ─────────────────────────────────────────────────────────────────────────
    public function test_can_reuse_email_of_soft_deleted_member(): void
    {
        // 1. Create a member with email
        $firstMember = $this->createMember(['email' => 'reuse@example.com']);

        // 2. Soft-delete the member
        $firstMember->delete();

        // 3. Create another member with the same email
        $response = $this->actingAs($this->createOwner())->post('/members', [
            'name'         => 'Second Member',
            'email'        => 'reuse@example.com',
            'phone'        => '03001234567',
            'gender'       => 'female',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('members', [
            'name'  => 'Second Member',
            'email' => 'reuse@example.com',
            'deleted_at' => null,
        ]);
    }
}
