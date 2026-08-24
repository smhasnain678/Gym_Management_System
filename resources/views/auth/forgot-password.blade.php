@extends('layouts.guest')

@section('title', 'Forgot Password')
@section('meta_description', 'Reset your WarmUp gym dashboard password.')

@section('content')
<div>
    <a href="{{ route('login') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium mb-5 hover:underline"
       style="color: #22C55E;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Login
    </a>

    <h2 class="text-2xl font-bold mb-1" style="color: #111827;">Forgot Password?</h2>
    <p class="text-sm mb-6" style="color: #6B7280;">
        Enter your registered email and we'll generate a secure reset link for you.
    </p>

    @if ($errors->any())
        <div class="mb-4 flex items-start gap-3 p-4 rounded-2xl text-sm" style="background-color: #FEE2E2; color: #DC2626;">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
        </div>
    @endif

    @if (session('status'))
        <div class="flash-message mb-4 font-medium text-sm p-4 rounded-xl"
             style="background-color: #DCFCE7; color: #15803D;">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-5">
            <label for="email" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                Registered Email
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #9CA3AF;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                </div>
                <input id="email"
                       name="email"
                       type="email"
                       value="{{ old('email') }}"
                       required
                       autocomplete="email"
                       placeholder="owner@mygym.com"
                       class="w-full pl-10 pr-4 py-3 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent
                              {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50 hover:border-gray-300' }}"
                       style="--tw-ring-color: #22C55E;">
            </div>
        </div>

        <button type="submit"
                class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold text-white shadow-md
                       hover:shadow-lg active:scale-95"
                style="background: linear-gradient(135deg, #22C55E, #16A34A);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            Generate Reset Link
        </button>
    </form>
</div>
@endsection
