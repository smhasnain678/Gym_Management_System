@extends('layouts.app')

@section('title', __('Edit Membership Plan'))
@section('meta_description', __('Edit an existing membership plan template.'))
@section('page_title', __('Edit Membership Plan'))

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Back link --}}
    <a href="{{ route('membership-plans.index') }}"
       class="inline-flex items-center gap-1.5 text-sm mb-6 transition-colors hover:opacity-70"
       style="color:#22C55E;">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        {{ __('Back to Plans') }}
    </a>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Card header --}}
        <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3"
             style="background: linear-gradient(135deg,#22C55E10,#16A34A08);">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                 style="background:linear-gradient(135deg,#22C55E,#16A34A);">
                <i data-lucide="pencil" class="w-5 h-5 text-white"></i>
            </div>
            <div class="min-w-0">
                <h2 class="text-lg font-semibold truncate" style="color:#111827;">
                    Edit: {{ $membershipPlan->name }}
                </h2>
                <p class="text-xs" style="color:#6B7280;">
                    {{ __('Update plan details. Changes take effect immediately.') }}
                </p>
            </div>
        </div>

        {{-- Plan has member assignments warning --}}
        @if($membershipPlan->memberMemberships()->exists())
            <div class="mx-6 mt-5 p-4 rounded-xl text-sm flex items-start gap-2"
                 style="background-color:#FFFBEB; color:#B45309;">
                <i data-lucide="alert-triangle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                <div>
                    <strong>{{ __('This plan is assigned to members.') }}</strong>
                    {{ __('Editing price or duration will not affect existing active memberships, only new ones.') }}
                </div>
            </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="mx-6 mt-5 p-4 rounded-xl text-sm" style="background-color:#FEF2F2; color:#DC2626;">
                <div class="flex items-start gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                    <ul class="list-disc pl-1 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('membership-plans.update', $membershipPlan) }}"
              method="POST"
              class="p-6 space-y-5"
              id="edit-plan-form">
            @csrf
            @method('PUT')

            {{-- Plan Name --}}
            <div>
                <label for="name" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                    {{ __('Plan Name') }} <span style="color:#DC2626;">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $membershipPlan->name) }}"
                       required
                       maxlength="100"
                       class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl
                              focus:outline-none focus:ring-2 focus:border-transparent
                              @error('name') border-red-400 @else border-gray-200 @enderror"
                       style="--tw-ring-color:#22C55E;">
                @error('name')
                    <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Duration & Price --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="duration_days" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Duration (days)') }} <span style="color:#DC2626;">*</span>
                    </label>
                    <input type="number"
                           id="duration_days"
                           name="duration_days"
                           value="{{ old('duration_days', $membershipPlan->duration_days) }}"
                           required
                           min="1"
                           max="3650"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl
                                  focus:outline-none focus:ring-2 focus:border-transparent
                                  @error('duration_days') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                    <p class="text-xs mt-1" style="color:#9CA3AF;">{{ __('30 = 1 month · 90 = 3 months · 365 = 1 year') }}</p>
                    @error('duration_days')
                        <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Price') }} <span style="color:#DC2626;">*</span>
                    </label>
                    <input type="number"
                           id="price"
                           name="price"
                           value="{{ old('price', $membershipPlan->price) }}"
                           required
                           min="0"
                           step="0.01"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl
                                  focus:outline-none focus:ring-2 focus:border-transparent
                                  @error('price') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                    @error('price')
                        <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                    {{ __('Description') }} <span class="font-normal" style="color:#9CA3AF;">({{ __('optional') }})</span>
                </label>
                <textarea id="description"
                          name="description"
                          rows="3"
                          maxlength="1000"
                          placeholder="{{ __('Brief description of what this plan includes…') }}"
                          class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl resize-none
                                 focus:outline-none focus:ring-2 focus:border-transparent
                                 @error('description') border-red-400 @else border-gray-200 @enderror"
                          style="--tw-ring-color:#22C55E;">{{ old('description', $membershipPlan->description) }}</textarea>
                @error('description')
                    <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Color & Sort Order --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="color" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Badge Color') }} <span class="font-normal" style="color:#9CA3AF;">({{ __('optional') }})</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color"
                               id="color-picker"
                               value="{{ old('color', $membershipPlan->color ?? '#22C55E') }}"
                               class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5"
                               oninput="document.getElementById('color').value = this.value;
                                        document.getElementById('color-preview').style.backgroundColor = this.value;">
                        <input type="text"
                               id="color"
                               name="color"
                               value="{{ old('color', $membershipPlan->color) }}"
                               placeholder="#22C55E"
                               maxlength="20"
                               class="flex-1 px-4 py-2.5 text-sm bg-gray-50 border rounded-xl
                                      focus:outline-none focus:ring-2 focus:border-transparent
                                      @error('color') border-red-400 @else border-gray-200 @enderror"
                               style="--tw-ring-color:#22C55E;"
                               oninput="document.getElementById('color-picker').value = this.value;
                                        document.getElementById('color-preview').style.backgroundColor = this.value;">
                    </div>
                    <div id="color-preview"
                         class="mt-2 h-2 rounded-full"
                         style="background-color:{{ old('color', $membershipPlan->color ?? '#22C55E') }};"></div>
                    @error('color')
                        <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Sort Order') }} <span class="font-normal" style="color:#9CA3AF;">({{ __('optional') }})</span>
                    </label>
                    <input type="number"
                           id="sort_order"
                           name="sort_order"
                           value="{{ old('sort_order', $membershipPlan->sort_order) }}"
                           min="0"
                           max="9999"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl
                                  focus:outline-none focus:ring-2 focus:border-transparent
                                  @error('sort_order') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                    <p class="text-xs mt-1" style="color:#9CA3AF;">{{ __('Lower number = shown first.') }}</p>
                    @error('sort_order')
                        <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Active toggle --}}
            <div class="flex items-center justify-between p-4 rounded-xl" style="background-color:#F9FAFB;">
                <div>
                    <p class="text-sm font-medium" style="color:#374151;">{{ __('Active Status') }}</p>
                    <p class="text-xs" style="color:#9CA3AF;">{{ __('Inactive plans are hidden from member assignment.') }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox"
                           id="is_active"
                           name="is_active"
                           value="1"
                           class="sr-only peer"
                           {{ old('is_active', $membershipPlan->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                                peer-checked:after:translate-x-full peer-checked:after:border-white
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:border-gray-300 after:border after:rounded-full
                                after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                </label>
            </div>

            {{-- Metadata strip --}}
            <div class="text-xs flex items-center gap-4 pt-1" style="color:#9CA3AF;">
                <span>{{ __('Created:') }} {{ $membershipPlan->created_at->gymDateFormat() }}</span>
                <span>{{ __('Last updated:') }} {{ $membershipPlan->updated_at->gymDateTimeFormat() }}</span>
                <span>{{ __('Assignments:') }} {{ $membershipPlan->memberMemberships()->count() }}</span>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between pt-2">

                {{-- Danger zone: delete --}}
                @if(!$membershipPlan->memberMemberships()->exists())
                    <form action="{{ route('membership-plans.destroy', $membershipPlan) }}"
                          method="POST"
                          onsubmit="return confirm('Delete plan \'{{ addslashes($membershipPlan->name) }}\'?\nThis cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                id="btn-delete-plan"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium
                                       rounded-xl border transition-colors"
                                style="border-color:#FCA5A5; color:#DC2626; background-color:#FFF7F7;">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            {{ __('Delete Plan') }}
                        </button>
                    </form>
                @else
                    <div></div>
                @endif

                <div class="flex items-center gap-3">
                    <a href="{{ route('membership-plans.index') }}"
                       class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200
                              bg-white hover:bg-gray-50 transition-colors"
                       style="color:#374151;">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                            id="btn-update-plan"
                            class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold
                                   text-white rounded-xl transition-all hover:shadow-md active:scale-95"
                            style="background-color:#22C55E;">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
