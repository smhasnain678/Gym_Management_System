<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Member;
use App\Models\Trainer;
use App\Models\Attendance;
use App\Models\FeePayment;
use App\Models\MembershipPlan;
use App\Models\MemberMembership;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_dashboard()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHasAll([
            'totalMembers',
            'activeMembers',
            'activeTrainers',
            'todaysAttendance',
            'monthlyRevenue',
            'pendingFees',
            'monthlyExpenses',
            'netProfit',
            'todaysNewMembers',
            'expiringMemberships',
            'recentActivities',
            'membershipStats'
        ]);
    }

    public function test_dashboard_excludes_soft_deleted_members_from_membership_stats()
    {
        $user = User::factory()->create();
        $plan = MembershipPlan::create(['name' => 'Basic', 'duration_days' => 30, 'price' => 1000]);

        // 1. Create active member with active membership
        $activeMember = Member::create([
            'name' => 'Active Member',
            'phone' => '03001234567',
            'gender' => 'male',
            'joining_date' => today(),
            'status' => 'active'
        ]);
        MemberMembership::create([
            'member_id' => $activeMember->id,
            'membership_plan_id' => $plan->id,
            'start_date' => today(),
            'end_date' => today()->addDays(30),
            'total_amount' => 1000,
            'status' => 'active'
        ]);

        // 2. Create another member, give active membership, then soft delete
        $deletedMember = Member::create([
            'name' => 'Deleted Member',
            'phone' => '03007654321',
            'gender' => 'female',
            'joining_date' => today(),
            'status' => 'active'
        ]);
        MemberMembership::create([
            'member_id' => $deletedMember->id,
            'membership_plan_id' => $plan->id,
            'start_date' => today(),
            'end_date' => today()->addDays(30),
            'total_amount' => 1000,
            'status' => 'active'
        ]);

        $deletedMember->delete(); // Soft delete

        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $membershipStats = $response->viewData('membershipStats');
        
        $basicPlanStats = $membershipStats->where('id', $plan->id)->first();
        
        // Assert only the non-deleted member's membership is counted
        $this->assertEquals(1, $basicPlanStats->member_memberships_count);
    }

}
