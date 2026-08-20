<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Trainer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainerController extends Controller
{
    /**
     * Display a listing of the trainers.
     */
    public function index(Request $request)
    {
        $query = Trainer::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $trainers = $query->orderBy('name')->paginate(15)->withQueryString();

        $totalTrainers = Trainer::count();
        $activeTrainers = Trainer::where('is_active', true)->count();

        return view('trainers.index', compact('trainers', 'totalTrainers', 'activeTrainers'));
    }

    /**
     * Show the form for creating a new trainer.
     */
    public function create()
    {
        return view('trainers.create');
    }

    /**
     * Store a newly created trainer in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => 'nullable|email|max:150|unique:trainers,email',
            'phone'          => 'required|string|max:20',
            'gender'         => ['required', Rule::in(['male', 'female', 'other'])],
            'date_of_birth'  => 'nullable|date|before:today',
            'specialization' => 'nullable|string|max:100',
            'bio'            => 'nullable|string|max:1000',
            'address'        => 'nullable|string|max:500',
            'salary'         => 'nullable|numeric|min:0',
            'joining_date'   => 'required|date',
            'is_active'      => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $trainer = Trainer::create($validated);

        // Log Activity
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Trainer Added',
            'description'  => "Added new trainer {$trainer->name}.",
            'subject_type' => Trainer::class,
            'subject_id'   => $trainer->id,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return redirect()
            ->route('trainers.show', $trainer)
            ->with('success', 'Trainer "' . $trainer->name . '" added successfully.');
    }

    /**
     * Display the specified trainer.
     */
    public function show(Trainer $trainer)
    {
        return view('trainers.show', compact('trainer'));
    }

    /**
     * Show the form for editing the specified trainer.
     */
    public function edit(Trainer $trainer)
    {
        return view('trainers.edit', compact('trainer'));
    }

    /**
     * Update the specified trainer in storage.
     */
    public function update(Request $request, Trainer $trainer)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => ['nullable', 'email', 'max:150', Rule::unique('trainers', 'email')->ignore($trainer->id)],
            'phone'          => 'required|string|max:20',
            'gender'         => ['required', Rule::in(['male', 'female', 'other'])],
            'date_of_birth'  => 'nullable|date|before:today',
            'specialization' => 'nullable|string|max:100',
            'bio'            => 'nullable|string|max:1000',
            'address'        => 'nullable|string|max:500',
            'salary'         => 'nullable|numeric|min:0',
            'joining_date'   => 'required|date',
            'is_active'      => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $trainer->update($validated);

        // Log Activity
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Trainer Updated',
            'description'  => "Updated trainer {$trainer->name}.",
            'subject_type' => Trainer::class,
            'subject_id'   => $trainer->id,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return redirect()
            ->route('trainers.show', $trainer)
            ->with('success', 'Trainer "' . $trainer->name . '" updated successfully.');
    }

    /**
     * Remove the specified trainer from storage.
     */
    public function destroy(Trainer $trainer)
    {
        // Safe delete: trainers don't have soft deletes out of the box in this schema, 
        // but let's check relationships. Since we can't modify schema, we will just delete it,
        // unless it violates a foreign key constraint. We'll wrap it in try-catch.
        $name = $trainer->name;
        
        try {
            $trainer->delete();
            return redirect()
                ->route('trainers.index')
                ->with('success', 'Trainer "' . $name . '" has been removed.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Cannot delete trainer because they are linked to existing members or records.');
        }
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Trainer $trainer)
    {
        $trainer->update(['is_active' => !$trainer->is_active]);
        $status = $trainer->is_active ? 'Activated' : 'Deactivated';

        return redirect()
            ->route('trainers.show', $trainer)
            ->with('success', 'Trainer "' . $trainer->name . '" ' . strtolower($status) . ' successfully.');
    }
}
