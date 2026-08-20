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

}
