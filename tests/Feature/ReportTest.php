<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Member;
use App\Models\Trainer;
use App\Models\MembershipPlan;
use App\Models\MemberMembership;
use App\Models\FeePayment;
use App\Models\Attendance;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user manually since factory may not exist
        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    private function createMember() {
        return Member::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '03001234567',
            'gender' => 'male',
            'joining_date' => now(),
            'status' => 'active',
        ]);
    }

    public function test_guest_cannot_access_reports()
    {
        $response = $this->get(route('reports.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_owner_can_access_reports_index()
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
    }

    public function test_revenue_report_calculates_correctly()
    {
        $member = $this->createMember();
        $plan = MembershipPlan::create(['name' => 'Basic Plan', 'duration_days' => 30, 'price' => 1000, 'is_active' => true]);
        $membership = MemberMembership::create(['member_id' => $member->id, 'membership_plan_id' => $plan->id, 'start_date' => now(), 'end_date' => now()->addMonth(), 'total_amount' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000, 'status' => 'active']);
        
        FeePayment::create([
            'member_id' => $member->id,
            'member_membership_id' => $membership->id,
            'amount_paid' => 1500,
            'payment_date' => now(),
            'payment_method' => 'cash'
        ]);
        FeePayment::create([
            'member_id' => $member->id,
            'member_membership_id' => $membership->id,
            'amount_paid' => 500,
            'payment_date' => now(),
            'payment_method' => 'card'
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.revenue'));
        $response->assertStatus(200);
        $response->assertViewHas('totalRevenue', 2000.00);
        $response->assertViewHas('paymentsCount', 2);
    }

    public function test_revenue_filters_work()
    {
        $member = $this->createMember();
        $plan = MembershipPlan::create(['name' => 'Basic Plan', 'duration_days' => 30, 'price' => 1000, 'is_active' => true]);
        $membership = MemberMembership::create(['member_id' => $member->id, 'membership_plan_id' => $plan->id, 'start_date' => now(), 'end_date' => now()->addMonth(), 'total_amount' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000, 'status' => 'active']);
        
        FeePayment::create([
            'member_id' => $member->id,
            'member_membership_id' => $membership->id,
            'amount_paid' => 1000,
            'payment_date' => now()->subMonth(),
            'payment_method' => 'cash'
        ]);
        FeePayment::create([
            'member_id' => $member->id,
            'member_membership_id' => $membership->id,
            'amount_paid' => 500,
            'payment_date' => now(),
            'payment_method' => 'card'
        ]);

        $filterDate = now()->subMonth()->format('Y-m-d');
        $response = $this->actingAs($this->user)->get(route('reports.revenue', [
            'start_date' => $filterDate,
            'end_date' => $filterDate,
        ]));
        
        $response->assertStatus(200);
        $response->assertViewHas('totalRevenue', 1000.00);
    }

    public function test_attendance_report_calculates_correctly()
    {
        $member = $this->createMember();
        Attendance::create(['member_id' => $member->id, 'date' => now(), 'status' => 'present']);
        Attendance::create(['member_id' => $member->id, 'date' => now()->subDay(), 'status' => 'absent']);

        $response = $this->actingAs($this->user)->get(route('reports.attendance'));
        $response->assertStatus(200);
        $response->assertViewHas('totalAttendance', 2);
        $response->assertViewHas('presentCount', 1);
        $response->assertViewHas('absentCount', 1);
        $response->assertViewHas('attendanceRate', 50.0);
    }

    public function test_attendance_filters_work()
    {
        $member1 = $this->createMember();
        $member2 = Member::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '03007654321',
            'gender' => 'female',
            'joining_date' => now(),
            'status' => 'active',
        ]);
        Attendance::create(['member_id' => $member1->id, 'date' => now(), 'status' => 'present']);
        Attendance::create(['member_id' => $member2->id, 'date' => now(), 'status' => 'absent']);

        $response = $this->actingAs($this->user)->get(route('reports.attendance', ['member_id' => $member1->id]));
        $response->assertStatus(200);
        $response->assertViewHas('totalAttendance', 1);
        $response->assertViewHas('presentCount', 1);
    }

    public function test_member_report_renders_correct_members()
    {
        $this->createMember();
        Member::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '03007654321',
            'gender' => 'female',
            'joining_date' => now(),
            'status' => 'suspended',
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.members'));
        $response->assertStatus(200);
        $response->assertViewHas('totalMembers', 2);
        $response->assertViewHas('activeMembers', 1);
    }

    public function test_membership_report_renders_correct_memberships()
    {
        $plan = MembershipPlan::create([
            'name' => 'Basic Plan',
            'duration_days' => 30,
            'price' => 1000,
            'is_active' => true
        ]);
        $member = $this->createMember();
        MemberMembership::create([
            'member_id' => $member->id,
            'membership_plan_id' => $plan->id,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'total_amount' => 1000,
            'paid_amount' => 1000,
            'remaining_amount' => 0,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.memberships'));
        $response->assertStatus(200);
        $response->assertViewHas('totalMemberships', 1);
        $response->assertViewHas('totalAmount', 1000);
    }

    public function test_trainer_report_renders_correct_trainers()
    {
        Trainer::create([
            'name' => 'Trainer 1',
            'phone' => '03001111111',
            'gender' => 'male',
            'specialization' => 'Yoga',
            'joining_date' => now(),
            'salary' => 5000,
            'is_active' => true
        ]);
        $response = $this->actingAs($this->user)->get(route('reports.trainers'));
        $response->assertStatus(200);
        $response->assertViewHas('totalTrainers', 1);
    }

    public function test_fee_collection_report_calculates_correctly()
    {
        $member = $this->createMember();
        $plan = MembershipPlan::create(['name' => 'Basic Plan', 'duration_days' => 30, 'price' => 1000, 'is_active' => true]);
        $membership = MemberMembership::create(['member_id' => $member->id, 'membership_plan_id' => $plan->id, 'start_date' => now(), 'end_date' => now()->addMonth(), 'total_amount' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000, 'status' => 'active']);
        
        FeePayment::create([
            'member_id' => $member->id,
            'member_membership_id' => $membership->id,
            'amount_paid' => 300,
            'payment_date' => now(),
            'payment_method' => 'bank_transfer'
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.fees'));
        $response->assertStatus(200);
        $response->assertViewHas('totalCollected', 300);
        $response->assertViewHas('paymentsCount', 1);
    }

    public function test_expense_report_calculates_correctly_and_category_totals()
    {
        $category1 = ExpenseCategory::create(['name' => 'Utilities', 'is_active' => true]);
        $category2 = ExpenseCategory::create(['name' => 'Rent', 'is_active' => true]);

        Expense::create(['expense_category_id' => $category1->id, 'title' => 'Electric', 'amount' => 200, 'expense_date' => now()]);
        Expense::create(['expense_category_id' => $category2->id, 'title' => 'Building', 'amount' => 1000, 'expense_date' => now()]);

        $response = $this->actingAs($this->user)->get(route('reports.expenses'));
        $response->assertStatus(200);
        $response->assertViewHas('totalExpenses', 1200);
        $response->assertViewHas('expenseCount', 2);
        
        $viewTotals = collect($response->original->getData()['categoryTotals']);
        $this->assertEquals(200, $viewTotals->firstWhere('category_name', 'Utilities')->total);
        $this->assertEquals(1000, $viewTotals->firstWhere('category_name', 'Rent')->total);
    }

    public function test_pdf_export_endpoints_respond_correctly()
    {
        $member = $this->createMember();
        $plan = MembershipPlan::create(['name' => 'Basic Plan', 'duration_days' => 30, 'price' => 1000, 'is_active' => true]);
        $membership = MemberMembership::create(['member_id' => $member->id, 'membership_plan_id' => $plan->id, 'start_date' => now(), 'end_date' => now()->addMonth(), 'total_amount' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000, 'status' => 'active']);
        FeePayment::create(['member_id' => $member->id, 'member_membership_id' => $membership->id, 'amount_paid' => 100, 'payment_date' => now(), 'payment_method' => 'cash']);
        
        $response = $this->actingAs($this->user)->get(route('reports.revenue', ['export' => 'pdf']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_excel_export_endpoints_respond_correctly()
    {
        $member = $this->createMember();
        $plan = MembershipPlan::create(['name' => 'Basic Plan', 'duration_days' => 30, 'price' => 1000, 'is_active' => true]);
        $membership = MemberMembership::create(['member_id' => $member->id, 'membership_plan_id' => $plan->id, 'start_date' => now(), 'end_date' => now()->addMonth(), 'total_amount' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000, 'status' => 'active']);
        FeePayment::create(['member_id' => $member->id, 'member_membership_id' => $membership->id, 'amount_paid' => 100, 'payment_date' => now(), 'payment_method' => 'cash']);
        
        $response = $this->actingAs($this->user)->get(route('reports.revenue', ['export' => 'excel']));
        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('content-type'));
    }

    public function test_print_views_render_correctly()
    {
        $member = $this->createMember();
        $plan = MembershipPlan::create(['name' => 'Basic Plan', 'duration_days' => 30, 'price' => 1000, 'is_active' => true]);
        $membership = MemberMembership::create(['member_id' => $member->id, 'membership_plan_id' => $plan->id, 'start_date' => now(), 'end_date' => now()->addMonth(), 'total_amount' => 1000, 'paid_amount' => 0, 'remaining_amount' => 1000, 'status' => 'active']);
        FeePayment::create(['member_id' => $member->id, 'member_membership_id' => $membership->id, 'amount_paid' => 100, 'payment_date' => now(), 'payment_method' => 'cash']);
        
        $response = $this->actingAs($this->user)->get(route('reports.revenue', ['print' => true]));
        $response->assertStatus(200);
        $response->assertViewIs('reports.prints.revenue');
    }
}
