@extends('layouts.app')

@section('title', __('Member Report'))
@section('page_title', __('Member Report'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('reports.members') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Name, email, phone...') }}" class="report-filter-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                <select name="status" class="report-filter-input">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                    <option value="expiring_soon" {{ request('status') == 'expiring_soon' ? 'selected' : '' }}>{{ __('Expiring Soon') }}</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Trainer') }}</label>
                <select name="trainer_id" class="report-filter-input">
                    <option value="">{{ __('All Trainers') }}</option>
                    @foreach($trainers as $trainer)
                        <option value="{{ $trainer->id }}" {{ request('trainer_id') == $trainer->id ? 'selected' : '' }}>{{ $trainer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Active Plan') }}</label>
                <select name="plan_id" class="report-filter-input">
                    <option value="">{{ __('All Plans') }}</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition-colors text-sm font-medium">{{ __('Filter') }}</button>
                <a href="{{ route('reports.members') }}" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors text-sm font-medium text-center">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">{{ __('Total Members') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalMembers }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">{{ __('Active Members') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $activeMembers }}</p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-900">{{ __('Member List') }}</h3>
            
            <div class="flex gap-2">
                <a href="{{ route('reports.members', array_merge(request()->all(), ['export' => 'pdf'])) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-colors text-sm font-medium">
                    <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                </a>
                <a href="{{ route('reports.members', array_merge(request()->all(), ['export' => 'excel'])) }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-colors text-sm font-medium">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Excel
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">{{ __('Name') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Contact') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Joining Date') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Trainer') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-4 font-medium text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($membersList as $member)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $member->name }}</td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900">{{ $member->phone }}</div>
                                <div class="text-xs text-gray-500">{{ $member->email }}</div>
                            </td>
                            <td class="px-6 py-4">{{ $member->joining_date->gymDateFormat() }}</td>
                            <td class="px-6 py-4">{{ $member->trainer ? $member->trainer->name : '-' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-700',
                                        'expired' => 'bg-gray-100 text-gray-700',
                                        'expiring_soon' => 'bg-yellow-100 text-yellow-700',
                                        'suspended' => 'bg-red-100 text-red-700',
                                    ];
                                    $colorClass = $statusColors[$member->status] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="px-2 py-1 {{ $colorClass }} text-xs rounded-full">{{ ucwords(str_replace('_', ' ', $member->status)) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('reports.members.print', $member) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-xs font-medium">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i> {{ __('Print') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <p>{{ __('No members found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($membersList->hasPages())
            <div class="p-6 border-t border-gray-100">
                {{ $membersList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
