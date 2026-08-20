@extends('layouts.guest')

@section('title', 'Login')
@section('meta_description', 'Sign in to your WarmUp Gym Management Dashboard.')

@section('content')
<div>
    <h2 class="text-2xl font-bold mb-1" style="color: #111827;">Welcome back!</h2>
    <p class="text-sm mb-6" style="color: #6B7280;">Sign in to your gym dashboard.</p>

    {{-- Session Errors --}}
    @if ($errors->any())
        <div class="mb-4 flex items-start gap-3 p-4 rounded-2xl text-sm" style="background-color: #FEE2E2; color: #DC2626;">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    @if (session('status'))
        <div class="mb-4 flex items-center gap-3 p-4 rounded-2xl text-sm" style="background-color: #DCFCE7; color: #15803D;">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <form id="login-form" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                Email Address
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

        {{-- Password --}}
        <div class="mb-4">
            <label for="password" class="block text-sm font-medium mb-1.5" style="color: #374151;">
                Password
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
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="w-full pl-10 pr-12 py-3 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:border-transparent
                              {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50 hover:border-gray-300' }}"
                       style="--tw-ring-color: #22C55E;">
                {{-- Toggle password visibility --}}
                <button type="button"
                        id="toggle-password"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center"
                        style="color: #9CA3AF;">
                    <svg id="eye-icon" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Remember Me + Forgot --}}
        <div class="flex items-center justify-between mb-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox"
                       name="remember"
                       id="remember"
                       class="w-4 h-4 rounded border-gray-300 text-green-500"
                       style="accent-color: #22C55E;">
                <span class="text-sm" style="color: #6B7280;">Remember me</span>
            </label>
            <a href="{{ route('password.request') }}"
               class="text-sm font-medium hover:underline"
               style="color: #22C55E;">
                Forgot password?
            </a>
        </div>

        {{-- Submit Button --}}
        <button id="login-btn"
                type="submit"
                class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold text-white shadow-md
                       hover:shadow-lg active:scale-95"
                style="background: linear-gradient(135deg, #22C55E, #16A34A);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            Sign In to Dashboard
        </button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle password visibility
        const toggleBtn = document.getElementById('toggle-password');
        const pwdInput = document.getElementById('password');
        if (toggleBtn && pwdInput) {
            toggleBtn.addEventListener('click', function () {
                pwdInput.type = pwdInput.type === 'password' ? 'text' : 'password';
            });
        }

        // Prevent double-submit
        const form = document.getElementById('login-form');
        const btn = document.getElementById('login-btn');
        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Signing in...';
            });
        }
    });
</script>
@endsection
