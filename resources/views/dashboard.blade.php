@extends('layouts.app')

@section('title', 'Dashboard')
@section('meta_description', 'WarmUp Gym Management Dashboard — overview of your gym at a glance.')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome Banner --}}
    <div class="rounded-2xl p-6 text-white overflow-hidden relative shadow-sm"
         style="background: linear-gradient(135deg, #22C55E 0%, #15803D 100%);">
        <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full opacity-20" style="background: white;"></div>
        <div class="absolute -right-4 -bottom-12 w-56 h-56 rounded-full opacity-10" style="background: white;"></div>
        <div class="relative flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold mb-1">Welcome back, {{ auth()->user()->name }}! 👋</h1>
                <p class="text-green-100 text-sm">Here is what's happening at your gym today, {{ now()->format('l, d F Y') }}</p>
            </div>
            <div class="hidden sm:block">
                <a href="#" class="inline-flex items-center gap-2 bg-white text-green-700 px-4 py-2 rounded-xl font-medium text-sm hover:bg-green-50 transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add Member
                </a>
            </div>
        </div>
    </div>

    {{-- Core Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-dashboard-stat label="Total Members" :value="$totalMembers" icon="users" color="#22C55E" bg="#DCFCE7" />
        <x-dashboard-stat label="Active Members" :value="$activeMembers" icon="user-check" color="#3B82F6" bg="#DBEAFE" />
        <x-dashboard-stat label="Active Trainers" :value="$activeTrainers" icon="dumbbell" color="#A855F7" bg="#F3E8FF" />
        <x-dashboard-stat label="Today's Check-ins" :value="$todaysAttendance" icon="calendar-check" color="#F59E0B" bg="#FEF3C7" />
    </div>

    {{-- Financial Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-dashboard-stat label="Monthly Revenue" value="${{ number_format($monthlyRevenue, 2) }}" icon="trending-up" color="#10B981" bg="#D1FAE5" />
        <x-dashboard-stat label="Monthly Expenses" value="${{ number_format($monthlyExpenses, 2) }}" icon="trending-down" color="#EF4444" bg="#FEE2E2" />
        <x-dashboard-stat label="Net Profit" value="${{ number_format($netProfit, 2) }}" icon="dollar-sign" color="#06B6D4" bg="#CFFAFE" />
        <x-dashboard-stat label="Pending Fees" value="${{ number_format($pendingFees, 2) }}" icon="alert-circle" color="#F97316" bg="#FFEDD5" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Column: Charts and Activities --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Membership Statistics Chart --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-gray-500"></i> Membership Statistics
                </h3>
                <div id="membershipChart" class="h-[300px] w-full flex items-center justify-center">
                    @if($membershipStats->sum('member_memberships_count') == 0)
                        <p class="text-gray-400 text-sm">No active memberships found.</p>
                    @endif
                </div>
            </div>

            {{-- Recent Activities --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <i data-lucide="activity" class="w-5 h-5 text-gray-500"></i> Recent Activities
                    </h3>
                    <a href="{{ route('activity-logs.index') }}" class="text-sm text-green-600 hover:text-green-700 font-medium">View All</a>
                </div>
                
                @if($recentActivities->isEmpty())
                    <div class="text-center py-6">
                        <i data-lucide="clock" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                        <p class="text-gray-500 text-sm">No recent activities.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($recentActivities as $activity)
                            <div class="flex gap-4 items-start pb-4 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0 mt-1">
                                    <i data-lucide="zap" class="w-4 h-4 text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-900"><span class="font-medium">{{ $activity->user->name ?? 'System' }}</span> {{ $activity->description }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Column: Expiring, Quick Actions --}}
        <div class="space-y-6">
            
            {{-- Quick Actions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="zap" class="w-5 h-5 text-gray-500"></i> Quick Actions
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('members.create') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 bg-gray-50 hover:bg-green-50 hover:border-green-100 hover:text-green-700 transition-colors group">
                        <i data-lucide="user-plus" class="w-6 h-6 text-gray-400 group-hover:text-green-600 mb-2"></i>
                        <span class="text-xs font-medium text-gray-600 group-hover:text-green-700">Add Member</span>
                    </a>
                    <a href="{{ route('fees.index') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 bg-gray-50 hover:bg-green-50 hover:border-green-100 hover:text-green-700 transition-colors group">
                        <i data-lucide="credit-card" class="w-6 h-6 text-gray-400 group-hover:text-green-600 mb-2"></i>
                        <span class="text-xs font-medium text-gray-600 group-hover:text-green-700">Receive Fee</span>
                    </a>
                    <a href="{{ route('attendances.index') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 bg-gray-50 hover:bg-green-50 hover:border-green-100 hover:text-green-700 transition-colors group">
                        <i data-lucide="check-square" class="w-6 h-6 text-gray-400 group-hover:text-green-600 mb-2"></i>
                        <span class="text-xs font-medium text-gray-600 group-hover:text-green-700">Attendance</span>
                    </a>
                    <a href="{{ route('expenses.create') }}" class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-100 bg-gray-50 hover:bg-green-50 hover:border-green-100 hover:text-green-700 transition-colors group">
                        <i data-lucide="receipt" class="w-6 h-6 text-gray-400 group-hover:text-green-600 mb-2"></i>
                        <span class="text-xs font-medium text-gray-600 group-hover:text-green-700">Add Expense</span>
                    </a>
                </div>
            </div>

            {{-- Memberships Expiring Soon --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-orange-500"></i> Expiring Soon
                    </h3>
                </div>
                
                @if($expiringMemberships->isEmpty())
                    <div class="text-center py-4 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-gray-500 text-sm">No memberships expiring in the next 7 days.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($expiringMemberships as $mm)
                            <div class="flex items-center justify-between p-3 rounded-xl border border-orange-100 bg-orange-50/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-orange-700 font-bold text-sm">{{ substr($mm->member->name ?? 'M', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $mm->member->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-orange-600">Ends {{ $mm->end_date->format('M d') }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('members.show', $mm->member) }}" class="text-xs bg-white border border-orange-200 text-orange-700 px-2 py-1 rounded-lg hover:bg-orange-100 transition-colors">
                                    Renew
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- Add ApexCharts --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($membershipStats->sum('member_memberships_count') > 0)
            const membershipLabels = {!! json_encode($membershipStats->pluck('name')) !!};
            const membershipData = {!! json_encode($membershipStats->pluck('member_memberships_count')) !!};
            
            // Extract colors if available, otherwise use defaults
            const membershipColors = {!! json_encode($membershipStats->pluck('color')->map(function($c) { return $c ?: '#22C55E'; })) !!};
            // Ensure we have enough colors if they were null
            const defaultColors = ['#22C55E', '#3B82F6', '#A855F7', '#F59E0B', '#EF4444', '#06B6D4'];
            const finalColors = membershipColors.map((c, i) => c !== '#22C55E' ? c : (defaultColors[i % defaultColors.length]));

            const options = {
                series: membershipData,
                chart: {
                    type: 'donut',
                    height: 300,
                    fontFamily: 'Inter, sans-serif',
                },
                labels: membershipLabels,
                colors: finalColors,
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                },
                                value: {
                                    show: true,
                                    formatter: function (val) {
                                        return val;
                                    }
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Active',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => {
                                            return a + b
                                        }, 0)
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    position: 'bottom',
                    offsetY: 0,
                },
                stroke: {
                    show: true,
                    colors: ['#fff'],
                    width: 2
                }
            };

            const chart = new ApexCharts(document.querySelector("#membershipChart"), options);
            chart.render();
        @endif
    });
</script>
@endpush
@endsection
