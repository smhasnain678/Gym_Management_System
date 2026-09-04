<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FeePayment;
use App\Models\Member;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Index — list all members with search / filter
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Member::with(['memberships' => function ($q) {
            $q->with('membershipPlan')
              ->orderByDesc('start_date')
              ->limit(1);
        }]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name',  'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Gender filter
        if ($request->filled('gender') && $request->gender !== 'all') {
            $query->where('gender', $request->gender);
        }

        $members = $query->orderBy('name')->paginate(15)->withQueryString();

        $totalMembers  = Member::count();
        $activeMembers = Member::where('status', 'active')->count();

        return view('members.index', compact('members', 'totalMembers', 'activeMembers'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create form
    // ─────────────────────────────────────────────────────────────────────────

    public function create()
    {
        $plans = MembershipPlan::where('is_active', true)
                               ->orderBy('sort_order')
                               ->orderBy('name')
                               ->get();

        return view('members.create', compact('plans'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Store new member (+ optional immediate membership assignment)
    // ─────────────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:100',
            'email'                   => 'nullable|email|max:150|unique:members,email',
            'phone'                   => 'required|string|max:20',
            'date_of_birth'           => 'nullable|date|before:today',
            'gender'                  => ['required', Rule::in(['male', 'female', 'other'])],
            'address'                 => 'nullable|string|max:500',
            'emergency_contact_name'  => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'medical_notes'           => 'nullable|string|max:1000',
            'height'                  => 'nullable|numeric|min:50|max:300',
            'weight'                  => 'nullable|numeric|min:10|max:500',
            'blood_group'             => 'nullable|string|max:10',
            'joining_date'            => 'required|date',
            'status'                  => ['required', Rule::in(['active', 'expired', 'expiring_soon', 'suspended'])],
            'profile_photo'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            // Optional: assign membership immediately
            'membership_plan_id'      => 'nullable|exists:membership_plans,id',
            'membership_start_date'   => 'nullable|required_with:membership_plan_id|date',
            'paid_amount'             => 'nullable|numeric|min:0',
            'membership_notes'        => 'nullable|string|max:500',
        ], [
            'email.unique'            => 'A member with this email already exists.',
            'date_of_birth.before'    => 'Date of birth must be in the past.',
            'membership_start_date.required_with' => 'Start date is required when assigning a plan.',
        ]);

        $photoPath = null;
        if ($request->hasFile('profile_photo')) {
            $photoPath = $request->file('profile_photo')->store('members', 'public');
        }

        $member = Member::create([
            'name'                    => $validated['name'],
            'email'                   => $validated['email'] ?? null,
            'phone'                   => $validated['phone'],
            'date_of_birth'           => $validated['date_of_birth'] ?? null,
            'gender'                  => $validated['gender'],
            'address'                 => $validated['address'] ?? null,
            'emergency_contact_name'  => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'medical_notes'           => $validated['medical_notes'] ?? null,
            'height'                  => $validated['height'] ?? null,
            'weight'                  => $validated['weight'] ?? null,
            'blood_group'             => $validated['blood_group'] ?? null,
            'profile_photo'           => $photoPath,
            'joining_date'            => $validated['joining_date'],
            'status'                  => $validated['status'],
        ]);

        // Assign membership if plan selected
        if (!empty($validated['membership_plan_id'])) {
            $plan      = MembershipPlan::findOrFail($validated['membership_plan_id']);
            $startDate = Carbon::parse($validated['membership_start_date']);
            $endDate   = $startDate->copy()->addDays($plan->duration_days);
            $paid      = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.0;
            $total     = (float) $plan->price;

            MemberMembership::create([
                'member_id'          => $member->id,
                'membership_plan_id' => $plan->id,
                'start_date'         => $startDate->toDateString(),
                'end_date'           => $endDate->toDateString(),
                'total_amount'       => $total,
                'paid_amount'        => $paid,
                'remaining_amount'   => max(0, $total - $paid),
                'status'             => 'active',
                'notes'              => $validated['membership_notes'] ?? null,
            ]);

            // Record initial payment if paid
            if ($paid > 0) {
                FeePayment::create([
                    'member_id'            => $member->id,
                    'member_membership_id' => $member->memberships()->latest()->first()->id,
                    'amount_paid'          => $paid,
                    'payment_date'         => $startDate->toDateString(),
                    'payment_method'       => 'cash',
                    'notes'                => 'Initial payment on registration.',
                ]);
            }
        }

        // Log Activity
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Member Added',
            'description'  => "Added new member {$member->name}.",
            'subject_type' => Member::class,
            'subject_id'   => $member->id,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return redirect()
            ->route('members.show', $member)
            ->with('success', __('Member ":name" registered successfully.', ['name' => $member->name]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Show member details
    // ─────────────────────────────────────────────────────────────────────────

    public function show(Member $member)
    {
        $member->load([
            'memberships.membershipPlan',
            'memberships.feePayments',
            'attendances' => fn ($q) => $q->orderByDesc('date')->limit(10),
        ]);

        $activeMembership = $member->memberships
            ->whereIn('status', ['active', 'expiring_soon'])
            ->sortByDesc('start_date')
            ->first();

        $activePlans = MembershipPlan::where('is_active', true)
                                     ->orderBy('sort_order')
                                     ->get();

        return view('members.show', compact('member', 'activeMembership', 'activePlans'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Edit form
    // ─────────────────────────────────────────────────────────────────────────

    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Update member
    // ─────────────────────────────────────────────────────────────────────────

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:100',
            'email'                   => ['nullable', 'email', 'max:150', Rule::unique('members', 'email')->ignore($member->id)],
            'phone'                   => 'required|string|max:20',
            'date_of_birth'           => 'nullable|date|before:today',
            'gender'                  => ['required', Rule::in(['male', 'female', 'other'])],
            'address'                 => 'nullable|string|max:500',
            'emergency_contact_name'  => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'medical_notes'           => 'nullable|string|max:1000',
            'height'                  => 'nullable|numeric|min:50|max:300',
            'weight'                  => 'nullable|numeric|min:10|max:500',
            'blood_group'             => 'nullable|string|max:10',
            'joining_date'            => 'required|date',
            'status'                  => ['required', Rule::in(['active', 'expired', 'expiring_soon', 'suspended'])],
            'profile_photo'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'email.unique'         => 'A member with this email already exists.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($member->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($member->profile_photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($member->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('members', 'public');
        }

        $member->update($validated);

        // Log Activity
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Member Updated',
            'description'  => "Updated member {$member->name}.",
            'subject_type' => Member::class,
            'subject_id'   => $member->id,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return redirect()
            ->route('members.show', $member)
            ->with('success', __('Member ":name" updated successfully.', ['name' => $member->name]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Destroy member — safe deletion (soft-delete is on the model)
    // ─────────────────────────────────────────────────────────────────────────

    public function destroy(Member $member)
    {
        // fee_payments cascade from member_memberships cascade from member,
        // so soft-deleting the member is safe — historical data is preserved.
        $name = $member->name;
        $id = $member->id;
        $member->delete(); // triggers SoftDeletes (sets deleted_at)

        // Log Activity
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Member Deleted',
            'description'  => "Deleted member {$name}.",
            'subject_type' => Member::class,
            'subject_id'   => $id,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return redirect()
            ->route('members.index')
            ->with('success', __('Member ":name" has been removed.', ['name' => $name]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Assign / create a new membership for a member
    // ─────────────────────────────────────────────────────────────────────────

    public function assignMembership(Request $request, Member $member)
    {
        $validated = $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'start_date'         => 'required|date',
            'paid_amount'        => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string|max:500',
        ]);

        $plan      = MembershipPlan::findOrFail($validated['membership_plan_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate   = $startDate->copy()->addDays($plan->duration_days);
        $paid      = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.0;
        $total     = (float) $plan->price;

        $membership = MemberMembership::create([
            'member_id'          => $member->id,
            'membership_plan_id' => $plan->id,
            'start_date'         => $startDate->toDateString(),
            'end_date'           => $endDate->toDateString(),
            'total_amount'       => $total,
            'paid_amount'        => $paid,
            'remaining_amount'   => max(0, $total - $paid),
            'status'             => 'active',
            'notes'              => $validated['notes'] ?? null,
        ]);

        // Record initial payment if paid > 0
        if ($paid > 0) {
            FeePayment::create([
                'member_id'            => $member->id,
                'member_membership_id' => $membership->id,
                'amount_paid'          => $paid,
                'payment_date'         => $startDate->toDateString(),
                'payment_method'       => 'cash',
                'notes'                => 'Payment on plan assignment.',
            ]);
        }

        // Update member status to active if currently not active
        if ($member->status !== 'active') {
            $member->update(['status' => 'active']);
        }

        return redirect()
            ->route('members.show', $member)
            ->with('success', __('Membership plan ":name" assigned successfully.', ['name' => $plan->name]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Renew membership — creates a new membership record starting from
    // the day after the current membership ends (or today if already expired)
    // ─────────────────────────────────────────────────────────────────────────

    public function renewMembership(Request $request, Member $member)
    {
        $validated = $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'start_date'         => 'required|date',
            'paid_amount'        => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string|max:500',
        ]);

        // Find the old active membership and mark it as expired/renewed
        $oldMembership = $member->memberships()
                                ->whereIn('status', ['active', 'expiring_soon'])
                                ->orderByDesc('start_date')
                                ->first();
        if ($oldMembership) {
            $oldMembership->update([
                'status'     => 'expired',
                'renewed_at' => now(),
            ]);
        }

        $plan      = MembershipPlan::findOrFail($validated['membership_plan_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate   = $startDate->copy()->addDays($plan->duration_days);
        $paid      = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.0;
        $total     = (float) $plan->price;

        $membership = MemberMembership::create([
            'member_id'          => $member->id,
            'membership_plan_id' => $plan->id,
            'start_date'         => $startDate->toDateString(),
            'end_date'           => $endDate->toDateString(),
            'total_amount'       => $total,
            'paid_amount'        => $paid,
            'remaining_amount'   => max(0, $total - $paid),
            'status'             => 'active',
            'notes'              => $validated['notes'] ?? null,
            'renewed_at'         => now(),
        ]);

        if ($paid > 0) {
            FeePayment::create([
                'member_id'            => $member->id,
                'member_membership_id' => $membership->id,
                'amount_paid'          => $paid,
                'payment_date'         => $startDate->toDateString(),
                'payment_method'       => 'cash',
                'notes'                => 'Payment on renewal.',
            ]);
        }

        // Keep member status active
        $member->update(['status' => 'active']);

        return redirect()
            ->route('members.show', $member)
            ->with('success', __('Membership renewed with plan ":name".', ['name' => $plan->name]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Record a fee payment against an existing membership
    // ─────────────────────────────────────────────────────────────────────────

    public function recordPayment(Request $request, Member $member)
    {
        $validated = $request->validate([
            'member_membership_id' => [
                'required',
                'exists:member_memberships,id',
                Rule::exists('member_memberships', 'id')->where('member_id', $member->id),
            ],
            'amount_paid'    => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'easypaisa', 'jazzcash', 'card'])],
            'notes'          => 'nullable|string|max:500',
        ]);

        $membership = MemberMembership::findOrFail($validated['member_membership_id']);

        if ($validated['amount_paid'] > $membership->remaining_amount) {
            return back()->withErrors(['amount_paid' => 'Payment cannot exceed the remaining balance.']);
        }

        FeePayment::create([
            'member_id'            => $member->id,
            'member_membership_id' => $membership->id,
            'amount_paid'          => $validated['amount_paid'],
            'payment_date'         => $validated['payment_date'],
            'payment_method'       => $validated['payment_method'],
            'notes'                => $validated['notes'] ?? null,
        ]);

        // Update paid/remaining on the membership
        $totalPaid = $membership->feePayments()->sum('amount_paid');
        $membership->update([
            'paid_amount'      => $totalPaid,
            'remaining_amount' => max(0, $membership->total_amount - $totalPaid),
        ]);

        // Activity Log
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Fee Received',
            'description'  => 'Payment of ' . number_format($validated['amount_paid'], 2) . ' received from ' . $member->name . ' for membership #' . $membership->id . '.',
            'subject_type' => FeePayment::class,
            'subject_id'   => $membership->feePayments()->latest()->value('id'),
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return redirect()
            ->route('members.show', $member)
            ->with('success', __('Payment of :amount recorded successfully.', ['amount' => number_format($validated['amount_paid'], 2)]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Toggle member active/suspended status
    // ─────────────────────────────────────────────────────────────────────────

    public function toggleStatus(Member $member)
    {
        $newStatus = $member->status === 'active' ? 'suspended' : 'active';
        $member->update(['status' => $newStatus]);

        $label = ucfirst($newStatus);

        return redirect()
            ->route('members.show', $member)
            ->with('success', __('Member ":name" status changed to :status.', ['name' => $member->name, 'status' => $label]));
    }
}
