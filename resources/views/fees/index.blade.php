@extends('layouts.app')

@section('title', __('Fee Management'))
@section('meta_description', __('Manage member fees, record payments, and track outstanding balances.'))
@section('page_title', __('Fee Management'))

@section('content')

{{-- ── Summary Cards ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color:#EFF6FF; color:#3B82F6;">
            <i data-lucide="wallet" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">{{ __('Total Expected') }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalExpected, 0) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color:#DCFCE7; color:#22C55E;">
            <i data-lucide="check-circle" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">{{ __('Total Collected') }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalPaid, 0) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color:#FEE2E2; color:#EF4444;">
            <i data-lucide="alert-circle" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">{{ __('Pending / Outstanding') }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalPending, 0) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color:#F3E8FF; color:#A855F7;">
            <i data-lucide="trending-up" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">{{ __('This Month\'s Revenue') }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($thisMonthRevenue, 0) }}</p>
        </div>
    </div>
</div>

{{-- ── Quick Links ──────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('fees.history') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border border-gray-200 bg-white hover:bg-gray-50 transition-colors"
       style="color:#374151;">
        <i data-lucide="history" class="w-4 h-4"></i>
        {{ __('Full Payment History') }}
    </a>
</div>

{{-- ── Renewal Reminders ────────────────────────────────────────────────────── --}}
@if($renewalReminders->isNotEmpty())
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6">
    <div class="flex items-center gap-2 mb-3">
        <i data-lucide="bell" class="w-5 h-5 text-amber-600"></i>
        <h3 class="text-sm font-semibold text-amber-800">{{ __('Memberships Expiring Within 7 Days') }}</h3>
    </div>
    <div class="space-y-2">
        @foreach($renewalReminders as $reminder)
        <div class="flex items-center justify-between bg-white rounded-xl px-4 py-3 border border-amber-100">
            <div>
                <span class="font-medium text-gray-900 text-sm">{{ $reminder->member->name }}</span>
                <span class="mx-2 text-gray-400">·</span>
                <span class="text-sm text-gray-600">{{ $reminder->membershipPlan->name ?? 'Unknown Plan' }}</span>
                <span class="mx-2 text-gray-400">·</span>
                <span class="text-sm font-medium {{ $reminder->end_date->isPast() ? 'text-red-600' : 'text-amber-700' }}">
                    {{ __('Expires') }} {{ $reminder->end_date->gymDateFormat() }}
                    @if($reminder->end_date->isPast()) <span class="text-xs">({{ __('Overdue') }})</span>
                    @elseif($reminder->end_date->isToday()) <span class="text-xs">({{ __('Today') }})</span>
                    @else <span class="text-xs">({{ $reminder->end_date->diffForHumans() }})</span>
                    @endif
                </span>
            </div>
            <a href="{{ route('members.show', $reminder->member) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition-colors"
               style="background-color:#22C55E;">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                {{ __('Renew') }}
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Pending Fees Table ───────────────────────────────────────────────────── --}}
<div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-base font-semibold flex items-center gap-2 text-gray-900">
            <i data-lucide="clock" class="w-5 h-5" style="color:#22C55E;"></i>
            {{ __('Pending Fees') }}
            @if($pendingFees->total() > 0)
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">{{ $pendingFees->total() }}</span>
            @endif
        </h2>
        <form method="GET" action="{{ route('fees.index') }}" class="flex gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search member...') }}"
                       class="pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                       style="--tw-ring-color: #22C55E;">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl text-white text-sm font-medium" style="background-color:#22C55E;">{{ __('Search') }}</button>
            @if(request('search'))
                <a href="{{ route('fees.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-200">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                <tr>
                    <th class="px-6 py-4 font-medium">{{ __('Member') }}</th>
                    <th class="px-6 py-4 font-medium">{{ __('Plan') }}</th>
                    <th class="px-6 py-4 font-medium text-right">{{ __('Total') }}</th>
                    <th class="px-6 py-4 font-medium text-right">{{ __('Paid') }}</th>
                    <th class="px-6 py-4 font-medium text-right">{{ __('Remaining') }}</th>
                    <th class="px-6 py-4 font-medium">{{ __('Due Date') }}</th>
                    <th class="px-6 py-4 font-medium">{{ __('Status') }}</th>
                    <th class="px-6 py-4 font-medium text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pendingFees as $mm)
                @php
                    $isOverdue = $mm->end_date->isPast();
                    $isDueSoon = !$isOverdue && $mm->end_date->lte(today()->addDays(7));
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <a href="{{ route('members.show', $mm->member) }}" class="flex items-center gap-3 group">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                                 style="background: linear-gradient(135deg, #22C55E, #16A34A);">
                                {{ strtoupper(substr($mm->member->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 group-hover:text-green-600 transition-colors">{{ $mm->member->name }}</p>
                                <p class="text-xs text-gray-500">{{ $mm->member->phone }}</p>
                            </div>
                        </a>
                    </td>
                    <td class="px-6 py-4 text-gray-700">{{ $mm->membershipPlan->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-right font-medium text-gray-900">{{ number_format($mm->total_amount, 0) }}</td>
                    <td class="px-6 py-4 text-right text-green-700 font-medium">{{ number_format($mm->paid_amount, 0) }}</td>
                    <td class="px-6 py-4 text-right font-bold {{ $isOverdue ? 'text-red-600' : 'text-orange-600' }}">{{ number_format($mm->remaining_amount, 0) }}</td>
                    <td class="px-6 py-4">
                        <span class="text-sm {{ $isOverdue ? 'text-red-600 font-semibold' : ($isDueSoon ? 'text-amber-600 font-medium' : 'text-gray-600') }}">
                            {{ $mm->end_date->gymDateFormat() }}
                            @if($isOverdue) <span class="block text-xs text-red-500">{{ __('Overdue') }}</span>
                            @elseif($isDueSoon) <span class="block text-xs text-amber-500">{{ __('Due soon') }}</span>
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($mm->status === 'expiring_soon')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">{{ __('Expiring Soon') }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ __('Active') }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button type="button"
                                onclick="openPayModal({{ $mm->id }}, '{{ addslashes($mm->member->name) }}', '{{ addslashes($mm->membershipPlan->name ?? 'N/A') }}', {{ $mm->remaining_amount }}, {{ $mm->total_amount }}, {{ $mm->paid_amount }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition-colors hover:opacity-90"
                                style="background-color:#22C55E;">
                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                            {{ __('Record Payment') }}
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center mb-3">
                                <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
                            </div>
                            <p class="font-medium text-gray-900">{{ __('No pending fees!') }}</p>
                            <p class="text-gray-500 text-sm mt-1">{{ __('All active memberships are fully paid.') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pendingFees->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $pendingFees->links() }}</div>
    @endif
</div>

{{-- ── Recent Payments ──────────────────────────────────────────────────────── --}}
<div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-base font-semibold flex items-center gap-2 text-gray-900">
            <i data-lucide="receipt" class="w-5 h-5" style="color:#22C55E;"></i>
            {{ __('Recent Payments') }}
        </h2>
        <a href="{{ route('fees.history') }}" class="text-sm font-medium" style="color:#22C55E;">{{ __('View All') }}</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                <tr>
                    <th class="px-6 py-4 font-medium">{{ __('Date') }}</th>
                    <th class="px-6 py-4 font-medium">{{ __('Member') }}</th>
                    <th class="px-6 py-4 font-medium">{{ __('Plan') }}</th>
                    <th class="px-6 py-4 font-medium">{{ __('Method') }}</th>
                    <th class="px-6 py-4 font-medium text-right">{{ __('Amount') }}</th>
                    <th class="px-6 py-4 font-medium text-right">{{ __('Receipt') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentPayments as $payment)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-gray-600">{{ $payment->payment_date->gymDateFormat() }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('members.show', $payment->member) }}" class="font-medium text-gray-900 hover:text-green-600 transition-colors">
                            {{ $payment->member->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $payment->memberMembership?->membershipPlan?->name ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 capitalize">
                            {{ str_replace('_', ' ', $payment->payment_method ?? '—') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right font-semibold text-green-700">{{ number_format($payment->amount_paid, 2) }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('fees.receipt', $payment) }}" target="_blank"
                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium border border-gray-200 bg-white hover:bg-gray-50 transition-colors text-gray-700">
                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                            {{ __('Receipt') }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">{{ __('No payments recorded yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Record Payment Modal ─────────────────────────────────────────────────── --}}
<div id="pay-modal" class="hidden fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md flex flex-col max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">{{ __('Record Payment') }}</h3>
            <button type="button" onclick="closePayModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('fees.pay') }}" method="POST" class="p-4 space-y-3 overflow-y-auto flex-1" id="pay-form">
            @csrf
            <input type="hidden" name="member_membership_id" id="modal-mm-id">

            {{-- Member/Plan info banner --}}
            <div class="p-3 rounded-xl text-sm bg-blue-50 border border-blue-100 text-blue-800">
                <span class="font-semibold" id="modal-member-name"></span>
                <span class="mx-1 text-blue-400">·</span>
                <span id="modal-plan-name"></span>
            </div>

            {{-- Balance info --}}
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="bg-gray-50 rounded-xl p-2 border border-gray-100">
                    <p class="text-xs text-gray-500">{{ __('Total') }}</p>
                    <p class="font-bold text-gray-900 text-sm" id="modal-total"></p>
                </div>
                <div class="bg-green-50 rounded-xl p-2 border border-green-100">
                    <p class="text-xs text-gray-500">{{ __('Paid') }}</p>
                    <p class="font-bold text-green-700 text-sm" id="modal-paid"></p>
                </div>
                <div class="bg-red-50 rounded-xl p-2 border border-red-100">
                    <p class="text-xs text-gray-500">{{ __('Remaining') }}</p>
                    <p class="font-bold text-red-700 text-sm" id="modal-remaining"></p>
                </div>
            </div>

            @if($errors->any())
            <div class="p-3 rounded-xl text-sm text-red-700 bg-red-50 border border-red-100">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Amount') }} <span class="text-red-500">*</span></label>
                <input type="number" name="amount_paid" id="modal-amount" step="0.01" min="0.01"
                       value="{{ old('amount_paid') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white text-gray-900"
                       placeholder="{{ __('Enter amount') }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Payment Date') }} <span class="text-red-500">*</span></label>
                <input type="date" name="payment_date"
                       value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white text-gray-900">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Payment Method') }} <span class="text-red-500">*</span></label>
                <select name="payment_method"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white text-gray-900">
                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                    <option value="easypaisa" {{ old('payment_method') === 'easypaisa' ? 'selected' : '' }}>{{ __('EasyPaisa') }}</option>
                    <option value="jazzcash" {{ old('payment_method') === 'jazzcash' ? 'selected' : '' }}>{{ __('JazzCash') }}</option>
                    <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>{{ __('Card') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Notes (Optional)') }}</label>
                <textarea name="notes" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white resize-none text-gray-900"
                          placeholder="{{ __('Optional note...') }}">{{ old('notes') }}</textarea>
            </div>

            <div class="pt-2 flex justify-end gap-3 border-t border-gray-100">
                <button type="button" onclick="closePayModal()"
                        class="px-5 py-2.5 text-sm font-semibold rounded-xl border-2 border-gray-300 bg-white hover:bg-gray-50 transition-colors text-gray-700">
                    {{ __('Cancel') }}
                </button>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-sm hover:opacity-90 transition-all active:scale-95"
                        style="background-color:#22C55E;">
                    {{ __('Save Payment') }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openPayModal(mmId, memberName, planName, remaining, total, paid) {
    document.getElementById('modal-mm-id').value = mmId;
    document.getElementById('modal-member-name').textContent = memberName;
    document.getElementById('modal-plan-name').textContent = planName;
    document.getElementById('modal-total').textContent = parseFloat(total).toLocaleString('en-PK', {minimumFractionDigits: 2});
    document.getElementById('modal-paid').textContent = parseFloat(paid).toLocaleString('en-PK', {minimumFractionDigits: 2});
    document.getElementById('modal-remaining').textContent = parseFloat(remaining).toLocaleString('en-PK', {minimumFractionDigits: 2});
    document.getElementById('modal-amount').max = remaining;
    document.getElementById('modal-amount').value = '';
    document.getElementById('pay-modal').classList.remove('hidden');
}
function closePayModal() {
    document.getElementById('pay-modal').classList.add('hidden');
}
// Re-open modal with old input on validation error
@if($errors->any() && old('member_membership_id'))
document.addEventListener('DOMContentLoaded', () => {
    // Reopen modal if there was a validation error
    const mmId = '{{ old("member_membership_id") }}';
    if (mmId) {
        document.getElementById('modal-mm-id').value = mmId;
        document.getElementById('pay-modal').classList.remove('hidden');
    }
});
@endif
</script>
@endpush
