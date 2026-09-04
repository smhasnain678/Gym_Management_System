@extends('layouts.app')

@section('title', __('Trainer Details') . ' — ' . $trainer->name)
@section('meta_description', __('View trainer profile and assigned members.'))
@section('page_title', __('Trainer Profile'))

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Back & Actions row --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <a href="{{ route('trainers.index') }}"
           class="inline-flex items-center gap-1.5 text-sm transition-colors hover:opacity-70"
           style="color:#22C55E;">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            {{ __('Back to Trainers') }}
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('trainers.edit', $trainer) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition-colors"
               style="color:#374151;">
                <i data-lucide="pencil" class="w-4 h-4"></i>
                {{ __('Edit Trainer') }}
            </a>
            <form action="{{ route('trainers.toggle-status', $trainer) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-xl border transition-colors
                               {{ $trainer->is_active ? 'bg-orange-50 border-orange-200 text-orange-700 hover:bg-orange-100' : 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100' }}">
                    <i data-lucide="{{ $trainer->is_active ? 'pause-circle' : 'play-circle' }}" class="w-4 h-4"></i>
                    {{ $trainer->is_active ? __('Deactivate') : __('Activate') }}
                </button>
            </form>
                            <button type="button"
                                    onclick="openDeleteModal('{{ route('trainers.destroy', $trainer) }}', '{{ __('Delete Trainer?') }}', '{{ __('Are you sure you want to delete :name? This action cannot be undone if the trainer has no linked members.', ['name' => '<strong>'.addslashes($trainer->name).'</strong>']) }}', '{{ $trainer->members()->count() > 0 ? __('This trainer has :count assigned member(s). Deletion will be blocked by the database.', ['count' => '<strong>'.$trainer->members()->count().'</strong>']) : '' }}')"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                {{ __('Delete') }}
                            </button>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT COLUMN: Profile & Contact --}}
        <div class="space-y-6 lg:col-span-1">
            
            {{-- Profile Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center">
                <div class="w-24 h-24 rounded-full flex items-center justify-center mb-4 text-3xl font-bold text-white shadow-sm"
                     style="background:linear-gradient(135deg,#22C55E,#16A34A);">
                    {{ strtoupper(substr($trainer->name, 0, 1)) }}
                </div>
                <h2 class="text-xl font-bold" style="color:#111827;">{{ $trainer->name }}</h2>
                <p class="text-sm mb-3" style="color:#6B7280;">#TR-{{ str_pad($trainer->id, 4, '0', STR_PAD_LEFT) }}</p>

                @if($trainer->is_active)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider" style="background-color: #DCFCE7; color: #15803D;">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> {{ __('Active') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider" style="background-color: #F3F4F6; color: #6B7280;">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> {{ __('Inactive') }}
                    </span>
                @endif

                <div class="w-full mt-6 pt-6 border-t border-gray-100 flex justify-around text-center">
                    <div>
                        <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Joined') }}</p>
                        <p class="text-sm font-semibold mt-0.5" style="color:#374151;">{{ $trainer->joining_date->gymDateFormat() }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Gender') }}</p>
                        <p class="text-sm font-semibold mt-0.5 capitalize" style="color:#374151;">{{ $trainer->gender }}</p>
                    </div>
                    @if($trainer->date_of_birth)
                    <div>
                        <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Age') }}</p>
                        <p class="text-sm font-semibold mt-0.5" style="color:#374151;">{{ $trainer->date_of_birth->age }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider" style="color:#9CA3AF;">{{ __('Contact Info') }}</h3>
                
                <div class="flex items-start gap-3">
                    <i data-lucide="phone" class="w-4 h-4 mt-0.5" style="color:#9CA3AF;"></i>
                    <div>
                        <p class="text-sm font-medium" style="color:#374151;">{{ $trainer->phone }}</p>
                        <p class="text-xs" style="color:#6B7280;">{{ __('Mobile') }}</p>
                    </div>
                </div>

                @if($trainer->email)
                <div class="flex items-start gap-3">
                    <i data-lucide="mail" class="w-4 h-4 mt-0.5" style="color:#9CA3AF;"></i>
                    <div>
                        <p class="text-sm font-medium break-all" style="color:#374151;">{{ $trainer->email }}</p>
                        <p class="text-xs" style="color:#6B7280;">{{ __('Email') }}</p>
                    </div>
                </div>
                @endif

                @if($trainer->address)
                <div class="flex items-start gap-3">
                    <i data-lucide="map-pin" class="w-4 h-4 mt-0.5" style="color:#9CA3AF;"></i>
                    <div>
                        <p class="text-sm font-medium" style="color:#374151;">{{ $trainer->address }}</p>
                    </div>
                </div>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN: Professional Details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Professional Details --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100" style="background-color:#F8FAFC;">
                    <h3 class="text-base font-semibold flex items-center gap-2" style="color:#111827;">
                        <i data-lucide="briefcase" class="w-5 h-5" style="color:#22C55E;"></i>
                        {{ __('Professional Details') }}
                    </h3>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Specialization') }}</p>
                            <p class="text-sm font-semibold mt-1" style="color:#374151;">{{ $trainer->specialization ?? __('N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Salary / Rate') }}</p>
                            <p class="text-sm font-semibold mt-1" style="color:#374151;">{{ $trainer->salary ? number_format($trainer->salary, 2) : __('N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Date of Birth') }}</p>
                            <p class="text-sm font-semibold mt-1" style="color:#374151;">{{ $trainer->date_of_birth ? $trainer->date_of_birth->gymDateFormat() : __('N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium" style="color:#9CA3AF;">{{ __('Members Assigned') }}</p>
                            <p class="text-sm font-semibold mt-1" style="color:#374151;">{{ $trainer->members()->count() }}</p>
                        </div>
                    </div>

                    @if($trainer->bio)
                    <div class="pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium mb-2" style="color:#9CA3AF;">{{ __('Bio & Notes') }}</p>
                        <div class="p-4 bg-gray-50 rounded-xl text-sm leading-relaxed" style="color:#374151;">
                            {{ $trainer->bio }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Assigned Members --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100" style="background-color:#F8FAFC;">
                    <h3 class="text-base font-semibold flex items-center gap-2" style="color:#111827;">
                        <i data-lucide="users" class="w-5 h-5" style="color:#22C55E;"></i>
                        {{ __('Assigned Members') }}
                    </h3>
                </div>
                @php
                    $assignedMembers = $trainer->members()->orderBy('name')->get();
                @endphp
                @if($assignedMembers->isEmpty())
                    <div class="p-6 text-center">
                        <i data-lucide="user-plus" class="w-8 h-8 mx-auto mb-2" style="color:#9CA3AF;"></i>
                        <p class="text-sm font-medium" style="color:#374151;">{{ __('No members assigned') }}</p>
                        <p class="text-xs mt-1" style="color:#6B7280;">{{ __('Members can be linked to this trainer from their profile.') }}</p>
                    </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider" style="color:#9CA3AF;">
                            <tr>
                                <th class="px-6 py-3 font-medium">{{ __('Member') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Contact') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($assignedMembers as $mem)
                            @php
                                $memStatusColors = [
                                    'active'        => ['bg'=>'#DCFCE7','color'=>'#15803D'],
                                    'expired'       => ['bg'=>'#FEE2E2','color'=>'#DC2626'],
                                    'expiring_soon' => ['bg'=>'#FEF3C7','color'=>'#B45309'],
                                    'suspended'     => ['bg'=>'#F3F4F6','color'=>'#6B7280'],
                                ];
                                $msc = $memStatusColors[$mem->status] ?? ['bg'=>'#F3F4F6','color'=>'#6B7280'];
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                             style="background:linear-gradient(135deg,#22C55E,#16A34A);">
                                            {{ strtoupper(substr($mem->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium" style="color:#374151;">{{ $mem->name }}</p>
                                            <p class="text-xs" style="color:#6B7280;">#{{ str_pad($mem->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-xs" style="color:#6B7280;">{{ $mem->phone }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded text-xs font-medium" style="background-color:{{ $msc['bg'] }}; color:{{ $msc['color'] }};">
                                        {{ ucfirst(str_replace('_', ' ', $mem->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('members.show', $mem) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors"
                                       style="color:#374151;">
                                        <i data-lucide="eye" class="w-3 h-3"></i> {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
</div>

<x-delete-modal />
@endsection
