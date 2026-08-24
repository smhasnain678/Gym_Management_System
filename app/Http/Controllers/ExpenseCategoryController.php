<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    /**
     * Store a newly created expense category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:expense_categories,name',
            ],
        ], [
            'name.required' => 'Category name is required.',
            'name.unique'   => 'A category with this name already exists.',
            'name.max'      => 'Category name must not exceed 100 characters.',
        ]);

        $category = ExpenseCategory::create([
            'name' => $validated['name'],
        ]);

        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Expense Category Added',
            'description'  => "Added expense category \"{$category->name}\".",
            'subject_type' => ExpenseCategory::class,
            'subject_id'   => $category->id,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return redirect()
            ->route('expenses.index', ['month' => $request->input('month', now()->format('Y-m'))])
            ->with('success', __('Category \\\"{$category->name}\\\" added successfully.'));
    }
}
