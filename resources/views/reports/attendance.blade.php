@extends('layouts.app')

@section('title', __('Attendance Report'))
@section('page_title', __('Attendance Report'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('reports.attendance') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Start Date') }}</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="report-filter-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('End Date') }}</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="report-filter-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Member') }}</label>
                <select name="member_id" class="report-filter-input">
                    <option value="">{{ __('All Members') }}</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}" {{ request('member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                <select name="status" class="report-filter-input">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>{{ __('Present') }}</option>
                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>{{ __('Absent') }}</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition-colors text-sm font-medium">{{ __('Filter') }}</button>
                <a href="{{ route('reports.attendance') }}" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors text-sm font-medium text-center">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">{{ __('Total Records') }}</p>
                <p class="text-xl font-bold text-gray-900">{{ $totalAttendance }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">{{ __('Present') }}</p>
                <p class="text-xl font-bold text-gray-900">{{ $presentCount }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                <i data-lucide="x-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">{{ __('Absent') }}</p>
                <p class="text-xl font-bold text-gray-900">{{ $absentCount }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <i data-lucide="pie-chart" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">{{ __('Attendance Rate') }}</p>
                <p class="text-xl font-bold text-gray-900">{{ $attendanceRate }}%</p>
            </div>
        </div>
    </div>

    <!-- Actions & Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-900">{{ __('Attendance Records') }}</h3>
            
            <div class="flex gap-2">
                <a href="{{ route('reports.attendance', array_merge(request()->all(), ['export' => 'pdf'])) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-colors text-sm font-medium">
                    <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                </a>
                <a href="{{ route('reports.attendance', array_merge(request()->all(), ['export' => 'excel'])) }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-colors text-sm font-medium">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Excel
                </a>
                <a href="{{ route('reports.attendance', array_merge(request()->all(), ['print' => true])) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 rounded-xl hover:bg-gray-100 transition-colors text-sm font-medium">
                    <i data-lucide="printer" class="w-4 h-4"></i> Print
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">{{ __('Date') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Member') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Check In') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Check Out') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">{{ $attendance->date->gymDateFormat() }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $attendance->member->name }}</td>
                            <td class="px-6 py-4">
                                @if($attendance->status === 'present')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> {{ __('Present') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> {{ __('Absent') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->gymTimeFormat() : '-' }}</td>
                            <td class="px-6 py-4">{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->gymTimeFormat() : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i data-lucide="calendar-off" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                                <p>{{ __('No attendance records found.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($attendances->hasPages())
            <div class="p-6 border-t border-gray-100">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
