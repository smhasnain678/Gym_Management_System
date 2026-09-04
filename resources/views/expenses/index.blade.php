@extends('layouts.app')

@section('title', __('Expense Management'))
@section('meta_description', __('Manage gym expenses, track monthly spending, and view category breakdowns.'))
@section('page_title', __('Expense Management'))

@section('content')

{{-- ── Header & Add Button ────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-2 text-gray-900">
        <h2 class="text-xl font-bold">{{ __('Expenses') }}</h2>
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
            {{ __('Add Expense') }}
        </a>
    </div>
</div>

{{-- ── Summary Cards ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">{{ __('Total Expenses') }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalMonthlyExpenses, 2) }}</p>
        </div>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color:#FEE2E2; color:#EF4444;">
            <i data-lucide="trending-down" class="w-6 h-6"></i>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">{{ __('Recorded Items') }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $expenseCount }}</p>
        </div>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color:#EFF6FF; color:#3B82F6;">
            <i data-lucide="receipt" class="w-6 h-6"></i>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">{{ __('Top Category') }}</p>
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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search title or recipient...') }}"
                           class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color: #22C55E;">
                </div>
                <select name="category" onchange="this.form.submit()"
                        class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none min-w-[150px]">
                    <option value="all">{{ __('All Categories') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl text-white text-sm font-medium" style="background-color:#22C55E;">{{ __('Search') }}</button>
                @if(request()->hasAny(['search', 'category']) && (request('search') != '' || request('category') != 'all'))
                    <a href="{{ route('expenses.index', ['month' => $month]) }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-200">{{ __('Clear') }}</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">{{ __('Date') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Title') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Category') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Paid To') }}</th>
                        <th class="px-6 py-4 font-medium text-right">{{ __('Amount') }}</th>
                        <th class="px-6 py-4 font-medium text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($expenses as $expense)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-gray-600">{{ $expense->expense_date->gymDateFormat() }}</td>
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
                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this expense?') }}');">
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
                                <p class="font-medium text-gray-900">{{ __('No expenses recorded for this month.') }}</p>
                                <a href="{{ route('expenses.create') }}" class="text-sm font-medium mt-2" style="color:#22C55E;">{{ __('Record your first expense') }}</a>
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
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-5 h-5" style="color:#22C55E;"></i>
                    {{ __('Category Breakdown') }}
                </h3>
                <button type="button" onclick="openAddCategoryModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition-colors hover:opacity-90"
                        style="background-color:#22C55E;">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    {{ __('Add Category') }}
                </button>
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
                    <p class="text-sm text-gray-500 text-center py-4">{{ __('No data available for this month.') }}</p>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection

{{-- ── Add Category Modal ─────────────────────────────────────────────────── --}}
<div id="add-category-modal" class="hidden fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">{{ __('Add Expense Category') }}</h3>
            <button type="button" onclick="closeAddCategoryModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('expense-categories.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            {{-- Pass current month so redirect preserves the selected month --}}
            <input type="hidden" name="month" value="{{ $month }}">

            @if($errors->has('name'))
            <div class="p-3 rounded-xl text-sm text-red-700 bg-red-50 border border-red-100">
                {{ $errors->first('name') }}
            </div>
            @endif

            <div>
                <label for="category-name" class="block text-sm font-medium text-gray-700 mb-1.5">
                    {{ __('Category Name') }} <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="category-name"
                       name="name"
                       value="{{ old('name') }}"
                       required
                       maxlength="100"
                       autofocus
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white text-gray-900 text-sm"
                       placeholder="{{ __('e.g. Electricity') }}">
            </div>

            <div class="pt-2 flex justify-end gap-3 border-t border-gray-100">
                <button type="button" onclick="closeAddCategoryModal()"
                        class="px-5 py-2.5 text-sm font-semibold rounded-xl border-2 border-gray-300 bg-white hover:bg-gray-50 transition-colors text-gray-700">
                    {{ __('Cancel') }}
                </button>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-sm hover:opacity-90 transition-all active:scale-95"
                        style="background-color:#22C55E;">
                    {{ __('Add Category') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openAddCategoryModal() {
    document.getElementById('add-category-modal').classList.remove('hidden');
    // Focus the input after the modal animates in
    setTimeout(() => document.getElementById('category-name').focus(), 50);
}
function closeAddCategoryModal() {
    document.getElementById('add-category-modal').classList.add('hidden');
}
// Re-open modal if validation failed so the user sees the error
@if($errors->has('name') && old('name') !== null)
document.addEventListener('DOMContentLoaded', () => openAddCategoryModal());
@endif
// Close on backdrop click
document.getElementById('add-category-modal').addEventListener('click', function (e) {
    if (e.target === this) closeAddCategoryModal();
});
</script>
@endpush
