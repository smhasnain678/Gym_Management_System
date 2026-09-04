<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Index — Dashboard and list of expenses
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $date = Carbon::parse($month . '-01');

        $expensesQuery = Expense::with('expenseCategory')
            ->whereMonth('expense_date', $date->month)
            ->whereYear('expense_date', $date->year);

        // Search by title or paid_to
        if ($request->filled('search')) {
            $search = $request->search;
            $expensesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('paid_to', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category') && $request->category !== 'all') {
            $expensesQuery->where('expense_category_id', $request->category);
        }

        $totalMonthlyExpenses = (clone $expensesQuery)->sum('amount');
        $expenseCount = (clone $expensesQuery)->count();

        // Get expenses for the table
        $expenses = $expensesQuery->orderByDesc('expense_date')->orderByDesc('id')->paginate(15)->withQueryString();

        // Get category breakdown for the selected month
        $categoryBreakdown = Expense::whereMonth('expense_date', $date->month)
            ->whereYear('expense_date', $date->year)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name, SUM(expenses.amount) as total')
            ->groupBy('expense_categories.name')
            ->orderByDesc('total')
            ->get();

        $categories = ExpenseCategory::orderBy('name')->get();

        return view('expenses.index', compact(
            'expenses',
            'month',
            'totalMonthlyExpenses',
            'expenseCount',
            'categoryBreakdown',
            'categories'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create form
    // ─────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.create', compact('categories'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Store new expense
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title'               => 'required|string|max:150',
            'amount'              => 'required|numeric|min:0.01',
            'expense_date'        => 'required|date',
            'paid_to'             => 'nullable|string|max:150',
            'receipt_image'       => 'nullable|image|max:5120',
            'notes'               => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('receipt_image')) {
            $validated['receipt_image'] = $request->file('receipt_image')->store('receipts', 'public');
        }

        $expense = Expense::create($validated);
        $expense->load('expenseCategory');

        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Expense Added',
            'description'  => 'Expense of ' . number_format($expense->amount, 2) . ' added for ' . $expense->expenseCategory->name . '.',
            'subject_type' => Expense::class,
            'subject_id'   => $expense->id,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return redirect()
            ->route('expenses.index')
            ->with('success', __('Expense recorded successfully.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Edit form
    // ─────────────────────────────────────────────────────────────────────────
    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Update expense
    // ─────────────────────────────────────────────────────────────────────────
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title'               => 'required|string|max:150',
            'amount'              => 'required|numeric|min:0.01',
            'expense_date'        => 'required|date',
            'paid_to'             => 'nullable|string|max:150',
            'receipt_image'       => 'nullable|image|max:5120',
            'notes'               => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('receipt_image')) {
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }
            $validated['receipt_image'] = $request->file('receipt_image')->store('receipts', 'public');
        }

        $expense->update($validated);
        $expense->load('expenseCategory');

        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Expense Updated',
            'description'  => 'Expense for ' . $expense->expenseCategory->name . ' updated to ' . number_format($expense->amount, 2) . '.',
            'subject_type' => Expense::class,
            'subject_id'   => $expense->id,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return redirect()
            ->route('expenses.index')
            ->with('success', __('Expense updated successfully.'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Delete expense
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy(Expense $expense)
    {
        $expense->load('expenseCategory');

        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Expense Deleted',
            'description'  => 'Expense of ' . number_format($expense->amount, 2) . ' for ' . $expense->expenseCategory->name . ' was deleted.',
            'subject_type' => Expense::class,
            'subject_id'   => $expense->id,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        if ($expense->receipt_image) {
            Storage::disk('public')->delete($expense->receipt_image);
        }

        $expense->delete();

        return redirect()
            ->route('expenses.index')
            ->with('success', __('Expense deleted successfully.'));
    }
}
