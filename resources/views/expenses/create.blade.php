@extends('layouts.app')

@section('title', __('Add Expense'))
@section('meta_description', __('Record a new expense.'))
@section('page_title', __('Add Expense'))

@section('content')

<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('expenses.index') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium transition-colors hover:opacity-70"
       style="color:#22C55E;">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        {{ __('Back to Expenses') }}
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden max-w-3xl">
    <form action="{{ route('expenses.store') }}" method="POST"
          id="create-expense-form"
          enctype="multipart/form-data" class="p-8">
        @csrf

        <div class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Expense Title') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm"
                           style="--tw-ring-color: #22C55E;" placeholder="{{ __('e.g. November Rent') }}">
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Category') }} <span class="text-red-500">*</span></label>
                    <select name="expense_category_id" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm"
                            style="--tw-ring-color: #22C55E;">
                        <option value="">{{ __('Select Category') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('expense_category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Amount') }} <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm"
                           style="--tw-ring-color: #22C55E;" placeholder="0.00">
                    @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Date') }} <span class="text-red-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', now()->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm"
                           style="--tw-ring-color: #22C55E;">
                    @error('expense_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Paid To / Recipient') }}</label>
                    <input type="text" name="paid_to" value="{{ old('paid_to') }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm"
                           style="--tw-ring-color: #22C55E;" placeholder="{{ __('e.g. Landlord Name, Utility Company') }}">
                    @error('paid_to')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Notes / Description') }}</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm resize-none"
                              style="--tw-ring-color: #22C55E;" placeholder="{{ __('Optional notes...') }}">{{ old('notes') }}</textarea>
                    @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Receipt Image') }}</label>
                    <input type="file" name="receipt_image" accept="image/*"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm"
                           style="--tw-ring-color: #22C55E;">
                    @error('receipt_image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('expenses.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold rounded-xl border-2 border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-sm hover:opacity-90 transition-opacity"
                        style="background-color:#22C55E;">
                    {{ __('Save Expense') }}
                </button>
            </div>

        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Offline-First: Add Expense form ─────────────────────────────────────
    // When ONLINE  → native form submission proceeds unchanged.
    // When OFFLINE → intercept, queue as `expense_create` via the existing
    //                IndexedDB action_queue, then give feedback and redirect.
    //                Note: receipt_image (file upload) is not supported offline
    //                and must be added when back online.

    var form = document.getElementById('create-expense-form');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        // If online, let the browser submit normally — do nothing.
        if (navigator.onLine) return;

        // ── We are OFFLINE ────────────────────────────────────────────────
        e.preventDefault();

        var data = new FormData(form);
        var payload = {};
        for (var pair of data.entries()) {
            var key = pair[0], value = pair[1];
            // Skip: CSRF token, method spoofing field, and file inputs
            if (key === '_token' || key === '_method') continue;
            if (value instanceof File) continue;
            payload[key] = value;
        }

        var hadReceiptImage = form.querySelector('[name="receipt_image"]') &&
                              form.querySelector('[name="receipt_image"]').files.length > 0;

        if (!window.WarmUpOffline || !window.WarmUpOffline.queueExpenseCreate) {
            console.error('[WarmUp Offline] queueExpenseCreate not available.');
            showOfflineBanner('Could not save expense offline. Please try again.', '#FEE2E2', '#DC2626');
            return;
        }

        try {
            await window.WarmUpOffline.queueExpenseCreate(payload);
        } catch (err) {
            console.error('[WarmUp Offline] Failed to queue expense_create:', err);
            showOfflineBanner('Could not save expense offline. Please try again.', '#FEE2E2', '#DC2626');
            return;
        }

        var msg = 'Expense saved offline and will sync automatically when you reconnect.';
        if (hadReceiptImage) {
            msg += ' Receipt image could not be saved offline — please add it when back online.';
        }

        showOfflineBanner(msg, '#DCFCE7', '#15803D');

        // Redirect to expenses list after a brief pause —
        // same destination as the online success redirect.
        setTimeout(function () {
            window.location.href = '{{ route('expenses.index') }}';
        }, 2500);
    });

    /**
     * Inject a dismissible inline banner above the form.
     * Matches the flash-message styling used by the app layout.
     */
    function showOfflineBanner(message, bgColor, textColor) {
        var existing = document.getElementById('offline-queue-banner');
        if (existing) existing.remove();

        var banner = document.createElement('div');
        banner.id = 'offline-queue-banner';
        banner.style.cssText = [
            'display:flex', 'align-items:center', 'gap:12px',
            'padding:14px 16px', 'border-radius:14px',
            'font-size:0.875rem', 'font-weight:500',
            'margin-bottom:16px',
            'background-color:' + bgColor,
            'color:' + textColor,
            'transition:opacity 0.5s ease',
        ].join(';');

        banner.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" ' +
            'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ' +
            'style="flex-shrink:0">' +
            '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>' +
            '<polyline points="22 4 12 13.01 9 10.01"/></svg>' +
            '<span>' + message + '</span>';

        // Insert before the form
        form.parentNode.insertBefore(banner, form);
        banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});
</script>
@endpush
