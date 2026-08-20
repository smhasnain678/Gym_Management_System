<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Trainer;
use App\Models\MembershipPlan;

class SearchController extends Controller
{
    /**
     * Search across members, trainers, and membership plans.
     */
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty(trim($query))) {
            return response()->json([
                'members' => [],
                'trainers' => [],
                'plans' => [],
            ]);
        }

        // Search Members (by name, phone, or id)
        $members = Member::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('id', 'like', "%{$query}%")
            ->select('id', 'name', 'phone', 'status', 'profile_photo')
            ->limit(5)
            ->get();

        // Search Trainers (by name, phone, or specialization)
        $trainers = Trainer::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('specialization', 'like', "%{$query}%")
            ->select('id', 'name', 'phone', 'specialization', 'profile_photo')
            ->limit(5)
            ->get();

        // Search Membership Plans (by name)
        $plans = MembershipPlan::where('name', 'like', "%{$query}%")
            ->select('id', 'name', 'price', 'duration_days')
            ->limit(5)
            ->get();

        return response()->json([
            'members' => $members,
            'trainers' => $trainers,
            'plans' => $plans,
        ]);
    }
}
