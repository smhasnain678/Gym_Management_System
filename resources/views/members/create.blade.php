@extends('layouts.app')

@section('title', __('Add Member'))
@section('meta_description', __('Register a new gym member.'))
@section('page_title', __('Add Member'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Back --}}
    <a href="{{ route('members.index') }}"
       class="inline-flex items-center gap-1.5 text-sm transition-colors hover:opacity-70"
       style="color:#22C55E;">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        {{ __('Back to Members') }}
    </a>

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="p-4 rounded-xl text-sm flex items-start gap-2"
             style="background-color:#FEF2F2; color:#DC2626;">
            <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
            <ul class="list-disc pl-1 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('members.store') }}"
          method="POST"
          id="create-member-form"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf

        {{-- ── Personal Information ──────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-4 flex items-center gap-2" style="color:#111827;">
                <i data-lucide="user" class="w-4 h-4" style="color:#22C55E;"></i>
                {{ __('Personal Information') }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Profile Photo --}}
                <div class="md:col-span-2">
                    <label for="profile_photo" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Profile Photo (Optional)') }}
                    </label>
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/webp,image/jpg"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    <p class="text-xs mt-1" style="color:#6B7280;">{{ __('Max size: 2MB. Formats: JPEG, PNG, WEBP.') }}</p>
                </div>

                {{-- Name --}}
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Full Name') }} <span style="color:#DC2626;">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name') }}" required maxlength="100"
                           placeholder="{{ __('e.g. Ahmed Hassan') }}"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('name') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                    @error('name') <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p> @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Phone') }} <span style="color:#DC2626;">*</span>
                    </label>
                    <input type="text" id="phone" name="phone"
                           value="{{ old('phone') }}" required maxlength="20"
                           placeholder="03001234567"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('phone') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                    @error('phone') <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Email') }} <span class="font-normal" style="color:#9CA3AF;">{{ __('(optional)') }}</span>
                    </label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}" maxlength="150"
                           placeholder="member@example.com"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('email') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                    @error('email') <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p> @enderror
                </div>

                {{-- Gender --}}
                <div>
                    <label for="gender" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Gender') }} <span style="color:#DC2626;">*</span>
                    </label>
                    <select id="gender" name="gender" required
                            class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('gender') border-red-400 @else border-gray-200 @enderror"
                            style="color:#374151; --tw-ring-color:#22C55E;">
                        <option value="">{{ __('Select gender') }}</option>
                        <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>{{ __('Male') }}</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                        <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>{{ __('Other') }}</option>
                    </select>
                    @error('gender') <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p> @enderror
                </div>

                {{-- Date of Birth --}}
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Date of Birth') }} <span class="font-normal" style="color:#9CA3AF;">{{ __('(optional)') }}</span>
                    </label>
                    <input type="date" id="date_of_birth" name="date_of_birth"
                           value="{{ old('date_of_birth') }}"
                           max="{{ now()->subDay()->format('Y-m-d') }}"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('date_of_birth') border-red-400 @else border-gray-200 @enderror"
                           style="color:#374151; --tw-ring-color:#22C55E;">
                    @error('date_of_birth') <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p> @enderror
                </div>

                {{-- Blood Group --}}
                <div>
                    <label for="blood_group" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Blood Group') }} <span class="font-normal" style="color:#9CA3AF;">{{ __('(optional)') }}</span>
                    </label>
                    <select id="blood_group" name="blood_group"
                            class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                            style="color:#374151; --tw-ring-color:#22C55E;">
                        <option value="">{{ __('Select blood group') }}</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Height --}}
                <div>
                    <label for="height" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Height (cm)') }} <span class="font-normal" style="color:#9CA3AF;">{{ __('(optional)') }}</span>
                    </label>
                    <input type="number" id="height" name="height"
                           value="{{ old('height') }}" min="50" max="300" step="0.01"
                           placeholder="{{ __('e.g. 175.00') }}"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('height') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                    @error('height') <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p> @enderror
                </div>

                {{-- Weight --}}
                <div>
                    <label for="weight" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Weight (kg)') }} <span class="font-normal" style="color:#9CA3AF;">{{ __('(optional)') }}</span>
                    </label>
                    <input type="number" id="weight" name="weight"
                           value="{{ old('weight') }}" min="10" max="500" step="0.01"
                           placeholder="{{ __('e.g. 70.00') }}"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('weight') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                    @error('weight') <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ── Membership Info ────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-4 flex items-center gap-2" style="color:#111827;">
                <i data-lucide="calendar" class="w-4 h-4" style="color:#22C55E;"></i>
                {{ __('Membership Details') }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Joining Date --}}
                <div>
                    <label for="joining_date" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Joining Date') }} <span style="color:#DC2626;">*</span>
                    </label>
                    <input type="date" id="joining_date" name="joining_date"
                           value="{{ old('joining_date', now()->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('joining_date') border-red-400 @else border-gray-200 @enderror"
                           style="color:#374151; --tw-ring-color:#22C55E;">
                    @error('joining_date') <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p> @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label for="status" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Status') }} <span style="color:#DC2626;">*</span>
                    </label>
                    <select id="status" name="status" required
                            class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('status') border-red-400 @else border-gray-200 @enderror"
                            style="color:#374151; --tw-ring-color:#22C55E;">
                        <option value="active"        {{ old('status','active') === 'active'        ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="suspended"     {{ old('status','active') === 'suspended'     ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                        <option value="expired"       {{ old('status','active') === 'expired'       ? 'selected' : '' }}>{{ __('Expired') }}</option>
                        <option value="expiring_soon" {{ old('status','active') === 'expiring_soon' ? 'selected' : '' }}>{{ __('Expiring Soon') }}</option>
                    </select>
                    @error('status') <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ── Optional: Assign Membership Plan now ─────────────────────── --}}
        @if($plans->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-1 flex items-center gap-2" style="color:#111827;">
                <i data-lucide="layers" class="w-4 h-4" style="color:#22C55E;"></i>
                {{ __('Assign Membership Plan') }}
                <span class="text-xs font-normal ml-1" style="color:#9CA3AF;">{{ __('(optional)') }}</span>
            </h3>
            <p class="text-xs mb-4" style="color:#9CA3AF;">{{ __('You can also assign a plan later from the member profile.') }}</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Plan select --}}
                <div class="md:col-span-2">
                    <label for="membership_plan_id" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Membership Plan') }}
                    </label>
                    <select id="membership_plan_id" name="membership_plan_id"
                            onchange="updatePlanPrice(this)"
                            class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                            style="color:#374151; --tw-ring-color:#22C55E;">
                        <option value="">{{ __('— No plan —') }}</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}"
                                    data-price="{{ $plan->price }}"
                                    data-duration="{{ $plan->duration_days }}"
                                    {{ old('membership_plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }}
                                ({{ $plan->duration_days }} {{ __('days') }} — {{ number_format($plan->price, 2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('membership_plan_id') <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p> @enderror
                </div>

                {{-- Plan start date --}}
                <div>
                    <label for="membership_start_date" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Plan Start Date') }}
                    </label>
                    <input type="date" id="membership_start_date" name="membership_start_date"
                           value="{{ old('membership_start_date', now()->format('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('membership_start_date') border-red-400 @else border-gray-200 @enderror"
                           style="color:#374151; --tw-ring-color:#22C55E;">
                    @error('membership_start_date') <p class="text-xs mt-1" style="color:#DC2626;">{{ $message }}</p> @enderror
                </div>

                {{-- Paid amount --}}
                <div>
                    <label for="paid_amount" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Amount Paid Now') }}
                    </label>
                    <input type="number" id="paid_amount" name="paid_amount"
                           value="{{ old('paid_amount', 0) }}" min="0" step="0.01"
                           placeholder="0.00"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color:#22C55E;">
                    <p id="plan-price-hint" class="text-xs mt-1" style="color:#9CA3AF;"></p>
                </div>

                {{-- Membership notes --}}
                <div class="md:col-span-2">
                    <label for="membership_notes" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        {{ __('Notes') }} <span class="font-normal" style="color:#9CA3AF;">{{ __('(optional)') }}</span>
                    </label>
                    <textarea id="membership_notes" name="membership_notes" rows="2"
                              class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:border-transparent"
                              style="--tw-ring-color:#22C55E;">{{ old('membership_notes') }}</textarea>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Contact & Medical ──────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-4 flex items-center gap-2" style="color:#111827;">
                <i data-lucide="phone" class="w-4 h-4" style="color:#22C55E;"></i>
                {{ __('Contact & Medical') }}
                <span class="text-xs font-normal ml-1" style="color:#9CA3AF;">{{ __('(optional)') }}</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Address') }}</label>
                    <textarea id="address" name="address" rows="2" maxlength="500"
                              class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:border-transparent"
                              style="--tw-ring-color:#22C55E;">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label for="emergency_contact_name" class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Emergency Contact Name') }}</label>
                    <input type="text" id="emergency_contact_name" name="emergency_contact_name"
                           value="{{ old('emergency_contact_name') }}" maxlength="100"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color:#22C55E;">
                </div>

                <div>
                    <label for="emergency_contact_phone" class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Emergency Contact Phone') }}</label>
                    <input type="text" id="emergency_contact_phone" name="emergency_contact_phone"
                           value="{{ old('emergency_contact_phone') }}" maxlength="20"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color:#22C55E;">
                </div>

                <div class="md:col-span-2">
                    <label for="medical_notes" class="block text-sm font-medium mb-1.5" style="color:#374151;">{{ __('Medical Notes') }}</label>
                    <textarea id="medical_notes" name="medical_notes" rows="2" maxlength="1000"
                              placeholder="{{ __('Any relevant medical conditions or notes…') }}"
                              class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:border-transparent"
                              style="--tw-ring-color:#22C55E;">{{ old('medical_notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Submit ─────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('members.index') }}"
               class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition-colors"
               style="color:#374151;">
                {{ __('Cancel') }}
            </a>
            <button type="submit"
                    id="btn-register-member"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white rounded-xl transition-all hover:shadow-md active:scale-95"
                    style="background-color:#22C55E;">
                <i data-lucide="user-check" class="w-4 h-4"></i>
                {{ __('Register Member') }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function updatePlanPrice(select) {
        const option = select.options[select.selectedIndex];
        const price = option.dataset.price;
        const duration = option.dataset.duration;
        const hint = document.getElementById('plan-price-hint');
        if (price) {
            hint.textContent = 'Plan price: ' + parseFloat(price).toFixed(2) + ' for ' + duration + ' days';
        } else {
            hint.textContent = '';
        }
    }
    // Init on load if old() value was present
    document.addEventListener('DOMContentLoaded', function() {
        const sel = document.getElementById('membership_plan_id');
        if (sel) updatePlanPrice(sel);
    });
</script>
@endpush
