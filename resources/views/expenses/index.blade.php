@extends('layouts.app')

@section('title', 'Expense Management')
@section('meta_description', 'Manage gym expenses, track monthly spending, and view category breakdowns.')
@section('page_title', 'Expense Management')

@section('content')

{{-- ── Header & Add Button ────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-2 text-gray-900">
        <h2 class="text-xl font-bold">Expenses</h2>
        <span class="px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
            {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
        </span>
    </div>
    <div class="flex items-center gap-3">
        <form method="GET" action="{{ route('expenses.index') }}" class="flex items-center gap-2">
            <input type="month" name="month" value="{{ request('month', $month) }}" onchange="this.form.submit()"
                   class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                   style="--tw-ring-color: #22C55E;">
        </form>
        <a href="{{ route('expenses.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-medium shadow-sm hover:opacity-90 transition-colors" style="background-color:#22C55E;">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Expense
        </a>
    </div>
</div>

{{-- ── Summary Cards ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Expenses</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalMonthlyExpenses, 2) }}</p>
        </div>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color:#FEE2E2; color:#EF4444;">
            <i data-lucide="trending-down" class="w-6 h-6"></i>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">Recorded Items</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $expenseCount }}</p>
        </div>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color:#EFF6FF; color:#3B82F6;">
            <i data-lucide="receipt" class="w-6 h-6"></i>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">Top Category</p>
            @if($categoryBreakdown->count() > 0)
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $categoryBreakdown->first()->name }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ number_format($categoryBreakdown->first()->total, 2) }}</p>
            @else
                <p class="text-2xl font-bold text-gray-900 mt-1">—</p>
            @endif
        </div>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color:#F3E8FF; color:#A855F7;">
            <i data-lucide="pie-chart" class="w-6 h-6"></i>
        </div>
    </div>

</div>

{{-- ── Main Layout: Table and Breakdown ─────────────────────────────────── --}}
<div class="flex flex-col xl:flex-row gap-6">

    {{-- Expense List --}}
    <div class="flex-1 bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap gap-3 w-full">
                <input type="hidden" name="month" value="{{ request('month', $month) }}">
                <div class="relative flex-1 min-w-[200px]">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or recipient..."
                           class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color: #22C55E;">
                </div>
                <select name="category" onchange="this.form.submit()"
                        class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none min-w-[150px]">
                    <option value="all">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl text-white text-sm font-medium" style="background-color:#22C55E;">Search</button>
                @if(request()->hasAny(['search', 'category']) && (request('search') != '' || request('category') != 'all'))
                    <a href="{{ route('expenses.index', ['month' => $month]) }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-200">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">Date</th>
                        <th class="px-6 py-4 font-medium">Title</th>
                        <th class="px-6 py-4 font-medium">Category</th>
                        <th class="px-6 py-4 font-medium">Paid To</th>
                        <th class="px-6 py-4 font-medium text-right">Amount</th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($expenses as $expense)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-gray-600">{{ $expense->expense_date->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ $expense->title }}</p>
                            @if($expense->notes)
                                <p class="text-xs text-gray-400 truncate max-w-[200px]" title="{{ $expense->notes }}">{{ $expense->notes }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $expense->expenseCategory->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $expense->paid_to ?? '—' }}</td>
                        <td class="px-6 py-4 text-right font-bold text-gray-900">{{ number_format($expense->amount, 2) }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('expenses.edit', $expense) }}" class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </a>
                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i data-lucide="receipt" class="w-8 h-8 text-gray-300 mb-3"></i>
                                <p class="font-medium text-gray-900">No expenses recorded for this month.</p>
                                <a href="{{ route('expenses.create') }}" class="text-sm font-medium mt-2" style="color:#22C55E;">Record your first expense</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $expenses->links() }}</div>
        @endif
    </div>

    {{-- Category Breakdown Sidebar --}}
    <div class="w-full xl:w-80 flex-shrink-0">
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden sticky top-6">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-5 h-5" style="color:#22C55E;"></i>
                    Category Breakdown
                </h3>
            </div>
            <div class="p-6">
                @if($categoryBreakdown->count() > 0)
                    <div class="space-y-4">
                        @foreach($categoryBreakdown as $cat)
                            @php
                                $percentage = $totalMonthlyExpenses > 0 ? ($cat->total / $totalMonthlyExpenses) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between items-end mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $cat->name }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ number_format($cat->total, 0) }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full" style="width: {{ $percentage }}%; background-color:#22C55E;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">No data available for this month.</p>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection
