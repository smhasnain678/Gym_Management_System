<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FeePayment;
use App\Models\GymSetting;
use App\Models\Member;
use App\Models\MemberMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FeeController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Fee Management dashboard
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // ── Summary statistics ───────────────────────────────────────────────
        $totalExpected = MemberMembership::sum('total_amount');
        $totalPaid     = MemberMembership::sum('paid_amount');
        $totalPending  = MemberMembership::whereIn('status', ['active', 'expiring_soon'])
                                         ->sum('remaining_amount');

        $thisMonthRevenue = FeePayment::whereMonth('payment_date', now()->month)
                                      ->whereYear('payment_date', now()->year)
                                      ->sum('amount_paid');

        // ── Pending fee list ─────────────────────────────────────────────────
        $pendingQuery = MemberMembership::with(['member', 'membershipPlan'])
            ->whereIn('status', ['active', 'expiring_soon'])
            ->where('remaining_amount', '>', 0);

        if ($request->filled('search')) {
            $search = $request->search;
            $pendingQuery->whereHas('member', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sort: overdue first (end_date < today), then by remaining_amount desc
        $today = now()->toDateString();
        $pendingFees = $pendingQuery
            ->orderByRaw("CASE WHEN end_date < ? THEN 0 ELSE 1 END", [$today])
            ->orderByDesc('remaining_amount')
            ->paginate(15)
            ->withQueryString();

        // ── Renewal reminders (expiring in next 7 days) ──────────────────────
        $renewalReminders = MemberMembership::with(['member', 'membershipPlan'])
            ->whereIn('status', ['active', 'expiring_soon'])
            ->whereBetween('end_date', [today(), today()->addDays(7)])
            ->orderBy('end_date')
            ->get();

        // ── Recent payments ──────────────────────────────────────────────────
        $recentPayments = FeePayment::with(['member', 'memberMembership.membershipPlan'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        return view('fees.index', compact(
            'totalExpected',
            'totalPaid',
            'totalPending',
            'thisMonthRevenue',
            'pendingFees',
            'renewalReminders',
            'recentPayments'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Full Payment History page
    // ─────────────────────────────────────────────────────────────────────────

    public function history(Request $request)
    {
        $query = FeePayment::with(['member', 'memberMembership.membershipPlan']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('method') && $request->method !== 'all') {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('month')) {
            $query->whereRaw("DATE_FORMAT(payment_date, '%Y-%m') = ?", [$request->month]);
        }

        $payments = $query->orderByDesc('payment_date')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('fees.history', compact('payments'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Record a payment (from Fee Management page)
    // ─────────────────────────────────────────────────────────────────────────

    public function pay(Request $request)
    {
        $validated = $request->validate([
            'member_membership_id' => [
                'required',
                'integer',
                'exists:member_memberships,id',
            ],
            'amount_paid'    => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'easypaisa', 'jazzcash', 'card'])],
            'notes'          => 'nullable|string|max:500',
        ]);

        $membership = MemberMembership::with('member')->findOrFail($validated['member_membership_id']);

        // Security: ensure membership belongs to a real (non-deleted) member
        if (! $membership->member) {
            return back()->with('error', __('Invalid membership - member not found.'));
        }

        // Overpayment guard
        if ($validated['amount_paid'] > $membership->remaining_amount) {
            return back()
                ->withInput()
                ->withErrors(['amount_paid' => 'Payment of ' . number_format($validated['amount_paid'], 2) . ' exceeds the remaining balance of ' . number_format($membership->remaining_amount, 2) . '.']);
        }

        DB::transaction(function () use ($validated, $membership) {
            // Create FeePayment record
            $payment = FeePayment::create([
                'member_id'            => $membership->member_id,
                'member_membership_id' => $membership->id,
                'amount_paid'          => $validated['amount_paid'],
                'payment_date'         => $validated['payment_date'],
                'payment_method'       => $validated['payment_method'],
                'notes'                => $validated['notes'] ?? null,
            ]);

            // Recalculate totals from all payments (source of truth)
            $totalPaid = $membership->feePayments()->sum('amount_paid');
            $membership->update([
                'paid_amount'      => $totalPaid,
                'remaining_amount' => max(0, $membership->total_amount - $totalPaid),
            ]);

            // Activity Log
            ActivityLog::create([
                'user_id'      => auth()->id(),
                'action'       => 'Fee Received',
                'description'  => 'Payment of ' . number_format($validated['amount_paid'], 2) . ' received from ' . $membership->member->name . ' for membership #' . $membership->id . '.',
                'subject_type' => FeePayment::class,
                'subject_id'   => $payment->id,
                'ip_address'   => request()->ip(),
                'user_agent'   => request()->userAgent(),
            ]);
        });

        return back()->with('success', __('Payment of :amount recorded successfully for :name.', ['amount' => number_format($validated['amount_paid'], 2), 'name' => $membership->member->name]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Printable receipt for a specific FeePayment
    // ─────────────────────────────────────────────────────────────────────────

    public function receipt(FeePayment $payment)
    {
        $payment->load(['member', 'memberMembership.membershipPlan']);
        $gymSettings = GymSetting::first();

        return view('fees.receipt', compact('payment', 'gymSettings'));
    }
}
