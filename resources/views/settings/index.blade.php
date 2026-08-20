@extends('layouts.app')

@section('title', 'Settings')
@section('page_title', 'Gym Settings')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm font-medium border border-red-100">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left Column: General & Contact --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- General Details --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="info" class="w-5 h-5 text-gray-400"></i> General Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gym Name</label>
                            <input type="text" name="gym_name" value="{{ old('gym_name', $settings->gym_name) }}" required
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Owner Name</label>
                            <input type="text" name="owner_name" value="{{ old('owner_name', $settings->owner_name) }}" required
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                            <input type="email" name="contact_email" value="{{ old('contact_email', $settings->contact_email) }}"
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                            <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings->contact_phone) }}"
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea name="address" rows="2"
                                      class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">{{ old('address', $settings->address) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input type="text" name="city" value="{{ old('city', $settings->city) }}"
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                            <input type="text" name="country" value="{{ old('country', $settings->country) }}"
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                        </div>
                    </div>
                </div>

                {{-- Localization & Preferences --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="globe" class="w-5 h-5 text-gray-400"></i> Localization & Preferences
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Currency Code</label>
                            <input type="text" name="currency" value="{{ old('currency', $settings->currency) }}" required placeholder="e.g. PKR"
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol</label>
                            <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}" required placeholder="e.g. Rs"
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                            <input type="text" name="timezone" value="{{ old('timezone', $settings->timezone) }}" required placeholder="e.g. Asia/Karachi"
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                            <select name="language" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                                <option value="en" {{ old('language', $settings->language) == 'en' ? 'selected' : '' }}>English</option>
                                <option value="ur" {{ old('language', $settings->language) == 'ur' ? 'selected' : '' }}>Urdu</option>
                                <option value="sd" {{ old('language', $settings->language) == 'sd' ? 'selected' : '' }}>Sindhi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Theme</label>
                            <select name="theme" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                                <option value="light" {{ old('theme', $settings->theme) == 'light' ? 'selected' : '' }}>Light</option>
                                <option value="dark" {{ old('theme', $settings->theme) == 'dark' ? 'selected' : '' }}>Dark</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Format</label>
                            <select name="date_format" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                                <option value="d/m/Y" {{ old('date_format', $settings->date_format) == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                <option value="m/d/Y" {{ old('date_format', $settings->date_format) == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                <option value="Y-m-d" {{ old('date_format', $settings->date_format) == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Time Format</label>
                            <select name="time_format" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent" style="--tw-ring-color: #22C55E;">
                                <option value="12h" {{ old('time_format', $settings->time_format) == '12h' ? 'selected' : '' }}>12 Hour (AM/PM)</option>
                                <option value="24h" {{ old('time_format', $settings->time_format) == '24h' ? 'selected' : '' }}>24 Hour</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3">
                    <button type="submit" class="px-6 py-2.5 text-white font-medium rounded-xl transition-colors shadow-sm"
                            style="background-color: #22C55E;" onmouseover="this.style.backgroundColor='#16A34A'" onmouseout="this.style.backgroundColor='#22C55E'">
                        Save Settings
                    </button>
                </div>
            </div>

            {{-- Right Column: Logo & System --}}
            <div class="space-y-6">
                
                {{-- Gym Logo --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="image" class="w-5 h-5 text-gray-400"></i> Gym Logo
                    </h3>
                    
                    <div class="flex flex-col items-center">
                        <div class="w-32 h-32 rounded-2xl bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden mb-4 relative group">
                            @if($settings->gym_logo)
                                <img src="{{ asset('storage/' . $settings->gym_logo) }}" alt="Gym Logo" class="w-full h-full object-cover">
                            @else
                                <i data-lucide="image-plus" class="w-8 h-8 text-gray-400"></i>
                            @endif
                        </div>
                        <label class="cursor-pointer text-sm font-medium text-green-600 hover:text-green-700 bg-green-50 px-4 py-2 rounded-xl transition-colors">
                            <span>Upload New Logo</span>
                            <input type="file" name="gym_logo" class="hidden" accept="image/*">
                        </label>
                        <p class="text-xs text-gray-500 mt-2 text-center">Max size 2MB. JPG, PNG, or GIF.</p>
                    </div>
                </div>

                {{-- Backup & Restore --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="database" class="w-5 h-5 text-gray-400"></i> Backup &amp; Restore
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">Safely backup your gym database or restore from an existing backup file.</p>
                    
                    <div class="space-y-3">
                        {{-- Download Backup --}}
                        <a href="{{ route('settings.backup.download') }}"
                           class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl text-sm font-medium transition-colors">
                            <i data-lucide="download" class="w-4 h-4"></i> Download Backup
                        </a>

                        {{-- Restore Backup --}}
                        <form action="{{ route('settings.backup.restore') }}" method="POST"
                              enctype="multipart/form-data"
                              onsubmit="return confirm('This will overwrite existing data with the backup. Continue?')">
                            @csrf
                            <label class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-red-50 hover:bg-red-100 border border-red-100 text-red-700 rounded-xl text-sm font-medium transition-colors cursor-pointer">
                                <i data-lucide="upload" class="w-4 h-4"></i> Restore from Backup
                                <input type="file" name="backup_file" class="hidden" accept=".json,application/json"
                                       onchange="this.closest('form').submit()">
                            </label>
                        </form>
                    </div>

                    <p class="text-xs text-gray-400 mt-3 text-center">Backup exports all members, trainers, fees &amp; expenses as JSON.</p>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
