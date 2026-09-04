@extends('layouts.app')

@section('title', __('Payment History'))
@section('meta_description', __('Full payment history for all members.'))
@section('page_title', __('Payment History'))

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <a href="{{ route('fees.index') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium transition-colors hover:opacity-70"
       style="color:#22C55E;">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        {{ __('Back to Fee Management') }}
    </a>
</div>

{{-- ── Filters ──────────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('fees.history') }}" class="flex flex-col sm:flex-row gap-3 mb-6">
    <div class="relative">
        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search member...') }}"
               class="pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-transparent"
               style="--tw-ring-color: #22C55E;">
    </div>
    <select name="method" onchange="this.form.submit()"
            class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none">
        <option value="all" {{ request('method', 'all') === 'all' ? 'selected' : '' }}>{{ __('All Methods') }}</option>
        <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
        <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
        <option value="easypaisa" {{ request('method') === 'easypaisa' ? 'selected' : '' }}>{{ __('EasyPaisa') }}</option>
        <option value="jazzcash" {{ request('method') === 'jazzcash' ? 'selected' : '' }}>{{ __('JazzCash') }}</option>
        <option value="card" {{ request('method') === 'card' ? 'selected' : '' }}>{{ __('Card') }}</option>
    </select>
    <input type="month" name="month" value="{{ request('month') }}" onchange="this.form.submit()"
           class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none">
    <button type="submit" class="px-4 py-2 rounded-xl text-white text-sm font-medium" style="background-color:#22C55E;">{{ __('Filter') }}</button>
    @if(request()->hasAny(['search', 'method', 'month']))
        <a href="{{ route('fees.history') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-200">{{ __('Clear') }}</a>
    @endif
</form>

{{-- ── Payment History Table ────────────────────────────────────────────────── --}}
<div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                <tr>
                    <th class="px-6 py-4 font-medium">{{ __('Date') }}</th>
                    <th class="px-6 py-4 font-medium">{{ __('Member') }}</th>
                    <th class="px-6 py-4 font-medium">{{ __('Plan') }}</th>
                    <th class="px-6 py-4 font-medium">{{ __('Method') }}</th>
                    <th class="px-6 py-4 font-medium text-right">{{ __('Amount') }}</th>
                    <th class="px-6 py-4 font-medium">{{ __('Notes') }}</th>
                    <th class="px-6 py-4 font-medium text-right">{{ __('Receipt') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-gray-600">{{ $payment->payment_date->gymDateFormat() }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('members.show', $payment->member) }}" class="font-medium text-gray-900 hover:text-green-600 transition-colors">
                            {{ $payment->member->name }}
                        </a>
                        <p class="text-xs text-gray-400">{{ $payment->member->phone }}</p>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $payment->memberMembership?->membershipPlan?->name ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 capitalize">
                            {{ str_replace('_', ' ', $payment->payment_method ?? '—') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right font-bold text-green-700">{{ number_format($payment->amount_paid, 2) }}</td>
                    <td class="px-6 py-4 text-gray-500 max-w-xs truncate">{{ $payment->notes ?? '—' }}</td>
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
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i data-lucide="receipt" class="w-8 h-8 text-gray-300 mb-3"></i>
                            <p class="font-medium text-gray-900">{{ __('No payments found') }}</p>
                            <p class="text-gray-500 text-sm mt-1">{{ __('Adjust your filters to see results.') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($payments->isNotEmpty())
            <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                <tr>
                    <td colspan="4" class="px-6 py-3 text-sm font-semibold text-gray-700">{{ __('Page Total') }}</td>
                    <td class="px-6 py-3 text-right text-sm font-bold text-green-700">
                        {{ number_format($payments->sum('amount_paid'), 2) }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if($payments->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $payments->links() }}</div>
    @endif
</div>

@endsection
