<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeePayment;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use App\Models\Trainer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RevenueExport;
use App\Exports\AttendanceExport;
use App\Exports\MemberExport;
use App\Exports\MembershipExport;
use App\Exports\TrainerExport;
use App\Exports\FeeCollectionExport;
use App\Exports\ExpenseExport;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function revenue(Request $request)
    {
        $query = FeePayment::with(['member', 'memberMembership.membershipPlan']);

        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }
        if ($request->filled('month')) {
            $date = Carbon::parse($request->month);
            $query->whereYear('payment_date', $date->year)
                  ->whereMonth('payment_date', $date->month);
        }

        $totalRevenue = (clone $query)->sum('amount_paid');
        $paymentsCount = (clone $query)->count();

        if ($request->has('export')) {
            $payments = $query->latest('payment_date')->get();
            if ($request->export === 'pdf') {
                $pdf = Pdf::loadView('reports.exports.revenue_pdf', compact('payments', 'totalRevenue', 'paymentsCount', 'request'));
                return $pdf->download('revenue_report.pdf');
            } elseif ($request->export === 'excel') {
                return Excel::download(new RevenueExport($payments), 'revenue_report.xlsx');
            }
        }
        
        if ($request->has('print')) {
            $payments = $query->latest('payment_date')->get();
            return view('reports.prints.revenue', compact('payments', 'totalRevenue', 'paymentsCount', 'request'));
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();

        return view('reports.revenue', compact('payments', 'totalRevenue', 'paymentsCount'));
    }

    public function attendance(Request $request)
    {
        $query = Attendance::with(['member']);

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }
        if ($request->filled('month')) {
            $date = Carbon::parse($request->month);
            $query->whereYear('date', $date->year)
                  ->whereMonth('date', $date->month);
        }
        if ($request->filled('status')) {
            $query->where('status', strtolower($request->status));
        }
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        $totalAttendance = (clone $query)->count();
        $presentCount = (clone $query)->where('status', 'present')->count();
        $absentCount = (clone $query)->where('status', 'absent')->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 2) : 0;

        if ($request->has('export')) {
            $attendances = $query->latest('date')->get();
            if ($request->export === 'pdf') {
                $pdf = Pdf::loadView('reports.exports.attendance_pdf', compact('attendances', 'totalAttendance', 'presentCount', 'absentCount', 'attendanceRate', 'request'));
                return $pdf->download('attendance_report.pdf');
            } elseif ($request->export === 'excel') {
                return Excel::download(new AttendanceExport($attendances), 'attendance_report.xlsx');
            }
        }
        
        if ($request->has('print')) {
            $attendances = $query->latest('date')->get();
            return view('reports.prints.attendance', compact('attendances', 'totalAttendance', 'presentCount', 'absentCount', 'attendanceRate', 'request'));
        }

        $attendances = $query->latest('date')->paginate(15)->withQueryString();
        $members = Member::orderBy('name')->get();

        return view('reports.attendance', compact('attendances', 'totalAttendance', 'presentCount', 'absentCount', 'attendanceRate', 'members'));
    }

    public function members(Request $request)
    {
        $query = Member::with(['trainer']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', strtolower($request->status));
        }
        if ($request->filled('trainer_id')) {
            $query->where('trainer_id', $request->trainer_id);
        }
        if ($request->filled('plan_id')) {
            $query->whereHas('memberships', function($q) use ($request) {
                $q->where('membership_plan_id', $request->plan_id)
                  ->where('status', 'active');
            });
        }

        $totalMembers = (clone $query)->count();
        $activeMembers = (clone $query)->where('status', 'active')->count();

        if ($request->has('export')) {
            $membersList = $query->latest('created_at')->get();
            if ($request->export === 'pdf') {
                $pdf = Pdf::loadView('reports.exports.members_pdf', compact('membersList', 'totalMembers', 'activeMembers', 'request'));
                return $pdf->download('members_report.pdf');
            } elseif ($request->export === 'excel') {
                return Excel::download(new MemberExport($membersList), 'members_report.xlsx');
            }
        }

        $membersList = $query->latest('created_at')->paginate(15)->withQueryString();
        $trainers = Trainer::orderBy('name')->get();
        $plans = MembershipPlan::orderBy('name')->get();

        return view('reports.members', compact('membersList', 'totalMembers', 'activeMembers', 'trainers', 'plans'));
    }

    public function memberships(Request $request)
    {
        $query = MemberMembership::with(['member', 'membershipPlan']);

        if ($request->filled('plan_id')) {
            $query->where('membership_plan_id', $request->plan_id);
        }
        if ($request->filled('status')) {
            $query->where('status', strtolower($request->status));
        }
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $totalMemberships = (clone $query)->count();
        $totalAmount = (clone $query)->sum('total_amount');
        $paidAmount = (clone $query)->sum('paid_amount');
        $remainingAmount = (clone $query)->sum('remaining_amount');

        if ($request->has('export')) {
            $memberships = $query->latest('created_at')->get();
            if ($request->export === 'pdf') {
                $pdf = Pdf::loadView('reports.exports.memberships_pdf', compact('memberships', 'totalMemberships', 'totalAmount', 'paidAmount', 'remainingAmount', 'request'));
                return $pdf->download('memberships_report.pdf');
            } elseif ($request->export === 'excel') {
                return Excel::download(new MembershipExport($memberships), 'memberships_report.xlsx');
            }
        }

        $memberships = $query->latest('created_at')->paginate(15)->withQueryString();
        $plans = MembershipPlan::orderBy('name')->get();

        return view('reports.memberships', compact('memberships', 'totalMemberships', 'totalAmount', 'paidAmount', 'remainingAmount', 'plans'));
    }

    public function trainers(Request $request)
    {
        $query = Trainer::withCount('members');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $isActive = $request->status === 'Active';
            $query->where('is_active', $isActive);
        }

        $totalTrainers = (clone $query)->count();

        if ($request->has('export')) {
            $trainersList = $query->latest('created_at')->get();
            if ($request->export === 'pdf') {
                $pdf = Pdf::loadView('reports.exports.trainers_pdf', compact('trainersList', 'totalTrainers', 'request'));
                return $pdf->download('trainers_report.pdf');
            } elseif ($request->export === 'excel') {
                return Excel::download(new TrainerExport($trainersList), 'trainers_report.xlsx');
            }
        }

        $trainersList = $query->latest('created_at')->paginate(15)->withQueryString();

        return view('reports.trainers', compact('trainersList', 'totalTrainers'));
    }

    public function fees(Request $request)
    {
        $query = FeePayment::with(['member', 'memberMembership.membershipPlan']);

        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }
        if ($request->filled('month')) {
            $date = Carbon::parse($request->month);
            $query->whereYear('payment_date', $date->year)
                  ->whereMonth('payment_date', $date->month);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $totalCollected = (clone $query)->sum('amount_paid');
        $paymentsCount = (clone $query)->count();

        if ($request->has('export')) {
            $payments = $query->latest('payment_date')->get();
            if ($request->export === 'pdf') {
                $pdf = Pdf::loadView('reports.exports.fees_pdf', compact('payments', 'totalCollected', 'paymentsCount', 'request'));
                return $pdf->download('fee_collection_report.pdf');
            } elseif ($request->export === 'excel') {
                return Excel::download(new FeeCollectionExport($payments), 'fee_collection_report.xlsx');
            }
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();

        return view('reports.fees', compact('payments', 'totalCollected', 'paymentsCount'));
    }

    public function expenses(Request $request)
    {
        $query = Expense::with(['expenseCategory']);

        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }
        if ($request->filled('month')) {
            $date = Carbon::parse($request->month);
            $query->whereYear('expense_date', $date->year)
                  ->whereMonth('expense_date', $date->month);
        }
        if ($request->filled('expense_category_id')) {
            $query->where('expense_category_id', $request->expense_category_id);
        }

        $totalExpenses = (clone $query)->sum('amount');
        $expenseCount = (clone $query)->count();
        
        $categoryTotals = (clone $query)->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name as category_name, SUM(expenses.amount) as total')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->get();

        if ($request->has('export')) {
            $expensesList = $query->latest('expense_date')->get();
            if ($request->export === 'pdf') {
                $pdf = Pdf::loadView('reports.exports.expenses_pdf', compact('expensesList', 'totalExpenses', 'expenseCount', 'categoryTotals', 'request'));
                return $pdf->download('expenses_report.pdf');
            } elseif ($request->export === 'excel') {
                return Excel::download(new ExpenseExport($expensesList, $categoryTotals), 'expenses_report.xlsx');
            }
        }

        $expensesList = $query->latest('expense_date')->paginate(15)->withQueryString();
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('reports.expenses', compact('expensesList', 'totalExpenses', 'expenseCount', 'categoryTotals', 'categories'));
    }

    public function printMember(Member $member)
    {
        $member->load(['trainer', 'memberships.membershipPlan']);

        return view('reports.prints.member_details', compact('member'));
    }
}
