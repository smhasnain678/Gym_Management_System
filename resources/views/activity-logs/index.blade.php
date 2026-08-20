@extends('layouts.app')

@section('title', 'Activity Logs')
@section('meta_description', 'View all system activity logs.')
@section('page_title', 'Activity Logs')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Filters --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="flex flex-col md:flex-row gap-4" id="filterForm">
            <div class="flex-1 relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description or action..."
                       class="w-full pl-9 pr-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none">
            </div>
            
            <div class="w-full md:w-48">
                <select name="action_filter" onchange="document.getElementById('filterForm').submit()"
                        class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none">
                    <option value="all" {{ request('action_filter') === 'all' || !request('action_filter') ? 'selected' : '' }}>All Actions</option>
                    @foreach($actions as $actionOpt)
                        <option value="{{ $actionOpt }}" {{ request('action_filter') === $actionOpt ? 'selected' : '' }}>{{ $actionOpt }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-48">
                <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="document.getElementById('filterForm').submit()"
                       class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none" title="Date From">
            </div>
            
            <div class="w-full md:w-48">
                <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="document.getElementById('filterForm').submit()"
                       class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none" title="Date To">
            </div>

            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl hover:shadow-md transition-all" style="background-color:#22C55E;">
                Filter
            </button>
            
            @if(request('search') || (request('action_filter') && request('action_filter') !== 'all') || request('date_from') || request('date_to'))
                <a href="{{ route('activity-logs.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition-colors"
                   style="color:#6B7280;">
                    <i data-lucide="x" class="w-4 h-4 mr-1"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Logs List --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($logs->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background-color:#F3F4F6;">
                    <i data-lucide="activity" class="w-8 h-8" style="color:#6B7280;"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No activity found</h3>
                <p class="text-gray-500 text-sm max-w-sm">No logs match your current filters.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold text-gray-600">Timestamp</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-gray-600">User</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-gray-600">Action</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-gray-600">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($logs as $log)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-gray-900">{{ $log->created_at->format('d M, Y') }}</span><br>
                                    <span class="text-gray-500 text-xs">{{ $log->created_at->format('h:i A') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                            <span class="text-green-700 font-bold text-xs">{{ substr($log->user->name ?? 'U', 0, 1) }}</span>
                                        </div>
                                        <span class="text-gray-900 font-medium">{{ $log->user->name ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-gray-600 whitespace-normal min-w-[200px]">{{ $log->description }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
