<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_generates_expiry_notifications()
    {
        $plan = MembershipPlan::create(['name' => 'Test Plan', 'duration_days' => 30, 'price' => 1000, 'sort_order' => 1]);
        $member = Member::create(['name' => 'John Doe', 'phone' => '03001234567', 'gender' => 'male', 'joining_date' => today(), 'status' => 'active']);
        
        $membership = MemberMembership::create([
            'member_id' => $member->id,
            'membership_plan_id' => $plan->id,
            'start_date' => today()->subDays(25),
            'end_date' => today()->addDays(5),
            'total_amount' => 1000,
            'paid_amount' => 1000,
            'remaining_amount' => 0,
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
             ->get(route('dashboard'))
             ->assertStatus(200);

        $this->assertDatabaseHas('notifications', [
            'type' => 'membership_expiry',
            'notifiable_type' => MemberMembership::class,
            'notifiable_id' => $membership->id,
        ]);
    }

    public function test_dashboard_generates_pending_fee_notifications()
    {
        $plan = MembershipPlan::create(['name' => 'Test Plan', 'duration_days' => 30, 'price' => 1000, 'sort_order' => 1]);
        $member = Member::create(['name' => 'John Doe', 'phone' => '03001234567', 'gender' => 'male', 'joining_date' => today(), 'status' => 'active']);
        
        $membership = MemberMembership::create([
            'member_id' => $member->id,
            'membership_plan_id' => $plan->id,
            'start_date' => today(),
            'end_date' => today()->addDays(30),
            'total_amount' => 1000,
            'paid_amount' => 500,
            'remaining_amount' => 500, // Pending fee
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
             ->get(route('dashboard'))
             ->assertStatus(200);

        $this->assertDatabaseHas('notifications', [
            'type' => 'pending_fee',
            'notifiable_type' => MemberMembership::class,
            'notifiable_id' => $membership->id,
        ]);
    }

    public function test_notifications_can_be_marked_as_read()
    {
        $notification = Notification::create([
            'type' => 'membership_expiry',
            'notifiable_type' => MemberMembership::class,
            'notifiable_id' => 1,
            'title' => 'Test',
            'message' => 'Test msg',
            'is_read' => false,
        ]);

        $this->actingAs($this->user)
             ->post(route('notifications.mark-read', $notification))
             ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_all_notifications_can_be_marked_as_read()
    {
        Notification::create([
            'type' => 'membership_expiry',
            'notifiable_type' => MemberMembership::class,
            'notifiable_id' => 1,
            'title' => 'Test1',
            'message' => 'Test msg1',
            'is_read' => false,
        ]);

        Notification::create([
            'type' => 'pending_fee',
            'notifiable_type' => MemberMembership::class,
            'notifiable_id' => 2,
            'title' => 'Test2',
            'message' => 'Test msg2',
            'is_read' => false,
        ]);

        $this->actingAs($this->user)
             ->post(route('notifications.mark-all-read'))
             ->assertRedirect();

        $this->assertDatabaseMissing('notifications', [
            'is_read' => false,
        ]);
    }
}
