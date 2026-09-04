@extends('layouts.app')

@section('title', 'Members')
@section('meta_description', 'Manage all gym members, their memberships, and payments.')
@section('page_title', 'Members')

@section('content')
<div class="space-y-6">

    {{-- ── Stats row ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium mb-1" style="color:#9CA3AF;">{{ __('Total Members') }}</p>
            <p class="text-2xl font-bold" style="color:#111827;">{{ $totalMembers }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium mb-1" style="color:#9CA3AF;">{{ __('Active') }}</p>
            <p class="text-2xl font-bold" style="color:#22C55E;">{{ $activeMembers }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium mb-1" style="color:#9CA3AF;">{{ __('Suspended') }}</p>
            <p class="text-2xl font-bold" style="color:#F59E0B;">{{ \App\Models\Member::where('status','suspended')->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <p class="text-xs font-medium mb-1" style="color:#9CA3AF;">{{ __('Expired') }}</p>
            <p class="text-2xl font-bold" style="color:#EF4444;">{{ \App\Models\Member::where('status','expired')->count() }}</p>
        </div>
    </div>

    {{-- ── Header + Add button ─────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold" style="color:#111827;">{{ __('Member Directory') }}</h2>
            <p class="text-sm mt-0.5" style="color:#6B7280;">{{ __('Search, filter, and manage all registered members.') }}</p>
        </div>
        <a href="{{ route('members.create') }}"
           id="btn-add-member"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white
                  shadow-sm transition-all hover:shadow-md active:scale-95 flex-shrink-0"
           style="background-color:#22C55E;">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            {{ __('Add Member') }}
        </a>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('members.index') }}"
          class="flex flex-wrap gap-3 items-center bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">

        <div class="relative flex-1 min-w-48">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color:#9CA3AF;"></i>
            <input type="text"
                   id="search-members"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="{{ __('Search name, email, phone…') }}"
                   class="w-full pl-9 pr-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl
                          focus:outline-none focus:ring-2 focus:border-transparent"
                   style="--tw-ring-color:#22C55E;">
        </div>

        <select name="status"
                id="filter-status"
                onchange="this.form.submit()"
                class="px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl
                       focus:outline-none focus:ring-2 focus:border-transparent"
                style="color:#374151; --tw-ring-color:#22C55E;">
            <option value="all">{{ __('All Statuses') }}</option>
            <option value="active"       {{ request('status') === 'active'        ? 'selected' : '' }}>{{ __('Active') }}</option>
            <option value="expired"      {{ request('status') === 'expired'       ? 'selected' : '' }}>{{ __('Expired') }}</option>
            <option value="expiring_soon"{{ request('status') === 'expiring_soon' ? 'selected' : '' }}>{{ __('Expiring Soon') }}</option>
            <option value="suspended"    {{ request('status') === 'suspended'     ? 'selected' : '' }}>{{ __('Suspended') }}</option>
        </select>

        <select name="gender"
                id="filter-gender"
                onchange="this.form.submit()"
                class="px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl
                       focus:outline-none focus:ring-2 focus:border-transparent"
                style="color:#374151; --tw-ring-color:#22C55E;">
            <option value="all">{{ __('All Genders') }}</option>
            <option value="male"   {{ request('gender') === 'male'   ? 'selected' : '' }}>{{ __('Male') }}</option>
            <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
            <option value="other"  {{ request('gender') === 'other'  ? 'selected' : '' }}>{{ __('Other') }}</option>
        </select>

        <button type="submit"
                class="px-4 py-2.5 text-sm font-medium text-white rounded-xl"
                style="background-color:#22C55E;">
            {{ __('Search') }}
        </button>

        @if(request('search') || (request('status') && request('status') !== 'all') || (request('gender') && request('gender') !== 'all'))
            <a href="{{ route('members.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm text-gray-500
                      bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                {{ __('Clear') }}
            </a>
        @endif
    </form>

    {{-- ── Members table ───────────────────────────────────────────────────── --}}
    @if($members->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background-color:#F0FDF4;">
                <i data-lucide="users" class="w-8 h-8" style="color:#22C55E;"></i>
            </div>
            <h3 class="text-lg font-semibold mb-1" style="color:#111827;">{{ __('No members found') }}</h3>
            <p class="text-sm mb-6" style="color:#6B7280;">
                @if(request('search') || request('status') || request('gender'))
                    {{ __('No members match your filters.') }}
                    <a href="{{ route('members.index') }}" class="underline" style="color:#22C55E;">{{ __('Clear filters') }}</a>
                @else
                    {{ __('Get started by registering your first member.') }}
                @endif
            </p>
            @if(!request('search') && !request('status') && !request('gender'))
                <a href="{{ route('members.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white"
                   style="background-color:#22C55E;">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    {{ __('Register First Member') }}
                </a>
            @endif
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background-color:#F9FAFB; border-bottom:1px solid #F3F4F6;">
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider" style="color:#9CA3AF;">#</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider" style="color:#9CA3AF;">{{ __('Member') }}</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider hidden sm:table-cell" style="color:#9CA3AF;">{{ __('Contact') }}</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider hidden md:table-cell" style="color:#9CA3AF;">{{ __('Joined') }}</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider hidden lg:table-cell" style="color:#9CA3AF;">{{ __('Current Plan') }}</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider" style="color:#9CA3AF;">{{ __('Status') }}</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider" style="color:#9CA3AF;">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color:#F9FAFB;">
                        @foreach($members as $member)
                        @php
                            $latestMembership = $member->memberships->first();
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors" id="member-row-{{ $member->id }}">

                            {{-- ID --}}
                            <td class="px-4 py-3 text-xs font-medium" style="color:#9CA3AF;">
                                #{{ $member->id }}
                            </td>

                            {{-- Name + gender --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold text-white"
                                         style="background:linear-gradient(135deg,#22C55E,#16A34A);">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold" style="color:#111827;">{{ $member->name }}</p>
                                        <p class="text-xs capitalize" style="color:#9CA3AF;">{{ __($member->gender) }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td class="px-4 py-3 hidden sm:table-cell" style="color:#374151;">
                                <p>{{ $member->phone }}</p>
                                @if($member->email)
                                    <p class="text-xs" style="color:#9CA3AF;">{{ $member->email }}</p>
                                @endif
                            </td>

                            {{-- Joined --}}
                            <td class="px-4 py-3 hidden md:table-cell text-xs" style="color:#6B7280;">
                                {{ $member->joining_date->gymDateFormat() }}
                            </td>

                            {{-- Current plan --}}
                            <td class="px-4 py-3 hidden lg:table-cell">
                                @if($latestMembership && $latestMembership->membershipPlan)
                                    <div>
                                        <p class="text-xs font-medium" style="color:#111827;">
                                            {{ $latestMembership->membershipPlan->name }}
                                        </p>
                                        <p class="text-xs" style="color:#9CA3AF;">
                                            {{ __('Ends') }} {{ $latestMembership->end_date->gymDateFormat() }}
                                        </p>
                                        @if($latestMembership->remaining_amount > 0)
                                            <p class="text-xs font-medium" style="color:#EF4444;">
                                                {{ __('Due:') }} {{ number_format($latestMembership->remaining_amount, 2) }}
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs" style="color:#9CA3AF;">{{ __('No plan') }}</span>
                                @endif
                            </td>

                            {{-- Status badge --}}
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'active'        => ['bg'=>'#DCFCE7','color'=>'#15803D'],
                                        'expired'       => ['bg'=>'#FEE2E2','color'=>'#DC2626'],
                                        'expiring_soon' => ['bg'=>'#FEF3C7','color'=>'#B45309'],
                                        'suspended'     => ['bg'=>'#F3F4F6','color'=>'#6B7280'],
                                    ];
                                    $sc = $statusColors[$member->status] ?? ['bg'=>'#F3F4F6','color'=>'#6B7280'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                      style="background-color:{{ $sc['bg'] }}; color:{{ $sc['color'] }};">
                                    {{ __(ucfirst(str_replace('_', ' ', $member->status))) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('members.show', $member) }}"
                                       id="btn-view-{{ $member->id }}"
                                       class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                                       style="color:#6B7280;" title="{{ __('View member') }}">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('members.edit', $member) }}"
                                       id="btn-edit-{{ $member->id }}"
                                       class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                                       style="color:#6B7280;" title="{{ __('Edit member') }}">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <button type="button"
                                            onclick="openDeleteModal('{{ route('members.destroy', $member) }}', 'Delete Member?', 'Are you sure you want to delete <strong>{{ addslashes($member->name) }}</strong>? Their history will be preserved.')"
                                            id="btn-delete-{{ $member->id }}"
                                            class="p-1.5 rounded-lg hover:bg-red-50 transition-colors"
                                            style="color:#9CA3AF;" title="{{ __('Remove member') }}">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($members->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $members->links() }}
                </div>
            @endif
        </div>

        <p class="text-xs text-center" style="color:#9CA3AF;">
            {{ __('Showing') }} {{ $members->firstItem() }}–{{ $members->lastItem() }} {{ __('of') }} {{ $members->total() }} {{ __('members') }}
        </p>
    @endif

</div>

<x-delete-modal />
@endsection
