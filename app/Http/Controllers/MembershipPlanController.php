<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    /**
     * Display a listing of membership plans.
     */
    public function index(Request $request)
    {
        $query = MembershipPlan::withCount('memberMemberships');

        // Search / filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $plans = $query->orderBy('sort_order')->orderBy('name')->get();

        return view('membership-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new membership plan.
     */
    public function create()
    {
        return view('membership-plans.create');
    }

    /**
     * Store a newly created membership plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100|unique:membership_plans,name',
            'duration_days'=> 'required|integer|min:1|max:3650',
            'price'        => 'required|numeric|min:0|max:9999999.99',
            'description'  => 'nullable|string|max:1000',
            'color'        => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'sort_order'   => 'nullable|integer|min:0|max:9999',
            'is_active'    => 'boolean',
        ], [
            'name.unique'         => 'A membership plan with this name already exists.',
            'duration_days.min'   => 'Duration must be at least 1 day.',
            'duration_days.max'   => 'Duration cannot exceed 3650 days (10 years).',
            'price.min'           => 'Price must be 0 or greater.',
            'color.regex'         => 'Color must be a valid hex code (e.g. #22C55E).',
        ]);

        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        MembershipPlan::create($validated);

        return redirect()
            ->route('membership-plans.index')
            ->with('success', __('Membership plan ":name" created successfully.', ['name' => $validated['name']]));
    }

    /**
     * Show the form for editing the specified membership plan.
     */
    public function edit(MembershipPlan $membershipPlan)
    {
        return view('membership-plans.edit', compact('membershipPlan'));
    }

    /**
     * Update the specified membership plan.
     */
    public function update(Request $request, MembershipPlan $membershipPlan)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100|unique:membership_plans,name,' . $membershipPlan->id,
            'duration_days'=> 'required|integer|min:1|max:3650',
            'price'        => 'required|numeric|min:0|max:9999999.99',
            'description'  => 'nullable|string|max:1000',
            'color'        => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'sort_order'   => 'nullable|integer|min:0|max:9999',
            'is_active'    => 'boolean',
        ], [
            'name.unique'         => 'A membership plan with this name already exists.',
            'duration_days.min'   => 'Duration must be at least 1 day.',
            'duration_days.max'   => 'Duration cannot exceed 3650 days (10 years).',
            'price.min'           => 'Price must be 0 or greater.',
            'color.regex'         => 'Color must be a valid hex code (e.g. #22C55E).',
        ]);

        $validated['is_active']  = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $membershipPlan->update($validated);

        return redirect()
            ->route('membership-plans.index')
            ->with('success', __('Membership plan ":name" updated successfully.', ['name' => $membershipPlan->name]));
    }

    /**
     * Remove the specified membership plan if it has no member memberships.
     */
    public function destroy(MembershipPlan $membershipPlan)
    {
        if ($membershipPlan->memberMemberships()->exists()) {
            return redirect()
                ->route('membership-plans.index')
                ->with('error', __('Cannot delete ":name" - it is assigned to one or more members. Please deactivate it instead.', ['name' => $membershipPlan->name]));
        }

        $name = $membershipPlan->name;
        $membershipPlan->delete();

        return redirect()
            ->route('membership-plans.index')
            ->with('success', __('Membership plan ":name" deleted successfully.', ['name' => $name]));
    }

    /**
     * Toggle the active/inactive status of a membership plan.
     */
    public function toggleStatus(MembershipPlan $membershipPlan)
    {
        $membershipPlan->update(['is_active' => !$membershipPlan->is_active]);

        $status = $membershipPlan->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('membership-plans.index')
            ->with('success', __('Membership plan ":name" :status successfully.', ['name' => $membershipPlan->name, 'status' => $status]));
    }
}
