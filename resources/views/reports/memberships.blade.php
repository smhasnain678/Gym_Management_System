@extends('layouts.app')

@section('title', __('Membership Report'))
@section('page_title', __('Membership Report'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('reports.memberships') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Plan') }}</label>
                <select name="plan_id" class="report-filter-input">
                    <option value="">{{ __('All Plans') }}</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                <select name="status" class="report-filter-input">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="Expired" {{ request('status') == 'Expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                    <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Start Date') }}</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="report-filter-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('End Date') }}</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="report-filter-input">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition-colors text-sm font-medium">{{ __('Filter') }}</button>
                <a href="{{ route('reports.memberships') }}" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors text-sm font-medium text-center">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                <i data-lucide="layers" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">{{ __('Total Memberships') }}</p>
                <p class="text-xl font-bold text-gray-900">{{ $totalMemberships }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="dollar-sign" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">{{ __('Total Value') }}</p>
                <p class="text-xl font-bold text-gray-900">${{ number_format($totalAmount, 2) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">{{ __('Total Paid') }}</p>
                <p class="text-xl font-bold text-gray-900">${{ number_format($paidAmount, 2) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">{{ __('Total Remaining') }}</p>
                <p class="text-xl font-bold text-gray-900">${{ number_format($remainingAmount, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-900">{{ __('Membership Records') }}</h3>
            
            <div class="flex gap-2">
                <a href="{{ route('reports.memberships', array_merge(request()->all(), ['export' => 'pdf'])) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-colors text-sm font-medium">
                    <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                </a>
                <a href="{{ route('reports.memberships', array_merge(request()->all(), ['export' => 'excel'])) }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-colors text-sm font-medium">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Excel
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">{{ __('Member') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Plan') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Dates') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Financials') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($memberships as $membership)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $membership->member->name }}</td>
                            <td class="px-6 py-4">{{ $membership->membershipPlan->name }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                <div><span class="font-medium text-gray-700">{{ __('Start:') }}</span> {{ $membership->start_date->gymDateFormat() }}</div>
                                <div><span class="font-medium text-gray-700">{{ __('End:') }}</span> {{ $membership->end_date->gymDateFormat() }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($membership->status === 'Active')
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">{{ __('Active') }}</span>
                                @elseif($membership->status === 'Expired')
                                    <span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full">{{ __('Expired') }}</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">{{ __($membership->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                <div><span class="font-medium text-gray-700">{{ __('Total:') }}</span> ${{ number_format($membership->total_amount, 2) }}</div>
                                <div><span class="font-medium text-green-600">{{ __('Paid:') }}</span> ${{ number_format($membership->paid_amount, 2) }}</div>
                                @if($membership->remaining_amount > 0)
                                    <div><span class="font-medium text-red-600">{{ __('Due:') }}</span> ${{ number_format($membership->remaining_amount, 2) }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <p>{{ __('No memberships found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($memberships->hasPages())
            <div class="p-6 border-t border-gray-100">
                {{ $memberships->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
