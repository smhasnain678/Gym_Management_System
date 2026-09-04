@extends('layouts.app')

@section('title', __('My Profile'))
@section('meta_description', __('Manage your WarmUp Gym Owner profile and account security.'))
@section('page_title', __('My Profile'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Profile Info Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="h-1" style="background: linear-gradient(90deg, #22C55E, #16A34A);"></div>
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: #DCFCE7;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #22C55E;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold" style="color: #111827;">{{ __('Profile Information') }}</h2>
                    <p class="text-sm" style="color: #6B7280;">{{ __('Update your name, email and contact details.') }}</p>
                </div>
            </div>

            @if ($errors->updateProfile->any())
                <div class="mb-4 flex items-start gap-3 p-4 rounded-2xl text-sm" style="background-color: #FEE2E2; color: #DC2626;">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>@foreach ($errors->updateProfile->all() as $e)<p>{{ $e }}</p>@endforeach</div>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium mb-1.5" style="color: #374151;">{{ __('Full Name') }}</label>
                        <input id="name" name="name" type="text"
                               value="{{ old('name', $user->name) }}"
                               required
                               class="w-full px-4 py-3 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent
                                      {{ $errors->updateProfile->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }}"
                               style="--tw-ring-color: #22C55E;">
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label for="phone" class="block text-sm font-medium mb-1.5" style="color: #374151;">{{ __('Phone Number') }}</label>
                        <input id="phone" name="phone" type="tel"
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="+92 300 0000000"
                               class="w-full px-4 py-3 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent
                                      {{ $errors->updateProfile->has('phone') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }}"
                               style="--tw-ring-color: #22C55E;">
                    </div>

                    {{-- Email --}}
                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-medium mb-1.5" style="color: #374151;">{{ __('Email Address') }}</label>
                        <input id="email" name="email" type="email"
                               value="{{ old('email', $user->email) }}"
                               required
                               class="w-full px-4 py-3 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent
                                      {{ $errors->updateProfile->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }}"
                               style="--tw-ring-color: #22C55E;">
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                            class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow
                                   hover:shadow-md active:scale-95"
                            style="background: linear-gradient(135deg, #22C55E, #16A34A);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Change Password Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="h-1" style="background: linear-gradient(90deg, #F59E0B, #D97706);"></div>
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: #FEF3C7;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #D97706;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold" style="color: #111827;">{{ __('Change Password') }}</h2>
                    <p class="text-sm" style="color: #6B7280;">{{ __('Make sure your password is strong and unique.') }}</p>
                </div>
            </div>

            @if ($errors->updatePassword->any())
                <div class="mb-4 flex items-start gap-3 p-4 rounded-2xl text-sm" style="background-color: #FEE2E2; color: #DC2626;">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>@foreach ($errors->updatePassword->all() as $e)<p>{{ $e }}</p>@endforeach</div>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium mb-1.5" style="color: #374151;">{{ __('Current Password') }}</label>
                        <input id="current_password" name="current_password" type="password"
                               required
                               placeholder="••••••••"
                               class="w-full px-4 py-3 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent
                                      {{ $errors->updatePassword->has('current_password') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }}"
                               style="--tw-ring-color: #22C55E;">
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-medium mb-1.5" style="color: #374151;">{{ __('New Password') }}</label>
                        <input id="new_password" name="password" type="password"
                               required
                               placeholder="{{ __('Minimum 8 characters') }}"
                               class="w-full px-4 py-3 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent
                                      {{ $errors->updatePassword->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }}"
                               style="--tw-ring-color: #22C55E;">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium mb-1.5" style="color: #374151;">{{ __('Confirm New Password') }}</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               required
                               placeholder="{{ __('Repeat new password') }}"
                               class="w-full px-4 py-3 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent border-gray-200 bg-gray-50"
                               style="--tw-ring-color: #22C55E;">
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                            class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow hover:shadow-md active:scale-95"
                            style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        {{ __('Update Password') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
