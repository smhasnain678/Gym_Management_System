<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display the daily or monthly attendance view.
     */
    public function index(Request $request)
    {
        $view = $request->get('view', 'daily');

        if ($view === 'monthly') {
            return $this->monthlyView($request);
        }

        return $this->dailyView($request);
    }

    /**
     * Show daily attendance.
     */
    protected function dailyView(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        // Ensure date is valid, fallback to today
        try {
            $parsedDate = Carbon::parse($date);
        } catch (\Exception $e) {
            $parsedDate = now();
            $date = $parsedDate->format('Y-m-d');
        }

        $query = Member::query()
            ->with(['attendances' => function ($query) use ($date) {
                $query->whereDate('date', $date);
            }])
            ->where('status', '!=', 'suspended'); // Only show active/expired members, not suspended.

        // Search Members
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // We fetch all non-suspended members and their attendance for the date
        $members = $query->orderBy('name')->paginate(20)->withQueryString();

        // Calculate statistics for the given day
        $totalMembers = Member::where('status', '!=', 'suspended')->count();
        $presentCount = Attendance::whereDate('date', $date)->where('status', 'present')->count();
        $absentCount = Attendance::whereDate('date', $date)->where('status', 'absent')->count();
        $attendancePercentage = $totalMembers > 0 ? round(($presentCount / $totalMembers) * 100) : 0;

        return view('attendances.index', compact(
            'members', 'date', 'totalMembers', 'presentCount', 'absentCount', 'attendancePercentage'
        ))->with('view', 'daily');
    }

    /**
     * Show monthly attendance history.
     */
    protected function monthlyView(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        try {
            $parsedMonth = Carbon::parse($month . '-01');
        } catch (\Exception $e) {
            $parsedMonth = now()->startOfMonth();
            $month = $parsedMonth->format('Y-m');
        }

        $query = Attendance::with('member')
            ->whereMonth('date', $parsedMonth->month)
            ->whereYear('date', $parsedMonth->year);

        // Filter by Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search within Attendances (by Member)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();

        return view('attendances.index', compact('attendances', 'month'))->with('view', 'monthly');
    }

    /**
     * Mark or update attendance for a member on a specific date.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id'      => 'required|exists:members,id',
            'date'           => 'required|date',
            'status'         => ['required', Rule::in(['present', 'absent'])],
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
        ]);

        $member = Member::findOrFail($validated['member_id']);

        // Cannot mark attendance for suspended member
        if ($member->status === 'suspended') {
            return back()->with('error', 'Cannot mark attendance for a suspended member.');
        }

        // Duplicate protection via whereDate
        $attendance = Attendance::where('member_id', $validated['member_id'])
            ->whereDate('date', $validated['date'])
            ->first();

        if ($attendance) {
            $attendance->update([
                'status'         => $validated['status'],
                'check_in_time'  => $validated['check_in_time'] ?? $attendance->check_in_time,
                'check_out_time' => $validated['check_out_time'] ?? $attendance->check_out_time,
            ]);
        } else {
            $attendance = Attendance::create([
                'member_id'      => $validated['member_id'],
                'date'           => $validated['date'],
                'status'         => $validated['status'],
                'check_in_time'  => $validated['check_in_time'] ?? null,
                'check_out_time' => $validated['check_out_time'] ?? null,
            ]);
        }

        // Log Activity only if it was recently created (to avoid spamming logs on updates)
        if ($attendance->wasRecentlyCreated) {
            $this->logActivity('Attendance Marked', "Marked attendance ({$attendance->status}) for {$member->name} on {$attendance->date->format('Y-m-d')}.", $attendance);
        } else if ($attendance->wasChanged()) {
            $this->logActivity('Attendance Updated', "Updated attendance ({$attendance->status}) for {$member->name} on {$attendance->date->format('Y-m-d')}.", $attendance);
        }

        return back()->with('success', "Attendance marked successfully for {$member->name}.");
    }

    /**
     * Update an existing attendance record directly (e.g. from the check-in history modal)
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'status'         => ['required', Rule::in(['present', 'absent'])],
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
        ]);

        $attendance->update([
            'status'         => $validated['status'],
            'check_in_time'  => $validated['check_in_time'] ?? $attendance->check_in_time,
            'check_out_time' => $validated['check_out_time'] ?? $attendance->check_out_time,
        ]);

        if ($attendance->wasChanged()) {
            $this->logActivity('Attendance Updated', "Updated attendance ({$attendance->status}) for {$attendance->member->name} on {$attendance->date->format('Y-m-d')}.", $attendance);
        }

        return back()->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Check out a member directly.
     */
    public function checkout(Attendance $attendance)
    {
        if ($attendance->status !== 'present') {
            return back()->with('error', 'Only present members can be checked out.');
        }
        
        if ($attendance->check_out_time) {
            return back()->with('error', 'Member is already checked out.');
        }

        $attendance->update([
            'check_out_time' => now()->format('H:i'),
        ]);

        $this->logActivity('Attendance Checked Out', "Checked out {$attendance->member->name} at {$attendance->check_out_time}.", $attendance);

        return back()->with('success', "{$attendance->member->name} checked out successfully.");
    }

    /**
     * Helper to log activities.
     */
    protected function logActivity(string $action, string $description, $subject = null)
    {
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'description'  => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject ? $subject->id : null,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }
}
