<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ur', 'sd']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WarmUp') — Gym Management</title>
    <meta name="description" content="@yield('meta_description', 'WarmUp Gym Management Dashboard')">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    {{-- Gym branding CSS custom properties — applied from saved GymSetting values --}}
    <style>
        :root {
            --gym-primary:   {{ $gymSettings?->primary_color ?? '#22C55E' }};
            --gym-secondary: {{ $gymSettings?->secondary_color ?? '#16A34A' }};
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden" style="background-color: #F1F1F1; font-family: 'Inter', sans-serif; color: #111827;">

    <!-- ===== SIDEBAR ===== -->
    <aside id="sidebar"
           class="flex-shrink-0 flex flex-col w-64 h-full shadow-xl transition-transform duration-300 z-30"
           style="background-color: #0F172A;">

        <!-- Logo -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-700">
            @if(!empty($gymSettings?->gym_logo))
                {{-- Custom uploaded logo --}}
                <img src="{{ asset('storage/' . $gymSettings->gym_logo) }}"
                     alt="{{ $gymSettings->gym_name ?? 'Gym Logo' }}"
                     class="w-9 h-9 rounded-xl object-cover flex-shrink-0">
            @else
                {{-- Default icon fallback --}}
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background: linear-gradient(135deg, #22C55E, #16A34A);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            @endif
            {{-- Two-colour gym name based on brand split position --}}
            @php
                $gymName = $gymSettings?->gym_name ?? 'WarmUp';
                $splitPos = $gymSettings?->brand_split_position;
                if (is_null($splitPos)) {
                    $splitPos = strpos($gymName, ' ') !== false ? strpos($gymName, ' ') : (strtolower($gymName) === 'warmup' ? 4 : mb_strlen($gymName));
                }
                
                $firstPart = mb_substr($gymName, 0, $splitPos);
                $secondPart = mb_substr($gymName, $splitPos);
            @endphp
            <span class="text-xl font-bold">
                <span style="color: var(--gym-primary);">{{ $firstPart }}</span>@if(mb_strlen($secondPart) > 0)<span style="color: var(--gym-secondary);">{{ $secondPart }}</span>@endif
            </span>
        </div>


        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 sidebar-scrollbar">
            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-widest" style="color: #64748B;">{{ __('Main Menu') }}</p>

            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium group
                      {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
               style="{{ request()->routeIs('dashboard') ? 'background-color: #22C55E;' : '' }}">
                <i data-lucide="layout-dashboard" class="w-4.5 h-4.5 flex-shrink-0"></i>
                {{ __('Dashboard') }}
            </a>

            <a href="{{ route('membership-plans.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                      {{ request()->routeIs('membership-plans.*') ? 'text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
               style="{{ request()->routeIs('membership-plans.*') ? 'background-color: #22C55E;' : '' }}">
                <i data-lucide="layers" class="w-4.5 h-4.5 flex-shrink-0"></i>
                {{ __('Membership Plans') }}
            </a>

            <a href="{{ route('members.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                      {{ request()->routeIs('members.*') ? 'text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
               style="{{ request()->routeIs('members.*') ? 'background-color: #22C55E;' : '' }}">
                <i data-lucide="users" class="w-4.5 h-4.5 flex-shrink-0"></i>
                {{ __('Members') }}
            </a>

            <a href="{{ route('trainers.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                      {{ request()->routeIs('trainers.*') ? 'text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
               style="{{ request()->routeIs('trainers.*') ? 'background-color: #22C55E;' : '' }}">
                <i data-lucide="dumbbell" class="w-4.5 h-4.5 flex-shrink-0"></i>
                {{ __('Trainers') }}
            </a>

            <a href="{{ route('attendances.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                      {{ request()->routeIs('attendances.*') ? 'text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
               style="{{ request()->routeIs('attendances.*') ? 'background-color: #22C55E;' : '' }}">
                <i data-lucide="calendar-check" class="w-4.5 h-4.5 flex-shrink-0"></i>
                {{ __('Attendance') }}
            </a>

            <a href="{{ route('fees.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                      {{ request()->routeIs('fees.*') ? 'text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
               style="{{ request()->routeIs('fees.*') ? 'background-color: #22C55E;' : '' }}">
                <i data-lucide="credit-card" class="w-4.5 h-4.5 flex-shrink-0"></i>
                {{ __('Fee Management') }}
            </a>

            <a href="{{ route('expenses.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                      {{ request()->routeIs('expenses.*') ? 'text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
               style="{{ request()->routeIs('expenses.*') ? 'background-color: #22C55E;' : '' }}">
                <i data-lucide="receipt" class="w-4.5 h-4.5 flex-shrink-0"></i>
                {{ __('Expenses') }}
            </a>

            <a href="{{ route('reports.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                      {{ request()->routeIs('reports.*') ? 'text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
               style="{{ request()->routeIs('reports.*') ? 'background-color: #22C55E;' : '' }}">
                <i data-lucide="bar-chart-2" class="w-4.5 h-4.5 flex-shrink-0"></i>
                {{ __('Reports') }}
            </a>

            <div class="pt-3 mt-3 border-t border-slate-700">
                <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-widest" style="color: #64748B;">{{ __('Account') }}</p>

                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                          {{ request()->routeIs('profile.*') ? 'text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
                   style="{{ request()->routeIs('profile.*') ? 'background-color: #22C55E;' : '' }}">
                    <i data-lucide="user-circle" class="w-4.5 h-4.5 flex-shrink-0"></i>
                    {{ __('Profile') }}
                </a>

                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                          {{ request()->routeIs('settings.*') ? 'text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
                   style="{{ request()->routeIs('settings.*') ? 'background-color: #22C55E;' : '' }}">
                    <i data-lucide="settings" class="w-4.5 h-4.5 flex-shrink-0"></i>
                    {{ __('Settings') }}
                </a>
                
                <a href="{{ route('activity-logs.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                          {{ request()->routeIs('activity-logs.*') ? 'text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
                   style="{{ request()->routeIs('activity-logs.*') ? 'background-color: #22C55E;' : '' }}">
                    <i data-lucide="list" class="w-4.5 h-4.5 flex-shrink-0"></i>
                    {{ __('Activity Logs') }}
                </a>
            </div>
        </nav>

        <!-- User Card -->
        <div class="p-4 border-t border-slate-700">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold text-white"
                     style="background: linear-gradient(135deg, #22C55E, #16A34A);">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs truncate" style="color: #64748B;">{{ __('Gym Owner') }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-sm font-medium
                               text-slate-400 hover:text-white hover:bg-slate-800">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    {{ __('Logout') }}
                </button>
            </form>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- TOP NAV -->
        <header class="flex-shrink-0 flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200 shadow-sm">
            <!-- Mobile sidebar toggle -->
            <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100" style="color: #111827;">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>

            <div class="flex items-center gap-3 flex-1 lg:flex-none">
                <h1 class="text-lg font-semibold" style="color: #111827;">@yield('page_title', __('Dashboard'))</h1>
            </div>

            <!-- Global Search -->
            <div class="hidden md:flex items-center gap-2 mx-4 flex-1 max-w-xs relative" id="global-search-container">
                <div class="relative w-full">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color: #9CA3AF;"></i>
                    <input type="text"
                           id="global-search"
                           placeholder="{{ __('Search members, trainers...') }}"
                           autocomplete="off"
                           class="w-full pl-9 pr-4 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl
                                  focus:outline-none focus:ring-2 focus:border-transparent"
                           style="--tw-ring-color: #22C55E;">
                </div>
                <!-- Search Results Dropdown -->
                <div id="search-results" class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-lg border border-gray-100 hidden z-50 overflow-hidden max-h-96 overflow-y-auto">
                    <!-- Results will be injected here -->
                </div>
            </div>

            <!-- Right side -->
            <div class="flex items-center gap-3">
                <!-- Network status indicator -->
                <div id="network-status" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium"
                     style="background-color: #DCFCE7; color: #15803D;">
                    <span class="w-2 h-2 rounded-full" style="background-color: #22C55E;"></span>
                    <span id="network-status-text">Online</span>
                </div>

                <!-- Notifications bell -->
                <a href="{{ route('notifications.index') }}" class="relative p-2 rounded-xl hover:bg-gray-100" style="color: #6B7280;">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span id="notification-badge" style="display:none; position:absolute; top:0; right:0; transform:translate(25%,-25%); background-color:#EF4444; color:#fff; font-size:10px; font-weight:700; line-height:1; min-width:20px; height:20px; padding:0 4px; border-radius:10px; border:2px solid #fff; align-items:center; justify-content:center; white-space:nowrap; box-sizing:border-box;"></span>
                </a>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="flex-1 overflow-y-auto p-6">
            @if(session('success'))
                <div class="flash-message mb-4 flex items-center gap-3 p-4 rounded-2xl text-sm font-medium"
                     style="background-color: #DCFCE7; color: #15803D; transition: opacity 0.5s ease, transform 0.5s ease;">
                    <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="flash-message mb-4 flex items-center gap-3 p-4 rounded-2xl text-sm font-medium"
                     style="background-color: #FEE2E2; color: #DC2626; transition: opacity 0.5s ease, transform 0.5s ease;">
                    <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            // Network status indicator managed by offline.js
            // Note: Advanced sync states (Syncing.../Synced) are managed by resources/js/offline.js

            // Mobile sidebar toggle
            const toggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            if (toggle) {
                toggle.addEventListener('click', () => {
                    sidebar.classList.toggle('-translate-x-full');
                });
            }

            // Fetch Notification Count
            function fetchNotificationCount() {
                fetch('/api/notifications/unread-count')
                    .then(response => response.json())
                    .then(data => {
                        const badge = document.getElementById('notification-badge');
                        if (data.count > 0) {
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                    })
                    .catch(error => console.error('Error fetching notifications:', error));
            }
            fetchNotificationCount();
            // Poll every 60 seconds
            setInterval(fetchNotificationCount, 60000);
            // Global Search Logic
            const searchInput = document.getElementById('global-search');
            const searchResults = document.getElementById('search-results');
            const searchContainer = document.getElementById('global-search-container');
            let searchTimeout;

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();

                    if (query.length < 2) {
                        searchResults.classList.add('hidden');
                        return;
                    }

                    searchTimeout = setTimeout(() => {
                        fetch(`/api/search?q=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(data => {
                                searchResults.innerHTML = '';
                                
                                let hasResults = false;

                                if (data.members && data.members.length > 0) {
                                    hasResults = true;
                                    let html = `<div class="px-3 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">Members</div>`;
                                    data.members.forEach(member => {
                                        html += `<a href="/members/${member.id}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700">
                                                    <div class="font-medium">${member.name}</div>
                                                    <div class="text-xs text-gray-500">${member.phone}</div>
                                                 </a>`;
                                    });
                                    searchResults.insertAdjacentHTML('beforeend', html);
                                }

                                if (data.trainers && data.trainers.length > 0) {
                                    hasResults = true;
                                    let html = `<div class="px-3 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">Trainers</div>`;
                                    data.trainers.forEach(trainer => {
                                        html += `<a href="/trainers/${trainer.id}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700">
                                                    <div class="font-medium">${trainer.name}</div>
                                                    <div class="text-xs text-gray-500">${trainer.specialization || 'Trainer'}</div>
                                                 </a>`;
                                    });
                                    searchResults.insertAdjacentHTML('beforeend', html);
                                }

                                if (data.plans && data.plans.length > 0) {
                                    hasResults = true;
                                    let html = `<div class="px-3 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plans</div>`;
                                    data.plans.forEach(plan => {
                                        html += `<a href="/membership-plans/${plan.id}/edit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700">
                                                    <div class="font-medium">${plan.name}</div>
                                                 </a>`;
                                    });
                                    searchResults.insertAdjacentHTML('beforeend', html);
                                }

                                if (!hasResults) {
                                    searchResults.innerHTML = `<div class="px-4 py-3 text-sm text-gray-500 text-center">No results found for "${query}"</div>`;
                                }

                                searchResults.classList.remove('hidden');
                            })
                            .catch(err => console.error('Search error:', err));
                    }, 300);
                });

                // Hide search results on click outside
                document.addEventListener('click', (e) => {
                    if (!searchContainer.contains(e.target)) {
                        searchResults.classList.add('hidden');
                    }
                });
            }

            // Auto-hide flash messages after 4.5 seconds
            setTimeout(() => {
                const flashes = document.querySelectorAll('.flash-message');
                flashes.forEach(f => {
                    f.style.opacity = '0';
                    f.style.transform = 'translateY(-10px)';
                    setTimeout(() => f.remove(), 500);
                });
            }, 4500);
        });
    </script>

    @stack('scripts')
</body>
</html>
