@extends('layouts.guest')

@section('title', __('Reset Password'))
@section('meta_description', __('Set a new password for your WarmUp gym dashboard.'))

@section('content')
<div>
    <h2 class="text-2xl font-bold mb-1" style="color: #111827;">{{ __('Set New Password') }}</h2>
    <p class="text-sm mb-6" style="color: #6B7280;">{{ __('Choose a strong new password for your account.') }}</p>

    @if ($errors->any())
        <div class="mb-4 flex items-start gap-3 p-4 rounded-2xl text-sm" style="background-color: #FEE2E2; color: #DC2626;">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        {{-- Hidden token and email --}}
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

        {{-- New Password --}}
        <div class="mb-4">
            <label for="password" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                {{ __('New Password') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #9CA3AF;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <input id="password"
                       name="password"
                       type="password"
                       required
                       autocomplete="new-password"
                       placeholder="{{ __('Minimum 8 characters') }}"
                       class="w-full pl-10 pr-4 py-3 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent
                              {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50 hover:border-gray-300' }}"
                       style="--tw-ring-color: #22C55E;">
            </div>
        </div>

        {{-- Confirm Password --}}
        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                {{ __('Confirm New Password') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #9CA3AF;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <input id="password_confirmation"
                       name="password_confirmation"
                       type="password"
                       required
                       autocomplete="new-password"
                       placeholder="{{ __('Repeat new password') }}"
                       class="w-full pl-10 pr-4 py-3 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent
                              border-gray-200 bg-gray-50 hover:border-gray-300"
                       style="--tw-ring-color: #22C55E;">
            </div>
        </div>

        <button type="submit"
                class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold text-white shadow-md
                       hover:shadow-lg active:scale-95"
                style="background: linear-gradient(135deg, #22C55E, #16A34A);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Reset Password
        </button>
    </form>
</div>
@endsection
