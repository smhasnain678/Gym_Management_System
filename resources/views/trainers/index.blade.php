@extends('layouts.app')

@section('title', 'Trainers Directory')
@section('meta_description', 'Manage gym trainers and staff.')
@section('page_title', 'Trainers Directory')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header & Stats --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="px-4 py-3 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="p-2 rounded-xl" style="background-color: #E0F2FE; color: #0284C7;">
                    <i data-lucide="dumbbell" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-xs font-medium" style="color: #6B7280;">Total Trainers</p>
                    <p class="text-lg font-bold" style="color: #111827;">{{ $totalTrainers }}</p>
                </div>
            </div>
            <div class="px-4 py-3 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="p-2 rounded-xl" style="background-color: #DCFCE7; color: #15803D;">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-xs font-medium" style="color: #6B7280;">Active</p>
                    <p class="text-lg font-bold" style="color: #111827;">{{ $activeTrainers }}</p>
                </div>
            </div>
            <div class="px-4 py-3 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="p-2 rounded-xl" style="background-color: #F3F4F6; color: #6B7280;">
                    <i data-lucide="user-x" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-xs font-medium" style="color: #6B7280;">Inactive</p>
                    <p class="text-lg font-bold" style="color: #111827;">{{ $totalTrainers - $activeTrainers }}</p>
                </div>
            </div>
        </div>
        <a href="{{ route('trainers.create') }}"
           id="btn-add-trainer"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95"
           style="background-color: #22C55E;">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Trainer
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('trainers.index') }}" class="flex flex-col md:flex-row gap-4" id="filterForm">
            <div class="flex-1 relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone, or specialization..."
                       class="w-full pl-9 pr-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none">
            </div>
            <div class="w-full md:w-48">
                <select name="status" onchange="document.getElementById('filterForm').submit()"
                        class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:outline-none">
                    <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>
            <button type="submit"
                    class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl hover:shadow-md transition-all"
                    style="background-color:#22C55E;">
                Search
            </button>
            @if(request('search') || (request('status') && request('status') !== 'all'))
                <a href="{{ route('trainers.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition-colors"
                   style="color:#6B7280;">
                    <i data-lucide="x" class="w-4 h-4 mr-1"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Desktop Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($trainers->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background-color:#F0FDF4;">
                    <i data-lucide="users" class="w-8 h-8" style="color:#22C55E;"></i>
                </div>
                <p class="text-base font-semibold mb-1" style="color:#111827;">No trainers found</p>
                <p class="text-sm mb-4" style="color:#6B7280;">
                    @if(request('search') || (request('status') && request('status') !== 'all'))
                        No trainers match your search criteria.
                        <a href="{{ route('trainers.index') }}" class="underline" style="color:#22C55E;">Clear filters</a>
                    @else
                        Start by adding your first trainer.
                    @endif
                </p>
                @if(!request('search') && !request('status'))
                    <a href="{{ route('trainers.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl"
                       style="background-color:#22C55E;">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add First Trainer
                    </a>
                @endif
            </div>
        @else
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider" style="color: #9CA3AF;">
                <tr>
                    <th class="px-6 py-4 font-medium">Trainer</th>
                    <th class="px-6 py-4 font-medium">Contact</th>
                    <th class="px-6 py-4 font-medium hidden md:table-cell">Specialization</th>
                    <th class="px-6 py-4 font-medium hidden lg:table-cell">Joined</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($trainers as $trainer)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0"
                                 style="background: linear-gradient(135deg, #22C55E, #16A34A);">
                                {{ strtoupper(substr($trainer->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold" style="color: #111827;">{{ $trainer->name }}</p>
                                <p class="text-xs" style="color: #6B7280;">#TR-{{ str_pad($trainer->id, 4, '0', STR_PAD_LEFT) }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="space-y-1">
                            <p class="flex items-center gap-1.5 text-xs" style="color: #374151;">
                                <i data-lucide="phone" class="w-3.5 h-3.5 text-gray-400"></i> {{ $trainer->phone }}
                            </p>
                            @if($trainer->email)
                            <p class="flex items-center gap-1.5 text-xs" style="color: #6B7280;">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-gray-400"></i> {{ $trainer->email }}
                            </p>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell" style="color: #374151;">
                        {{ $trainer->specialization ?? 'General' }}
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell text-xs" style="color: #374151;">
                        {{ $trainer->joining_date->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4">
                        @if($trainer->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium" style="background-color: #DCFCE7; color: #15803D;">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium" style="background-color: #F3F4F6; color: #6B7280;">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('trainers.show', $trainer) }}"
                               class="p-2 rounded-lg border border-gray-200 hover:border-green-200 hover:bg-green-50 transition-colors"
                               style="color:#6B7280;" title="View Profile">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('trainers.edit', $trainer) }}"
                               class="p-2 rounded-lg border border-gray-200 hover:border-blue-200 hover:bg-blue-50 transition-colors"
                               style="color:#6B7280;" title="Edit">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('trainers.toggle-status', $trainer) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="p-2 rounded-lg border transition-colors {{ $trainer->is_active ? 'border-orange-200 hover:bg-orange-50 text-orange-500' : 'border-green-200 hover:bg-green-50 text-green-600' }}"
                                        title="{{ $trainer->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i data-lucide="{{ $trainer->is_active ? 'pause-circle' : 'play-circle' }}" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Pagination --}}
    @if($trainers->hasPages())
    <div class="mt-4">
        {{ $trainers->links() }}
    </div>
    @endif

</div>
@endsection
