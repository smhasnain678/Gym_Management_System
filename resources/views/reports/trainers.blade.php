@extends('layouts.app')

@section('title', 'Trainer Report')
@section('page_title', 'Trainer Report')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('reports.trainers') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, specialization, phone..." class="w-full rounded-xl border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
            </div>
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-xl border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                    <option value="">All Statuses</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="flex-1 md:flex-none px-6 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition-colors text-sm font-medium">Filter</button>
                <a href="{{ route('reports.trainers') }}" class="flex-1 md:flex-none px-6 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors text-sm font-medium text-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i data-lucide="dumbbell" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Trainers</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalTrainers }}</p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-900">Trainer List</h3>
            
            <div class="flex gap-2">
                <a href="{{ route('reports.trainers', array_merge(request()->all(), ['export' => 'pdf'])) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-colors text-sm font-medium">
                    <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                </a>
                <a href="{{ route('reports.trainers', array_merge(request()->all(), ['export' => 'excel'])) }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-colors text-sm font-medium">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Excel
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">Name</th>
                        <th class="px-6 py-4 font-medium">Specialization</th>
                        <th class="px-6 py-4 font-medium">Contact</th>
                        <th class="px-6 py-4 font-medium">Joining Date</th>
                        <th class="px-6 py-4 font-medium">Assigned Members</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($trainersList as $trainer)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $trainer->name }}</td>
                            <td class="px-6 py-4">{{ $trainer->specialization }}</td>
                            <td class="px-6 py-4">{{ $trainer->phone }}</td>
                            <td class="px-6 py-4">{{ $trainer->joining_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full">
                                    {{ $trainer->members_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($trainer->is_active)
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">Active</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <p>No trainers found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($trainersList->hasPages())
            <div class="p-6 border-t border-gray-100">
                {{ $trainersList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
