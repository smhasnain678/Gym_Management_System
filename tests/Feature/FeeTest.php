<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\FeePayment;
use App\Models\Member;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    protected function owner(): User
    {
        return User::factory()->create(['is_active' => true]);
    }

    protected function createMember(array $overrides = []): Member
    {
        return Member::create(array_merge([
            'name'         => 'Fee Member',
            'phone'        => '03001234567',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ], $overrides));
    }

    protected function createPlan(array $overrides = []): MembershipPlan
    {
        return MembershipPlan::create(array_merge([
            'name'          => 'Monthly Plan',
            'duration_days' => 30,
            'price'         => 3000.00,
            'is_active'     => true,
        ], $overrides));
    }

    protected function createMembership(Member $member, MembershipPlan $plan, array $overrides = []): MemberMembership
    {
        return MemberMembership::create(array_merge([
            'member_id'          => $member->id,
            'membership_plan_id' => $plan->id,
            'start_date'         => now()->toDateString(),
            'end_date'           => now()->addDays(30)->toDateString(),
            'total_amount'       => $plan->price,
            'paid_amount'        => 0,
            'remaining_amount'   => $plan->price,
            'status'             => 'active',
        ], $overrides));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Guest cannot access Fee Management
    // ─────────────────────────────────────────────────────────────────────────
    public function test_guest_cannot_access_fee_management(): void
    {
        $this->get('/fees')->assertRedirect('/login');
        $this->get('/fees/history')->assertRedirect('/login');
        $this->post('/fees/pay')->assertRedirect('/login');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Authenticated owner can access Fee Management
    // ─────────────────────────────────────────────────────────────────────────
    public function test_authenticated_owner_can_access_fee_management(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner)->get('/fees')->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. Fee page renders
    // ─────────────────────────────────────────────────────────────────────────
    public function test_fee_page_renders_correctly(): void
    {
        $owner = $this->owner();
        $response = $this->actingAs($owner)->get('/fees');

        $response->assertStatus(200);
        $response->assertSee('Fee Management');
        $response->assertSee('Total Expected');
        $response->assertSee('Pending Fees');
        $response->assertSee('Recent Payments');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Pending fee list displays outstanding memberships
    // ─────────────────────────────────────────────────────────────────────────
    public function test_pending_fee_list_shows_outstanding_memberships(): void
    {
        $owner  = $this->owner();
        $member = $this->createMember();
        $plan   = $this->createPlan();

        // Membership with remaining balance
        $this->createMembership($member, $plan, ['remaining_amount' => 1500, 'paid_amount' => 1500]);

        $response = $this->actingAs($owner)->get('/fees');
        $response->assertStatus(200);
        $response->assertSee($member->name);
        $response->assertSee('Record Payment');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. Payment can be recorded
    // ─────────────────────────────────────────────────────────────────────────
    public function test_payment_can_be_recorded(): void
    {
        $owner      = $this->owner();
        $member     = $this->createMember();
        $plan       = $this->createPlan();
        $membership = $this->createMembership($member, $plan);

        $response = $this->actingAs($owner)->post('/fees/pay', [
            'member_membership_id' => $membership->id,
            'amount_paid'          => 1000,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fee_payments', [
            'member_id'            => $member->id,
            'member_membership_id' => $membership->id,
            'amount_paid'          => 1000,
            'payment_method'       => 'cash',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. Paid amount increases correctly
    // ─────────────────────────────────────────────────────────────────────────
    public function test_paid_amount_increases_after_payment(): void
    {
        $owner      = $this->owner();
        $member     = $this->createMember();
        $plan       = $this->createPlan(['price' => 3000]);
        $membership = $this->createMembership($member, $plan, ['total_amount' => 3000, 'paid_amount' => 0, 'remaining_amount' => 3000]);

        $this->actingAs($owner)->post('/fees/pay', [
            'member_membership_id' => $membership->id,
            'amount_paid'          => 1000,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $membership->refresh();
        $this->assertEquals(1000.00, (float) $membership->paid_amount);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. Remaining amount decreases correctly
    // ─────────────────────────────────────────────────────────────────────────
    public function test_remaining_amount_decreases_after_payment(): void
    {
        $owner      = $this->owner();
        $member     = $this->createMember();
        $plan       = $this->createPlan(['price' => 3000]);
        $membership = $this->createMembership($member, $plan, ['total_amount' => 3000, 'paid_amount' => 0, 'remaining_amount' => 3000]);

        $this->actingAs($owner)->post('/fees/pay', [
            'member_membership_id' => $membership->id,
            'amount_paid'          => 2000,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $membership->refresh();
        $this->assertEquals(1000.00, (float) $membership->remaining_amount);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8. Full payment makes remaining amount zero
    // ─────────────────────────────────────────────────────────────────────────
    public function test_full_payment_makes_remaining_zero(): void
    {
        $owner      = $this->owner();
        $member     = $this->createMember();
        $plan       = $this->createPlan(['price' => 3000]);
        $membership = $this->createMembership($member, $plan, ['total_amount' => 3000, 'paid_amount' => 0, 'remaining_amount' => 3000]);

        $this->actingAs($owner)->post('/fees/pay', [
            'member_membership_id' => $membership->id,
            'amount_paid'          => 3000,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $membership->refresh();
        $this->assertEquals(0.00, (float) $membership->remaining_amount);
        $this->assertEquals(3000.00, (float) $membership->paid_amount);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 9. Payment greater than remaining balance is rejected
    // ─────────────────────────────────────────────────────────────────────────
    public function test_overpayment_is_rejected(): void
    {
        $owner      = $this->owner();
        $member     = $this->createMember();
        $plan       = $this->createPlan(['price' => 3000]);
        $membership = $this->createMembership($member, $plan, ['total_amount' => 3000, 'paid_amount' => 2500, 'remaining_amount' => 500]);

        $response = $this->actingAs($owner)->post('/fees/pay', [
            'member_membership_id' => $membership->id,
            'amount_paid'          => 600,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $response->assertSessionHasErrors('amount_paid');
        $this->assertDatabaseEmpty('fee_payments');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 10. Zero payment is rejected
    // ─────────────────────────────────────────────────────────────────────────
    public function test_zero_payment_is_rejected(): void
    {
        $owner      = $this->owner();
        $member     = $this->createMember();
        $plan       = $this->createPlan();
        $membership = $this->createMembership($member, $plan);

        $response = $this->actingAs($owner)->post('/fees/pay', [
            'member_membership_id' => $membership->id,
            'amount_paid'          => 0,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $response->assertSessionHasErrors('amount_paid');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 11. Negative payment is rejected
    // ─────────────────────────────────────────────────────────────────────────
    public function test_negative_payment_is_rejected(): void
    {
        $owner      = $this->owner();
        $member     = $this->createMember();
        $plan       = $this->createPlan();
        $membership = $this->createMembership($member, $plan);

        $response = $this->actingAs($owner)->post('/fees/pay', [
            'member_membership_id' => $membership->id,
            'amount_paid'          => -100,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $response->assertSessionHasErrors('amount_paid');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 12. Invalid membership is rejected
    // ─────────────────────────────────────────────────────────────────────────
    public function test_invalid_membership_is_rejected(): void
    {
        $owner = $this->owner();

        $response = $this->actingAs($owner)->post('/fees/pay', [
            'member_membership_id' => 99999,
            'amount_paid'          => 100,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $response->assertSessionHasErrors('member_membership_id');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 13. Membership of another member cannot be manipulated (cross-member safety)
    //     The fee controller does NOT restrict by member; it finds by MM id and
    //     validates the member exists — so cross-manipulation is checked via
    //     MemberController::recordPayment (which uses Rule::exists with where)
    // ─────────────────────────────────────────────────────────────────────────
    public function test_cross_member_membership_manipulation_via_member_route(): void
    {
        $owner   = $this->owner();
        $member1 = $this->createMember(['phone' => '03001111111']);
        $member2 = $this->createMember(['phone' => '03002222222']);
        $plan    = $this->createPlan();
        $mm2     = $this->createMembership($member2, $plan);

        // Try to submit member2's membership via member1's route
        $response = $this->actingAs($owner)->post(route('members.record-payment', $member1), [
            'member_membership_id' => $mm2->id,
            'amount_paid'          => 100,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $response->assertSessionHasErrors('member_membership_id');
        $this->assertDatabaseEmpty('fee_payments');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 14. Payment history page is displayed
    // ─────────────────────────────────────────────────────────────────────────
    public function test_payment_history_page_loads(): void
    {
        $owner  = $this->owner();
        $member = $this->createMember();
        $plan   = $this->createPlan();
        $mm     = $this->createMembership($member, $plan);

        FeePayment::create([
            'member_id'            => $member->id,
            'member_membership_id' => $mm->id,
            'amount_paid'          => 500,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $response = $this->actingAs($owner)->get('/fees/history');
        $response->assertStatus(200);
        $response->assertSee('Payment History');
        $response->assertSee($member->name);
        $response->assertSee('500');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 15. Fee Received activity log is created
    // ─────────────────────────────────────────────────────────────────────────
    public function test_activity_log_created_on_payment(): void
    {
        $owner      = $this->owner();
        $member     = $this->createMember();
        $plan       = $this->createPlan();
        $membership = $this->createMembership($member, $plan);

        $this->actingAs($owner)->post('/fees/pay', [
            'member_membership_id' => $membership->id,
            'amount_paid'          => 500,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id'      => $owner->id,
            'action'       => 'Fee Received',
            'subject_type' => FeePayment::class,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 16. Existing Member Profile payment history still works
    // ─────────────────────────────────────────────────────────────────────────
    public function test_member_profile_payment_history_still_works(): void
    {
        $owner  = $this->owner();
        $member = $this->createMember();
        $plan   = $this->createPlan();
        $mm     = $this->createMembership($member, $plan);

        FeePayment::create([
            'member_id'            => $member->id,
            'member_membership_id' => $mm->id,
            'amount_paid'          => 1000,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $response = $this->actingAs($owner)->get(route('members.show', $member));
        $response->assertStatus(200);
        $response->assertSee('Payment History');
        $response->assertSee('1,000');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 17. Dashboard monthly revenue remains correct
    // ─────────────────────────────────────────────────────────────────────────
    public function test_dashboard_monthly_revenue_is_correct(): void
    {
        $owner  = $this->owner();
        $member = $this->createMember();
        $plan   = $this->createPlan();
        $mm     = $this->createMembership($member, $plan);

        FeePayment::create([
            'member_id'            => $member->id,
            'member_membership_id' => $mm->id,
            'amount_paid'          => 2500,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $response = $this->actingAs($owner)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('2,500');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 18. Dashboard pending fees remain correct
    // ─────────────────────────────────────────────────────────────────────────
    public function test_dashboard_pending_fees_are_correct(): void
    {
        $owner  = $this->owner();
        $member = $this->createMember();
        $plan   = $this->createPlan(['price' => 3000]);
        $this->createMembership($member, $plan, [
            'total_amount'     => 3000,
            'paid_amount'      => 1000,
            'remaining_amount' => 2000,
            'status'           => 'active',
        ]);

        $response = $this->actingAs($owner)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('2,000');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 19. Printable receipt page renders
    // ─────────────────────────────────────────────────────────────────────────
    public function test_printable_receipt_renders(): void
    {
        $owner  = $this->owner();
        $member = $this->createMember();
        $plan   = $this->createPlan();
        $mm     = $this->createMembership($member, $plan);

        $payment = FeePayment::create([
            'member_id'            => $member->id,
            'member_membership_id' => $mm->id,
            'amount_paid'          => 500,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $response = $this->actingAs($owner)->get(route('fees.receipt', $payment));
        $response->assertStatus(200);
        $response->assertSee('PAYMENT RECEIPT');
        $response->assertSee($member->name);
        $response->assertSee('500');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 20. Renewal-due memberships are identified correctly
    // ─────────────────────────────────────────────────────────────────────────
    public function test_renewal_reminders_are_shown_for_expiring_memberships(): void
    {
        $owner  = $this->owner();
        $member = $this->createMember();
        $plan   = $this->createPlan();
        $this->createMembership($member, $plan, [
            'end_date'         => now()->addDays(5)->toDateString(),
            'remaining_amount' => 0,
            'paid_amount'      => $plan->price,
            'status'           => 'active',
        ]);

        $response = $this->actingAs($owner)->get('/fees');
        $response->assertStatus(200);
        $response->assertSee('Memberships Expiring Within 7 Days');
        $response->assertSee($member->name);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 21. Multiple partial payments accumulate correctly
    // ─────────────────────────────────────────────────────────────────────────
    public function test_multiple_partial_payments_accumulate_correctly(): void
    {
        $owner      = $this->owner();
        $member     = $this->createMember();
        $plan       = $this->createPlan(['price' => 3000]);
        $membership = $this->createMembership($member, $plan, ['total_amount' => 3000, 'paid_amount' => 0, 'remaining_amount' => 3000]);

        $this->actingAs($owner)->post('/fees/pay', [
            'member_membership_id' => $membership->id,
            'amount_paid'          => 1000,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $membership->refresh();

        $this->actingAs($owner)->post('/fees/pay', [
            'member_membership_id' => $membership->id,
            'amount_paid'          => 1000,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'cash',
        ]);

        $membership->refresh();
        $this->assertEquals(2000.00, (float) $membership->paid_amount);
        $this->assertEquals(1000.00, (float) $membership->remaining_amount);
        $this->assertEquals(2, FeePayment::count());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 22. Existing Phase 5 / 6 / 8 regression check
    // ─────────────────────────────────────────────────────────────────────────
    public function test_membership_plan_index_still_loads(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner)->get('/membership-plans')->assertStatus(200);
    }

    public function test_member_index_still_loads(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner)->get('/members')->assertStatus(200);
    }

    public function test_attendance_index_still_loads(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner)->get('/attendances')->assertStatus(200);
    }
}
