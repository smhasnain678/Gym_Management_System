@extends('layouts.app')

@section('title', __('Member Details'))
@section('meta_description', __('View member profile, memberships, and payment history.'))
@section('page_title', __('Member Details'))

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Back & Actions row --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <a href="{{ route('members.index') }}"
           class="inline-flex items-center gap-1.5 text-sm transition-colors hover:opacity-70"
           style="color:#22C55E;">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            {{ __('Back to Members') }}
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('members.edit', $member) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition-colors"
               style="color:#374151;">
                <i data-lucide="pencil" class="w-4 h-4"></i>
                {{ __('Edit Profile') }}
            </a>
            <form action="{{ route('members.toggle-status', $member) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-xl border transition-colors
                               {{ $member->status === 'active' ? 'bg-orange-50 border-orange-200 text-orange-700 hover:bg-orange-100' : 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100' }}">
                    <i data-lucide="{{ $member->status === 'active' ? 'pause-circle' : 'play-circle' }}" class="w-4 h-4"></i>
                    {{ $member->status === 'active' ? __('Suspend Member') : __('Activate Member') }}
                </button>
            </form>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT COLUMN: Profile & Contact --}}
        <div class="space-y-6">
            
            {{-- Profile Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center">
                @if($member->profile_photo)
                    <img src="{{ asset('storage/' . $member->profile_photo) }}" alt="Profile Photo" class="w-24 h-24 rounded-full object-cover mb-4 shadow-sm border border-gray-200">
                @else
                    <div class="w-24 h-24 rounded-full flex items-center justify-center mb-4 text-3xl font-bold text-white shadow-sm"
                         style="background:linear-gradient(135deg,#22C55E,#16A34A);">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                @endif
                <h2 class="text-xl font-bold" style="color:#111827;">{{ $member->name }}</h2>
                <p class="text-sm mb-3" style="color:#6B7280;">#{{ str_pad($member->id, 5, '0', STR_PAD_LEFT) }}</p>

                @php
                    $statusColors = [
                        'active'        => ['bg'=>'#DCFCE7','color'=>'#15803D'],
                        'expired'       => ['bg'=>'#FEE2E2','color'=>'#DC2626'],
                        'expiring_soon' => ['bg'=>'#FEF3C7','color'=>'#B45309'],
                        'suspended'     => ['bg'=>'#F3F4F6','color'=>'#6B7280'],
                    ];
                    $sc = $statusColors[$member->status] ?? ['bg'=>'#F3F4F6','color'=>'#6B7280'];
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider"
                      style="background-color:{{ $sc['bg'] }}; color:{{ $sc['color'] }};">
                    {{ __(str_replace('_', ' ', $member->status)) }}
                </span>

                <div class="w-full mt-6 pt-6 border-t border-gray-100 flex justify-around text-center">
                    <div>
                        <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Joined') }}</p>
                        <p class="text-sm font-semibold mt-0.5" style="color:#374151;">{{ $member->joining_date->gymDateFormat() }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Gender') }}</p>
                        <p class="text-sm font-semibold mt-0.5 capitalize" style="color:#374151;">{{ $member->gender }}</p>
                    </div>
                    @if($member->date_of_birth)
                    <div>
                        <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Age') }}</p>
                        <p class="text-sm font-semibold mt-0.5" style="color:#374151;">{{ $member->date_of_birth->age }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Contact & Medical Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider" style="color:#9CA3AF;">{{ __('Contact Info') }}</h3>
                
                <div class="flex items-start gap-3">
                    <i data-lucide="phone" class="w-4 h-4 mt-0.5" style="color:#9CA3AF;"></i>
                    <div>
                        <p class="text-sm font-medium" style="color:#374151;">{{ $member->phone }}</p>
                        <p class="text-xs" style="color:#6B7280;">{{ __('Mobile') }}</p>
                    </div>
                </div>

                @if($member->email)
                <div class="flex items-start gap-3">
                    <i data-lucide="mail" class="w-4 h-4 mt-0.5" style="color:#9CA3AF;"></i>
                    <div>
                        <p class="text-sm font-medium break-all" style="color:#374151;">{{ $member->email }}</p>
                        <p class="text-xs" style="color:#6B7280;">{{ __('Email') }}</p>
                    </div>
                </div>
                @endif

                @if($member->address)
                <div class="flex items-start gap-3">
                    <i data-lucide="map-pin" class="w-4 h-4 mt-0.5" style="color:#9CA3AF;"></i>
                    <div>
                        <p class="text-sm font-medium" style="color:#374151;">{{ $member->address }}</p>
                    </div>
                </div>
                @endif

                @if($member->emergency_contact_name)
                <div class="pt-4 border-t border-gray-100">
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-3" style="color:#9CA3AF;">{{ __('Emergency Contact') }}</h3>
                    <p class="text-sm font-medium" style="color:#374151;">{{ $member->emergency_contact_name }}</p>
                    <p class="text-sm" style="color:#6B7280;">{{ $member->emergency_contact_phone }}</p>
                </div>
                @endif

                @if($member->blood_group || $member->height || $member->weight || $member->medical_notes)
                <div class="pt-4 border-t border-gray-100">
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-3" style="color:#9CA3AF;">{{ __('Medical Info') }}</h3>
                    <div class="grid grid-cols-2 gap-y-2 gap-x-4 text-sm mb-3">
                        @if($member->blood_group)
                            <div style="color:#6B7280;">{{ __('Blood') }}: <span class="font-medium" style="color:#374151;">{{ $member->blood_group }}</span></div>
                        @endif
                        @if($member->height)
                            <div style="color:#6B7280;">{{ __('Height') }}: <span class="font-medium" style="color:#374151;">{{ $member->height }}cm</span></div>
                        @endif
                        @if($member->weight)
                            <div style="color:#6B7280;">{{ __('Weight') }}: <span class="font-medium" style="color:#374151;">{{ $member->weight }}kg</span></div>
                        @endif
                    </div>
                    @if($member->medical_notes)
                        <div class="p-3 bg-gray-50 rounded-lg text-sm" style="color:#374151;">
                            {{ $member->medical_notes }}
                        </div>
                    @endif
                </div>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN: Membership & History --}}
        <div class="lg:col-span-2 space-y-6">

            @if($errors->any())
                <div class="p-4 rounded-xl text-sm flex items-start gap-2" style="background-color:#FEF2F2; color:#DC2626;">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                    <ul class="list-disc pl-1 space-y-0.5">
                        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            {{-- Current Membership Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between" style="background-color:#F8FAFC;">
                    <h3 class="text-base font-semibold flex items-center gap-2" style="color:#111827;">
                        <i data-lucide="award" class="w-5 h-5" style="color:#22C55E;"></i>
                        {{ __('Current Membership') }}
                    </h3>
                    @if(!$activeMembership)
                        <button type="button" onclick="document.getElementById('assign-modal').classList.remove('hidden')"
                                id="btn-assign-plan"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white rounded-lg transition-colors"
                                style="background-color:#22C55E;">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> {{ __('Assign Plan') }}
                        </button>
                    @else
                        <button type="button" onclick="document.getElementById('renew-modal').classList.remove('hidden')"
                                id="btn-renew-plan"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition-colors"
                                style="color:#374151;">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> {{ __('Renew') }}
                        </button>
                    @endif
                </div>
                
                <div class="p-6">
                    @if($activeMembership)
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-3 h-3 rounded-full" style="background-color:{{ $activeMembership->membershipPlan->color ?? '#22C55E' }};"></span>
                                    <h4 class="text-lg font-bold" style="color:#111827;">{{ $activeMembership->membershipPlan->name }}</h4>
                                </div>
                                <p class="text-sm mb-4" style="color:#6B7280;">
                                    {{ $activeMembership->start_date->gymDateFormat() }} — {{ $activeMembership->end_date->gymDateFormat() }}
                                </p>
                                <div class="flex gap-4">
                                    <div class="px-3 py-2 bg-gray-50 rounded-xl border border-gray-100">
                                        <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Total') }}</p>
                                        <p class="text-sm font-semibold" style="color:#374151;">{{ number_format($activeMembership->total_amount, 2) }}</p>
                                    </div>
                                    <div class="px-3 py-2 bg-gray-50 rounded-xl border border-gray-100">
                                        <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Paid') }}</p>
                                        <p class="text-sm font-semibold" style="color:#15803D;">{{ number_format($activeMembership->paid_amount, 2) }}</p>
                                    </div>
                                    <div class="px-3 py-2 bg-gray-50 rounded-xl border border-gray-100">
                                        <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Due') }}</p>
                                        <p class="text-sm font-semibold" style="{{ $activeMembership->remaining_amount > 0 ? 'color:#DC2626;' : 'color:#15803D;' }}">
                                            {{ number_format($activeMembership->remaining_amount, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if($activeMembership->remaining_amount > 0)
                            <div class="flex-shrink-0">
                                <button type="button" onclick="document.getElementById('payment-modal').classList.remove('hidden')"
                                        id="btn-record-payment"
                                        class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95"
                                        style="background-color:#22C55E;">
                                    <i data-lucide="credit-card" class="w-4 h-4"></i> {{ __('Record Payment') }}
                                </button>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i data-lucide="alert-circle" class="w-8 h-8 mx-auto mb-3" style="color:#9CA3AF;"></i>
                            <p class="text-sm font-medium" style="color:#374151;">{{ __('No active membership') }}</p>
                            <p class="text-sm mt-1" style="color:#6B7280;">{{ __('This member currently does not have an active plan.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Membership History (All memberships) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold" style="color:#111827;">{{ __('Membership History') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider" style="color:#9CA3AF;">
                            <tr>
                                <th class="px-6 py-3 font-medium">{{ __('Plan') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Period') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ __('Paid / Total') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($member->memberships()->with('membershipPlan')->orderByDesc('start_date')->get() as $mem)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium" style="color:#374151;">
                                    {{ $mem->membershipPlan->name ?? __('Unknown Plan') }}
                                    @if($mem->remaining_amount > 0)
                                        <span class="ml-2 px-2 py-0.5 text-[10px] font-bold rounded bg-red-100 text-red-700">{{ __('Due') }}: {{ number_format($mem->remaining_amount,0) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3" style="color:#6B7280;">
                                    {{ $mem->start_date->gymDateFormat() }} — {{ $mem->end_date->gymDateFormat() }}
                                </td>
                                <td class="px-6 py-3 text-right text-xs font-medium" style="color:#374151;">
                                    {{ number_format($mem->paid_amount, 0) }} / {{ number_format($mem->total_amount, 0) }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    @php
                                        $msc = $statusColors[$mem->status] ?? ['bg'=>'#F3F4F6','color'=>'#6B7280'];
                                    @endphp
                                    <span class="px-2 py-1 rounded text-xs font-medium" style="background-color:{{ $msc['bg'] }}; color:{{ $msc['color'] }};">
                                        {{ ucfirst(str_replace('_',' ',$mem->status)) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm" style="color:#9CA3AF;">{{ __('No membership history available.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Payment History --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold flex items-center gap-2" style="color:#111827;">
                        <i data-lucide="receipt" class="w-5 h-5" style="color:#22C55E;"></i>
                        {{ __('Payment History') }}
                    </h3>
                </div>
                @php
                    $allPayments = $member->feePayments()->with('memberMembership.membershipPlan')->orderByDesc('payment_date')->get();
                @endphp
                @if($allPayments->isEmpty())
                    <div class="px-6 py-8 text-center text-sm" style="color:#9CA3AF;">{{ __('No payments recorded yet.') }}</div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider" style="color:#9CA3AF;">
                            <tr>
                                <th class="px-6 py-3 font-medium">{{ __('Date') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Plan') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Method') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($allPayments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3" style="color:#6B7280;">
                                    {{ $payment->payment_date->gymDateFormat() }}
                                </td>
                                <td class="px-6 py-3 font-medium" style="color:#374151;">
                                    {{ $payment->memberMembership?->membershipPlan?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 capitalize">
                                        {{ str_replace('_', ' ', $payment->payment_method) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right font-semibold" style="color:#15803D;">
                                    {{ number_format($payment->amount_paid, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-sm font-semibold" style="color:#374151;">{{ __('Total Paid') }}</td>
                                <td class="px-6 py-3 text-right text-sm font-bold" style="color:#15803D;">
                                    {{ number_format($allPayments->sum('amount_paid'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>

            {{-- Check-in History --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold flex items-center gap-2" style="color:#111827;">
                        <i data-lucide="calendar-check" class="w-5 h-5" style="color:#22C55E;"></i>
                        {{ __('Check-in History') }}
                    </h3>
                    <a href="{{ route('attendances.index', ['view' => 'monthly', 'search' => $member->phone]) }}" class="text-sm font-medium" style="color:#22C55E;">{{ __('View All') }}</a>
                </div>
                @php
                    $recentAttendances = $member->attendances()->orderByDesc('date')->take(5)->get();
                @endphp
                @if($recentAttendances->isEmpty())
                    <div class="px-6 py-8 text-center text-sm" style="color:#9CA3AF;">{{ __('No check-in history found.') }}</div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider" style="color:#9CA3AF;">
                            <tr>
                                <th class="px-6 py-3 font-medium">{{ __('Date') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Check-In') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Check-Out') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentAttendances as $attendance)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium" style="color:#374151;">
                                    {{ $attendance->date->gymDateFormat() }}
                                </td>
                                <td class="px-6 py-3">
                                    @if($attendance->status === 'present')
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                            {{ __('Present') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
                                            {{ __('Absent') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3" style="color:#6B7280;">
                                    {{ ($attendance->status === 'present' && $attendance->check_in_time) ? \Carbon\Carbon::parse($attendance->check_in_time)->gymTimeFormat() : '—' }}
                                </td>
                                <td class="px-6 py-3" style="color:#6B7280;">
                                    {{ ($attendance->status === 'present' && $attendance->check_out_time) ? \Carbon\Carbon::parse($attendance->check_out_time)->gymTimeFormat() : '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>



        </div>
    </div>
</div>

{{-- MODALS ────────────────────────────────────────────────────────────────── --}}

{{-- 1. Assign Membership Modal --}}
@if(!$activeMembership)
<div id="assign-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold" style="color:#111827;">{{ __('Assign Membership Plan') }}</h3>
            <button onclick="document.getElementById('assign-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="{{ route('members.assign-membership', $member) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Select Plan') }} <span class="text-red-500">*</span></label>
                <select name="membership_plan_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white" style="color:#374151;">
                    <option value="">{{ __('— Choose Plan —') }}</option>
                    @foreach($activePlans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} ({{ number_format($plan->price, 0) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Start Date') }} <span class="text-red-500">*</span></label>
                <input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white" style="color:#374151;">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Initial Payment (Optional)') }}</label>
                <input type="number" name="paid_amount" min="0" step="0.01" value="0"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white" style="color:#374151;">
                <p class="text-xs mt-1" style="color:#6B7280;">{{ __('Leave 0 to record payment later.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Notes (Optional)') }}</label>
                <textarea name="notes" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white resize-none" style="color:#374151;"></textarea>
            </div>
            <div class="pt-2 flex justify-end gap-3 border-t border-gray-100 mt-4">
                <button type="button"
                        onclick="document.getElementById('assign-modal').classList.add('hidden')"
                        class="px-5 py-2.5 text-sm font-semibold rounded-xl border-2 border-gray-300 bg-white hover:bg-gray-50 transition-colors"
                        style="color:#374151;">
                    {{ __('Cancel') }}
                </button>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95"
                        style="background-color:#22C55E;">
                    {{ __('Assign Plan') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- 2. Renew Membership Modal --}}
@if($activeMembership)
<div id="renew-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold" style="color:#111827;">{{ __('Renew Membership') }}</h3>
            <button onclick="document.getElementById('renew-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="{{ route('members.renew-membership', $member) }}" method="POST" class="p-6 space-y-4">
            @csrf
            {{-- Info banner about current plan --}}
            <div class="p-3 rounded-xl text-xs" style="background-color:#F0FDF4; color:#15803D; border:1px solid #BBF7D0;">
                <strong>{{ __('Current') }}:</strong> {{ $activeMembership->membershipPlan->name }} — {{ __('ends') }} {{ $activeMembership->end_date->gymDateFormat() }}
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Select New Plan') }} <span class="text-red-500">*</span></label>
                <select name="membership_plan_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white" style="color:#374151;">
                    <option value="">{{ __('— Choose Plan —') }}</option>
                    @foreach($activePlans as $plan)
                        <option value="{{ $plan->id }}" {{ $plan->id == $activeMembership->membership_plan_id ? 'selected' : '' }}>
                            {{ $plan->name }} ({{ number_format($plan->price, 0) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('New Start Date') }} <span class="text-red-500">*</span></label>
                <input type="date" name="start_date" 
                       value="{{ max(now(), $activeMembership->end_date->copy()->addDay())->format('Y-m-d') }}" 
                       required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white" style="color:#374151;">
                <p class="text-xs mt-1" style="color:#6B7280;">{{ __('Defaults to day after current plan ends.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Payment Now (Optional)') }}</label>
                <input type="number" name="paid_amount" min="0" step="0.01" value="0"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white" style="color:#374151;">
                <p class="text-xs mt-1" style="color:#6B7280;">{{ __('Leave 0 to record payment later.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Notes (Optional)') }}</label>
                <textarea name="notes" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white resize-none" style="color:#374151;"></textarea>
            </div>
            <div class="pt-2 flex justify-end gap-3 border-t border-gray-100 mt-4">
                <button type="button"
                        onclick="document.getElementById('renew-modal').classList.add('hidden')"
                        class="px-5 py-2.5 text-sm font-semibold rounded-xl border-2 border-gray-300 bg-white hover:bg-gray-50 transition-colors"
                        style="color:#374151;">
                    {{ __('Cancel') }}
                </button>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95"
                        style="background-color:#22C55E;">
                    {{ __('Renew Plan') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- 3. Record Payment Modal --}}
@if($activeMembership && $activeMembership->remaining_amount > 0)
<div id="payment-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold" style="color:#111827;">{{ __('Record Payment') }}</h3>
            <button onclick="document.getElementById('payment-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="{{ route('members.record-payment', $member) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="member_membership_id" value="{{ $activeMembership->id }}">
            
            <div class="p-3 rounded-xl text-sm font-medium flex items-center gap-2"
                 style="background-color:#FEF3C7; color:#92400E; border:1px solid #FDE68A;">
                <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
                {{ __('Remaining Balance') }}: <strong>{{ number_format($activeMembership->remaining_amount, 2) }}</strong>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Amount Paid') }} <span class="text-red-500">*</span></label>
                <input type="number" name="amount_paid"
                       max="{{ $activeMembership->remaining_amount }}"
                       min="0.01" step="0.01"
                       value="{{ $activeMembership->remaining_amount }}"
                       required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white" style="color:#374151;">
                <p class="text-xs mt-1" style="color:#6B7280;">{{ __('Maximum') }}: {{ number_format($activeMembership->remaining_amount, 2) }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Payment Date') }} <span class="text-red-500">*</span></label>
                <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white" style="color:#374151;">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Payment Method') }} <span class="text-red-500">*</span></label>
                <select name="payment_method" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white" style="color:#374151;">
                    <option value="cash">{{ __('Cash') }}</option>
                    <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                    <option value="easypaisa">{{ __('Easypaisa') }}</option>
                    <option value="jazzcash">{{ __('JazzCash') }}</option>
                    <option value="card">{{ __('Card') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Notes (Optional)') }}</label>
                <textarea name="notes" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white resize-none" style="color:#374151;"></textarea>
            </div>
            <div class="pt-2 flex justify-end gap-3 border-t border-gray-100 mt-4">
                <button type="button"
                        onclick="document.getElementById('payment-modal').classList.add('hidden')"
                        class="px-5 py-2.5 text-sm font-semibold rounded-xl border-2 border-gray-300 bg-white hover:bg-gray-50 transition-colors"
                        style="color:#374151;">
                    {{ __('Cancel') }}
                </button>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95"
                        style="background-color:#22C55E;">
                    {{ __('Save Payment') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
