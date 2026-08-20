@extends('layouts.app')

@section('title', 'Edit Member')
@section('meta_description', 'Update member profile information.')
@section('page_title', 'Edit Member')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Back --}}
    <a href="{{ route('members.show', $member) }}"
       class="inline-flex items-center gap-1.5 text-sm transition-colors hover:opacity-70"
       style="color:#22C55E;">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Back to Profile
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

    <form action="{{ route('members.update', $member) }}"
          method="POST"
          id="edit-member-form"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ── Personal Information ──────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-4 flex items-center gap-2" style="color:#111827;">
                <i data-lucide="user" class="w-4 h-4" style="color:#22C55E;"></i>
                Personal Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Profile Photo --}}
                <div class="md:col-span-2">
                    <label for="profile_photo" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        Profile Photo
                    </label>
                    @if($member->profile_photo)
                        <div class="mb-3 flex items-center gap-4">
                            <img src="{{ asset('storage/' . $member->profile_photo) }}" alt="Current Photo" class="w-16 h-16 rounded-full object-cover shadow-sm border border-gray-200">
                            <span class="text-sm text-gray-500">Current photo</span>
                        </div>
                    @endif
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/webp,image/jpg"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    <p class="text-xs mt-1" style="color:#6B7280;">Upload a new photo to replace the current one. Max size: 2MB. Formats: JPEG, PNG, WEBP.</p>
                </div>

                {{-- Name --}}
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        Full Name <span style="color:#DC2626;">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $member->name) }}" required maxlength="100"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('name') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        Phone <span style="color:#DC2626;">*</span>
                    </label>
                    <input type="text" id="phone" name="phone"
                           value="{{ old('phone', $member->phone) }}" required maxlength="20"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('phone') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        Email <span class="font-normal" style="color:#9CA3AF;">(optional)</span>
                    </label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', $member->email) }}" maxlength="150"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('email') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                </div>

                {{-- Gender --}}
                <div>
                    <label for="gender" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        Gender <span style="color:#DC2626;">*</span>
                    </label>
                    <select id="gender" name="gender" required
                            class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('gender') border-red-400 @else border-gray-200 @enderror"
                            style="color:#374151; --tw-ring-color:#22C55E;">
                        <option value="male"   {{ old('gender', $member->gender) === 'male'   ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $member->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other"  {{ old('gender', $member->gender) === 'other'  ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                {{-- Date of Birth --}}
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        Date of Birth <span class="font-normal" style="color:#9CA3AF;">(optional)</span>
                    </label>
                    <input type="date" id="date_of_birth" name="date_of_birth"
                           value="{{ old('date_of_birth', optional($member->date_of_birth)->format('Y-m-d')) }}"
                           max="{{ now()->subDay()->format('Y-m-d') }}"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('date_of_birth') border-red-400 @else border-gray-200 @enderror"
                           style="color:#374151; --tw-ring-color:#22C55E;">
                </div>

                {{-- Blood Group --}}
                <div>
                    <label for="blood_group" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        Blood Group <span class="font-normal" style="color:#9CA3AF;">(optional)</span>
                    </label>
                    <select id="blood_group" name="blood_group"
                            class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                            style="color:#374151; --tw-ring-color:#22C55E;">
                        <option value="">Select blood group</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group', $member->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Height --}}
                <div>
                    <label for="height" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        Height (cm) <span class="font-normal" style="color:#9CA3AF;">(optional)</span>
                    </label>
                    <input type="number" id="height" name="height"
                           value="{{ old('height', $member->height) }}" min="50" max="300" step="0.01"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('height') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                </div>

                {{-- Weight --}}
                <div>
                    <label for="weight" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        Weight (kg) <span class="font-normal" style="color:#9CA3AF;">(optional)</span>
                    </label>
                    <input type="number" id="weight" name="weight"
                           value="{{ old('weight', $member->weight) }}" min="10" max="500" step="0.01"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('weight') border-red-400 @else border-gray-200 @enderror"
                           style="--tw-ring-color:#22C55E;">
                </div>
            </div>
        </div>

        {{-- ── Status Info ────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-4 flex items-center gap-2" style="color:#111827;">
                <i data-lucide="activity" class="w-4 h-4" style="color:#22C55E;"></i>
                Account Status
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Joining Date --}}
                <div>
                    <label for="joining_date" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        Joining Date <span style="color:#DC2626;">*</span>
                    </label>
                    <input type="date" id="joining_date" name="joining_date"
                           value="{{ old('joining_date', $member->joining_date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('joining_date') border-red-400 @else border-gray-200 @enderror"
                           style="color:#374151; --tw-ring-color:#22C55E;">
                </div>

                {{-- Status --}}
                <div>
                    <label for="status" class="block text-sm font-medium mb-1.5" style="color:#374151;">
                        Status <span style="color:#DC2626;">*</span>
                    </label>
                    <select id="status" name="status" required
                            class="w-full px-4 py-2.5 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent @error('status') border-red-400 @else border-gray-200 @enderror"
                            style="color:#374151; --tw-ring-color:#22C55E;">
                        <option value="active"        {{ old('status', $member->status) === 'active'        ? 'selected' : '' }}>Active</option>
                        <option value="suspended"     {{ old('status', $member->status) === 'suspended'     ? 'selected' : '' }}>Suspended</option>
                        <option value="expired"       {{ old('status', $member->status) === 'expired'       ? 'selected' : '' }}>Expired</option>
                        <option value="expiring_soon" {{ old('status', $member->status) === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                    </select>
                </div>
            </div>
        </div>


        {{-- ── Contact & Medical ──────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-4 flex items-center gap-2" style="color:#111827;">
                <i data-lucide="phone" class="w-4 h-4" style="color:#22C55E;"></i>
                Contact & Medical
                <span class="text-xs font-normal ml-1" style="color:#9CA3AF;">(optional)</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium mb-1.5" style="color:#374151;">Address</label>
                    <textarea id="address" name="address" rows="2" maxlength="500"
                              class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:border-transparent"
                              style="--tw-ring-color:#22C55E;">{{ old('address', $member->address) }}</textarea>
                </div>

                <div>
                    <label for="emergency_contact_name" class="block text-sm font-medium mb-1.5" style="color:#374151;">Emergency Contact Name</label>
                    <input type="text" id="emergency_contact_name" name="emergency_contact_name"
                           value="{{ old('emergency_contact_name', $member->emergency_contact_name) }}" maxlength="100"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color:#22C55E;">
                </div>

                <div>
                    <label for="emergency_contact_phone" class="block text-sm font-medium mb-1.5" style="color:#374151;">Emergency Contact Phone</label>
                    <input type="text" id="emergency_contact_phone" name="emergency_contact_phone"
                           value="{{ old('emergency_contact_phone', $member->emergency_contact_phone) }}" maxlength="20"
                           class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color:#22C55E;">
                </div>

                <div class="md:col-span-2">
                    <label for="medical_notes" class="block text-sm font-medium mb-1.5" style="color:#374151;">Medical Notes</label>
                    <textarea id="medical_notes" name="medical_notes" rows="2" maxlength="1000"
                              class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:border-transparent"
                              style="--tw-ring-color:#22C55E;">{{ old('medical_notes', $member->medical_notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Submit ─────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('members.show', $member) }}"
               class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition-colors"
               style="color:#374151;">
                Cancel
            </a>
            <button type="submit"
                    id="btn-update-member"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white rounded-xl transition-all hover:shadow-md active:scale-95"
                    style="background-color:#22C55E;">
                <i data-lucide="save" class="w-4 h-4"></i>
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
