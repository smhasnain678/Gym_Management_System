<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Search by description or action
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        // Filter by specific action
        if ($request->filled('action_filter') && $request->action_filter !== 'all') {
            $query->where('action', $request->action_filter);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get distinct actions for the filter dropdown
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('activity-logs.index', compact('logs', 'actions'));
    }
}
