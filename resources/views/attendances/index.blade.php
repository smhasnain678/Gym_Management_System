@extends('layouts.app')

@section('title', __('Attendance Management'))
@section('page_title', __('Attendance Management'))

@section('content')

<!-- View Toggles & Search/Filter Bar -->
<div class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <!-- View Switcher -->
    <div class="flex items-center bg-white p-1 rounded-xl shadow-sm border border-gray-200">
        <a href="{{ route('attendances.index', ['view' => 'daily', 'date' => request('date', now()->format('Y-m-d'))]) }}" 
           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $view === 'daily' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
            {{ __('Daily View') }}
        </a>
        <a href="{{ route('attendances.index', ['view' => 'monthly', 'month' => request('month', now()->format('Y-m'))]) }}" 
           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $view === 'monthly' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
            {{ __('Monthly View') }}
        </a>
    </div>

    <!-- Search & Filter Form -->
    <form method="GET" action="{{ route('attendances.index') }}" class="flex flex-col sm:flex-row gap-3">
        <input type="hidden" name="view" value="{{ $view }}">
        
        @if($view === 'daily')
            <!-- Date Picker -->
            <div class="relative">
                <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                       class="pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                       style="--tw-ring-color: #22C55E;">
            </div>
        @else
            <!-- Month Picker -->
            <div class="relative">
                <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()"
                       class="pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                       style="--tw-ring-color: #22C55E;">
            </div>
        @endif

        <!-- Search Input -->
        <div class="relative">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search members...') }}"
                   class="pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                   style="--tw-ring-color: #22C55E;">
        </div>

        @if($view === 'monthly')
        <!-- Status Filter -->
        <select name="status" onchange="this.form.submit()"
                class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                style="--tw-ring-color: #22C55E;">
            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>{{ __('All Status') }}</option>
            <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>{{ __('Present') }}</option>
            <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>{{ __('Absent') }}</option>
        </select>
        @endif

        <button type="submit" class="px-4 py-2 rounded-xl text-white text-sm font-medium transition-colors"
                style="background-color: #22C55E;">
            {{ __('Filter') }}
        </button>
        @if(request()->hasAny(['search', 'status']) && (request('search') != '' || (request('status') != '' && request('status') != 'all')))
            <a href="{{ route('attendances.index', ['view' => $view]) }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-200 transition-colors">
                {{ __('Clear') }}
            </a>
        @endif
    </form>
</div>

@if($view === 'daily')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: #EFF6FF; color: #3B82F6;">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">{{ __('Total Active Members') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalMembers }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: #DCFCE7; color: #22C55E;">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">{{ __('Present Today') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $presentCount }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: #FEE2E2; color: #EF4444;">
                <i data-lucide="x-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">{{ __('Absent Today') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $absentCount }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: #F3E8FF; color: #A855F7;">
                <i data-lucide="percent" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">{{ __('Attendance Rate') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $attendancePercentage }}%</p>
            </div>
        </div>
    </div>

    <!-- Daily Attendance Table -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">{{ __('Member') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Contact') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-4 font-medium text-center">{{ __('Check-In / Out') }}</th>
                        <th class="px-6 py-4 font-medium text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($members as $member)
                        @php
                            $attendance = $member->attendances->first(); // We eager loaded only the specific date
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                                         style="background: linear-gradient(135deg, #22C55E, #16A34A);">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $member->name }}</p>
                                        <p class="text-xs text-gray-500">{{ __('ID') }}: #{{ $member->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                                    {{ $member->phone }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($attendance)
                                    @if($attendance->status === 'present')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> {{ __('Present') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> {{ __('Absent') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> {{ __('Unmarked') }}
                                    </span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4">
                                @if($attendance && $attendance->status === 'present')
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <div><span class="font-medium text-gray-500">{{ __('Check-In:') }}</span> {{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->gymTimeFormat() : '—' }}</div>
                                        <div><span class="font-medium text-gray-500">{{ __('Check-Out:') }}</span> {{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->gymTimeFormat() : '—' }}</div>
                                    </div>
                                @else
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <div><span class="font-medium text-gray-500">{{ __('Check-In:') }}</span> —</div>
                                        <div><span class="font-medium text-gray-500">{{ __('Check-Out:') }}</span> —</div>
                                    </div>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(!$attendance)
                                        <form action="{{ route('attendances.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="member_id" value="{{ $member->id }}">
                                            <input type="hidden" name="date" value="{{ $date }}">
                                            <input type="hidden" name="status" value="present">
                                            <input type="hidden" name="check_in_time" value="{{ now()->format('H:i') }}">
                                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border border-green-200 bg-green-50 text-green-700 hover:bg-green-100">
                                                {{ __('Mark Present') }}
                                            </button>
                                        </form>

                                        <form action="{{ route('attendances.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="member_id" value="{{ $member->id }}">
                                            <input type="hidden" name="date" value="{{ $date }}">
                                            <input type="hidden" name="status" value="absent">
                                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border border-red-200 bg-red-50 text-red-700 hover:bg-red-100">
                                                {{ __('Mark Absent') }}
                                            </button>
                                        </form>
                                    @elseif($attendance->status === 'present')
                                        @if(!$attendance->check_out_time)
                                            <form action="{{ route('attendances.checkout', $attendance) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100">
                                                    {{ __('Check Out') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed">
                                                {{ __('Checked Out') }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                        <i data-lucide="users" class="w-6 h-6 text-gray-400"></i>
                                    </div>
                                    <p class="text-gray-900 font-medium">{{ __('No members found') }}</p>
                                    <p class="text-gray-500 text-sm mt-1">{{ __('Adjust your filters or date selection.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($members->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $members->links() }}
            </div>
        @endif
    </div>
@endif

@if($view === 'monthly')
    <!-- Monthly Attendance Table -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">{{ __('Date') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Member') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Check-In') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Check-Out') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($attendances as $record)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $record->date->gymDateFormat() }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('members.show', $record->member) }}" class="flex items-center gap-3 group">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                                         style="background: linear-gradient(135deg, #22C55E, #16A34A);">
                                        {{ strtoupper(substr($record->member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 group-hover:text-green-600 transition-colors">{{ $record->member->name }}</p>
                                    </div>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                @if($record->status === 'present')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> {{ __('Present') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> {{ __('Absent') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->gymTimeFormat() : '--' }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->gymTimeFormat() : '--' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                        <i data-lucide="calendar-x" class="w-6 h-6 text-gray-400"></i>
                                    </div>
                                    <p class="text-gray-900 font-medium">{{ __('No attendance records found') }}</p>
                                    <p class="text-gray-500 text-sm mt-1">{{ __('There are no marked attendances for this month.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attendances->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
@endif

@endsection
