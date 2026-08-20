@extends('layouts.app')

@section('title', 'Edit Trainer')
@section('page_title', 'Edit Trainer')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('trainers.show', $trainer) }}" class="inline-flex items-center gap-1.5 text-sm transition-colors hover:opacity-70" style="color: #22C55E;">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Profile
        </a>
    </div>

    <form action="{{ route('trainers.update', $trainer) }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-8">
        @csrf
        @method('PATCH')

        @if($errors->any())
            <div class="p-4 rounded-xl text-sm flex items-start gap-2" style="background-color: #FEF2F2; color: #DC2626;">
                <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                <ul class="list-disc pl-1 space-y-0.5">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Basic Information --}}
        <div>
            <h2 class="text-base font-bold mb-4 flex items-center gap-2" style="color: #111827;">
                <i data-lucide="user" class="w-5 h-5 text-gray-400"></i> Basic Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-gray-700">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $trainer->name) }}" required
                           class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-gray-700">Phone <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $trainer->phone) }}" required
                           class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-gray-700">Email (Optional)</label>
                    <input type="email" name="email" value="{{ old('email', $trainer->email) }}"
                           class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-gray-700">Gender <span class="text-red-500">*</span></label>
                    <select name="gender" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white transition-colors">
                        <option value="">— Select Gender —</option>
                        <option value="male" {{ old('gender', $trainer->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $trainer->gender) == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $trainer->gender) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-gray-700">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $trainer->date_of_birth?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white transition-colors">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1.5 text-gray-700">Address</label>
                    <input type="text" name="address" value="{{ old('address', $trainer->address) }}"
                           class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white transition-colors">
                </div>
            </div>
        </div>

        <hr class="border-gray-100">

        {{-- Professional Details --}}
        <div>
            <h2 class="text-base font-bold mb-4 flex items-center gap-2" style="color: #111827;">
                <i data-lucide="briefcase" class="w-5 h-5 text-gray-400"></i> Professional Details
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-gray-700">Specialization</label>
                    <input type="text" name="specialization" value="{{ old('specialization', $trainer->specialization) }}" placeholder="e.g. Yoga, Weightlifting"
                           class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-gray-700">Joining Date <span class="text-red-500">*</span></label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', $trainer->joining_date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-gray-700">Salary / Rate (Optional)</label>
                    <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary', $trainer->salary) }}"
                           class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white transition-colors">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1.5 text-gray-700">Bio / Notes</label>
                    <textarea name="bio" rows="3"
                              class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white transition-colors resize-none">{{ old('bio', $trainer->bio) }}</textarea>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $trainer->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 text-green-500 border-gray-300 rounded focus:ring-green-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Active Trainer</label>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex justify-end gap-3">
            <a href="{{ route('trainers.show', $trainer) }}" class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition-colors" style="color: #374151;">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95" style="background-color: #22C55E;">
                Save Changes
            </button>
        </div>

    </form>
</div>
@endsection
