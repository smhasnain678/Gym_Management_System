@extends('layouts.app')

@section('title', 'Reports')
@section('page_title', 'Reports')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Reports Dashboard</h2>
        <p class="mt-1 text-sm text-gray-500">Access and export comprehensive data across all modules.</p>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Revenue Report -->
        <a href="{{ route('reports.revenue') }}" class="block group">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all duration-200 hover:shadow-md hover:border-green-500 h-full flex flex-col">
                <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="trending-up" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-green-600 transition-colors">Revenue Report</h3>
                <p class="text-sm text-gray-500 flex-1">View total revenue generated from fee collections, categorized by time periods.</p>
                <div class="mt-4 flex items-center text-sm font-medium text-green-600">
                    View Report <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                </div>
            </div>
        </a>

        <!-- Attendance Report -->
        <a href="{{ route('reports.attendance') }}" class="block group">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all duration-200 hover:shadow-md hover:border-blue-500 h-full flex flex-col">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="calendar-check" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">Attendance Report</h3>
                <p class="text-sm text-gray-500 flex-1">Track member check-ins and check-outs, view present and absent counts.</p>
                <div class="mt-4 flex items-center text-sm font-medium text-blue-600">
                    View Report <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                </div>
            </div>
        </a>

        <!-- Member Report -->
        <a href="{{ route('reports.members') }}" class="block group">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all duration-200 hover:shadow-md hover:border-purple-500 h-full flex flex-col">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-purple-600 transition-colors">Member Report</h3>
                <p class="text-sm text-gray-500 flex-1">Comprehensive list of all members with joining dates, statuses, and assigned trainers.</p>
                <div class="mt-4 flex items-center text-sm font-medium text-purple-600">
                    View Report <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                </div>
            </div>
        </a>

        <!-- Membership Report -->
        <a href="{{ route('reports.memberships') }}" class="block group">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all duration-200 hover:shadow-md hover:border-orange-500 h-full flex flex-col">
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="layers" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-orange-600 transition-colors">Membership Report</h3>
                <p class="text-sm text-gray-500 flex-1">Analyze active, expired, and upcoming memberships across different plans.</p>
                <div class="mt-4 flex items-center text-sm font-medium text-orange-600">
                    View Report <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                </div>
            </div>
        </a>

        <!-- Trainer Report -->
        <a href="{{ route('reports.trainers') }}" class="block group">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all duration-200 hover:shadow-md hover:border-indigo-500 h-full flex flex-col">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="dumbbell" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">Trainer Report</h3>
                <p class="text-sm text-gray-500 flex-1">Overview of trainers, their specializations, statuses, and assigned members.</p>
                <div class="mt-4 flex items-center text-sm font-medium text-indigo-600">
                    View Report <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                </div>
            </div>
        </a>

        <!-- Fee Collection Report -->
        <a href="{{ route('reports.fees') }}" class="block group">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all duration-200 hover:shadow-md hover:border-teal-500 h-full flex flex-col">
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="credit-card" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-teal-600 transition-colors">Fee Collection Report</h3>
                <p class="text-sm text-gray-500 flex-1">Detailed history of all fee payments received, payment methods, and dates.</p>
                <div class="mt-4 flex items-center text-sm font-medium text-teal-600">
                    View Report <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                </div>
            </div>
        </a>

        <!-- Expense Report -->
        <a href="{{ route('reports.expenses') }}" class="block group">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all duration-200 hover:shadow-md hover:border-rose-500 h-full flex flex-col">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="receipt" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-rose-600 transition-colors">Expense Report</h3>
                <p class="text-sm text-gray-500 flex-1">Track gym expenditures by category, date, and view total expenses.</p>
                <div class="mt-4 flex items-center text-sm font-medium text-rose-600">
                    View Report <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                </div>
            </div>
        </a>

    </div>
</div>
@endsection
