<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WarmUp') — Gym Management</title>
    <meta name="description" content="@yield('meta_description', 'WarmUp Gym Management System — manage members, attendance, fees and more.')">

    <!-- Inter Font from Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center" style="background-color: #F1F1F1; font-family: 'Inter', sans-serif;">

    <div class="w-full max-w-md px-4">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg"
                     style="background: linear-gradient(135deg, #22C55E, #16A34A);">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span class="text-3xl font-bold" style="color: #111827;">Warm<span style="color: #22C55E;">Up</span></span>
            </a>
            <p class="mt-2 text-sm" style="color: #6B7280;">Gym Management Dashboard</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
            <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #22C55E, #16A34A);"></div>
            <div class="p-8">
                @yield('content')
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs mt-6" style="color: #9CA3AF;">
            &copy; {{ date('Y') }} WarmUp Gym Management. All rights reserved.
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            // Auto-hide flash messages
            setTimeout(() => {
                const flashes = document.querySelectorAll('.flash-message');
                flashes.forEach(f => {
                    f.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    f.style.opacity = '0';
                    f.style.transform = 'translateY(-10px)';
                    setTimeout(() => f.remove(), 500);
                });
            }, 4500);
        });
    </script>
</body>
</html>
