<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Expense;
use App\Models\FeePayment;
use App\Models\Member;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use App\Models\Trainer;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard with all metrics.
     */
    public function index(NotificationService $notificationService)
    {
        // Sync notifications for Phase 12
        $notificationService->syncDashboardNotifications();

        // 1. Total Members
        $totalMembers = Member::count();

        // 2. Active Members
        $activeMembers = Member::where('status', 'active')->count();

        // 3. Trainers Count
        $activeTrainers = Trainer::where('is_active', true)->count();

        // 4. Today's Attendance
        $todaysAttendance = Attendance::whereDate('date', today())->where('status', 'present')->count();

        // 5. Monthly Revenue (Current Month)
        $monthlyRevenue = FeePayment::whereMonth('payment_date', now()->month)
                                    ->whereYear('payment_date', now()->year)
                                    ->sum('amount_paid');

        // 6. Pending Fees
        $pendingFees = MemberMembership::whereIn('status', ['active', 'expiring_soon'])
                                       ->sum('remaining_amount');

        // 7. Monthly Expenses (Current Month)
        $monthlyExpenses = Expense::whereMonth('expense_date', now()->month)
                                  ->whereYear('expense_date', now()->year)
                                  ->sum('amount');

        // 8. Net Profit
        $netProfit = $monthlyRevenue - $monthlyExpenses;

        // 9. Today's New Members
        $todaysNewMembers = Member::whereDate('joining_date', today())->count();

        // 10. Memberships Expiring Soon (Next 7 Days)
        $expiringMemberships = MemberMembership::with('member')
            ->whereIn('status', ['active', 'expiring_soon'])
            ->whereBetween('end_date', [today(), today()->addDays(7)])
            ->get();

        // 11. Recent Activities (Latest 5)
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(5)
            ->get();

        // 12. Membership Statistics (Active memberships by plan)
        $membershipStats = MembershipPlan::withCount(['memberMemberships' => function ($query) {
            $query->where('status', 'active');
        }])->get();

        return view('dashboard', compact(
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
        ));
    }
}
