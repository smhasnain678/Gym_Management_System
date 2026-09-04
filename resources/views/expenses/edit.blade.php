@extends('layouts.app')

@section('title', __('Edit Expense'))
@section('meta_description', __('Update an existing expense.'))
@section('page_title', __('Edit Expense'))

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
    <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data" class="p-8">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Expense Title') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $expense->title) }}" required
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm"
                           style="--tw-ring-color: #22C55E;">
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Category') }} <span class="text-red-500">*</span></label>
                    <select name="expense_category_id" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm"
                            style="--tw-ring-color: #22C55E;">
                        <option value="">{{ __('Select Category') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('expense_category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Amount') }} <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0.01" required
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm"
                           style="--tw-ring-color: #22C55E;">
                    @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Date') }} <span class="text-red-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm"
                           style="--tw-ring-color: #22C55E;">
                    @error('expense_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Paid To / Recipient') }}</label>
                    <input type="text" name="paid_to" value="{{ old('paid_to', $expense->paid_to) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm"
                           style="--tw-ring-color: #22C55E;">
                    @error('paid_to')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Notes / Description') }}</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm resize-none"
                              style="--tw-ring-color: #22C55E;">{{ old('notes', $expense->notes) }}</textarea>
                    @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Receipt Image') }}</label>
                    @if($expense->receipt_image)
                        <div class="mb-3">
                            <p class="text-xs text-gray-500 mb-2">{{ __('Current Image:') }}</p>
                            <img src="{{ Storage::url($expense->receipt_image) }}" alt="Receipt" class="h-32 rounded-lg border border-gray-200 object-cover">
                        </div>
                    @endif
                    <input type="file" name="receipt_image" accept="image/*"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:border-transparent text-sm"
                           style="--tw-ring-color: #22C55E;">
                    <p class="text-xs text-gray-500 mt-1">{{ __('Upload a new image to replace the current one.') }}</p>
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
                    {{ __('Update Expense') }}
                </button>
            </div>

        </div>
    </form>
</div>

@endsection
