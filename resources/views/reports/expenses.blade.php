@extends('layouts.app')

@section('title', 'Expense Report')
@section('page_title', 'Expense Report')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('reports.expenses') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-xl border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-xl border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Specific Month</label>
                <input type="month" name="month" value="{{ request('month') }}" class="w-full rounded-xl border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="expense_category_id" class="w-full rounded-xl border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('expense_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end gap-2 mt-2">
                <a href="{{ route('reports.expenses') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors text-sm font-medium">Reset</a>
                <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition-colors text-sm font-medium">Filter</button>
            </div>
        </form>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i data-lucide="receipt" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Expenses</p>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($totalExpenses, 2) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="hash" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Expense Count</p>
                <p class="text-2xl font-bold text-gray-900">{{ $expenseCount }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:row-span-1 lg:col-span-1">
            <h4 class="text-sm font-bold text-gray-900 mb-2">Category Totals</h4>
            <div class="space-y-2 max-h-24 overflow-y-auto">
                @forelse($categoryTotals as $catTotal)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">{{ $catTotal->category_name }}</span>
                        <span class="font-bold text-gray-900">${{ number_format($catTotal->total, 2) }}</span>
                    </div>
                @empty
                    <div class="text-xs text-gray-500">No categories found for this period.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-900">Expense Records</h3>
            
            <div class="flex gap-2">
                <a href="{{ route('reports.expenses', array_merge(request()->all(), ['export' => 'pdf'])) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-colors text-sm font-medium">
                    <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                </a>
                <a href="{{ route('reports.expenses', array_merge(request()->all(), ['export' => 'excel'])) }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-colors text-sm font-medium">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Excel
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">Date</th>
                        <th class="px-6 py-4 font-medium">Title</th>
                        <th class="px-6 py-4 font-medium">Category</th>
                        <th class="px-6 py-4 font-medium">Amount</th>
                        <th class="px-6 py-4 font-medium">Paid To</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($expensesList as $expense)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">{{ $expense->expense_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $expense->title }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                                    {{ $expense->expenseCategory->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-rose-600">${{ number_format($expense->amount, 2) }}</td>
                            <td class="px-6 py-4">{{ $expense->paid_to ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <p>No expenses found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($expensesList->hasPages())
            <div class="p-6 border-t border-gray-100">
                {{ $expensesList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
