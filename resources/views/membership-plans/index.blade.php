@extends('layouts.app')

@section('title', 'Membership Plans')
@section('meta_description', 'Manage membership plan templates for your gym.')
@section('page_title', 'Membership Plans')

@section('content')
<div class="space-y-6">

    {{-- ── Header row ────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold" style="color:#111827;">Plan Templates</h2>
            <p class="text-sm mt-0.5" style="color:#6B7280;">
                Create and manage reusable membership plan templates. Assign them to members later.
            </p>
        </div>
        <a href="{{ route('membership-plans.create') }}"
           id="btn-add-plan"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm
                  transition-all duration-200 hover:shadow-md active:scale-95 flex-shrink-0"
           style="background-color:#22C55E;">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Plan
        </a>
    </div>

    {{-- ── Filter / Search bar ────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('membership-plans.index') }}"
          class="flex flex-col sm:flex-row gap-3" id="filter-form">

        <div class="relative flex-1 max-w-sm">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color:#9CA3AF;"></i>
            <input type="text"
                   id="search-plans"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search plans…"
                   class="w-full pl-9 pr-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl
                          focus:outline-none focus:ring-2 focus:border-transparent"
                   style="--tw-ring-color:#22C55E;">
        </div>

        <select name="status"
                id="filter-status"
                onchange="this.form.submit()"
                class="px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl
                       focus:outline-none focus:ring-2 focus:border-transparent"
                style="--tw-ring-color:#22C55E; color:#374151;">
            <option value="">All Statuses</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>

        @if(request('search') || request('status'))
            <a href="{{ route('membership-plans.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm text-gray-500
                      bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                Clear
            </a>
        @endif

        <button type="submit"
                class="px-4 py-2.5 text-sm font-medium text-white rounded-xl transition-colors"
                style="background-color:#22C55E;">
            Search
        </button>
    </form>

    {{-- ── Plans grid ─────────────────────────────────────────────────────── --}}
    @if($plans->isEmpty())
        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
                 style="background-color:#F0FDF4;">
                <i data-lucide="layers" class="w-8 h-8" style="color:#22C55E;"></i>
            </div>
            <h3 class="text-lg font-semibold mb-1" style="color:#111827;">No plans found</h3>
            <p class="text-sm mb-6" style="color:#6B7280;">
                @if(request('search') || request('status'))
                    No plans match your current filters. <a href="{{ route('membership-plans.index') }}" class="underline" style="color:#22C55E;">Clear filters</a>
                @else
                    Get started by creating your first membership plan.
                @endif
            </p>
            @if(!request('search') && !request('status'))
                <a href="{{ route('membership-plans.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white"
                   style="background-color:#22C55E;">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Create First Plan
                </a>
            @endif
        </div>

    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($plans as $plan)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden
                        flex flex-col transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
                 id="plan-card-{{ $plan->id }}">

                {{-- Colour bar + status badge --}}
                <div class="h-2 w-full flex-shrink-0"
                     style="background-color: {{ $plan->color ?? '#22C55E' }};"></div>

                <div class="p-5 flex flex-col flex-1">

                    {{-- Title row --}}
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                 style="background-color: {{ $plan->color ? $plan->color . '22' : '#F0FDF4' }};">
                                <i data-lucide="badge-check" class="w-4 h-4"
                                   style="color: {{ $plan->color ?? '#22C55E' }};"></i>
                            </div>
                            <h3 class="text-base font-semibold truncate" style="color:#111827;">
                                {{ $plan->name }}
                            </h3>
                        </div>

                        {{-- Status badge --}}
                        @if($plan->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0"
                                  style="background-color:#DCFCE7; color:#15803D;">
                                <span class="w-1.5 h-1.5 rounded-full" style="background-color:#22C55E;"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0"
                                  style="background-color:#F3F4F6; color:#6B7280;">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                Inactive
                            </span>
                        @endif
                    </div>

                    {{-- Stats row --}}
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="rounded-xl px-3 py-2.5" style="background-color:#F9FAFB;">
                            <p class="text-xs font-medium mb-0.5" style="color:#9CA3AF;">Duration</p>
                            <p class="text-sm font-semibold" style="color:#111827;">
                                {{ $plan->duration_days }}
                                {{ $plan->duration_days == 1 ? 'day' : 'days' }}
                                @if($plan->duration_days >= 365)
                                    <span class="text-xs font-normal" style="color:#6B7280;">
                                        ({{ number_format($plan->duration_days / 365, 1) }} yr)
                                    </span>
                                @elseif($plan->duration_days >= 30)
                                    <span class="text-xs font-normal" style="color:#6B7280;">
                                        (~{{ round($plan->duration_days / 30) }} mo)
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="rounded-xl px-3 py-2.5" style="background-color:#F9FAFB;">
                            <p class="text-xs font-medium mb-0.5" style="color:#9CA3AF;">Price</p>
                            <p class="text-sm font-semibold" style="color:#111827;">
                                {{ number_format($plan->price, 2) }}
                            </p>
                        </div>
                    </div>

                    {{-- Description --}}
                    @if($plan->description)
                        <p class="text-xs leading-relaxed mb-3 flex-1" style="color:#6B7280;">
                            {{ Str::limit($plan->description, 100) }}
                        </p>
                    @else
                        <div class="flex-1"></div>
                    @endif

                    {{-- Footer meta --}}
                    <div class="flex items-center justify-between text-xs mb-4" style="color:#9CA3AF;">
                        <span>Sort order: {{ $plan->sort_order }}</span>
                        <span>{{ $plan->member_memberships_count ?? 0 }} assignment(s)</span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 pt-3 border-t border-gray-100">

                        {{-- Edit --}}
                        <a href="{{ route('membership-plans.edit', $plan) }}"
                           id="btn-edit-{{ $plan->id }}"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2
                                  text-xs font-medium rounded-xl border border-gray-200
                                  bg-white hover:bg-gray-50 transition-colors"
                           style="color:#374151;">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            Edit
                        </a>

                        {{-- Toggle status --}}
                        <form action="{{ route('membership-plans.toggle-status', $plan) }}"
                              method="POST" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    id="btn-toggle-{{ $plan->id }}"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2
                                           text-xs font-medium rounded-xl border transition-colors"
                                    style="{{ $plan->is_active
                                        ? 'border-color:#FCA5A5; color:#DC2626; background-color:#FFF7F7;'
                                        : 'border-color:#86EFAC; color:#15803D; background-color:#F0FDF4;' }}">
                                <i data-lucide="{{ $plan->is_active ? 'eye-off' : 'eye' }}" class="w-3.5 h-3.5"></i>
                                {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        {{-- Delete --}}
                        <form action="{{ route('membership-plans.destroy', $plan) }}"
                              method="POST"
                              class="flex-shrink-0"
                              onsubmit="return confirm('Delete plan \'{{ addslashes($plan->name) }}\'?\nThis cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    id="btn-delete-{{ $plan->id }}"
                                    class="inline-flex items-center justify-center p-2 rounded-xl
                                           border border-gray-200 bg-white hover:bg-red-50
                                           hover:border-red-200 transition-colors"
                                    style="color:#9CA3AF;" title="Delete plan">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Summary bar --}}
        <p class="text-xs text-center" style="color:#9CA3AF;">
            Showing {{ $plans->count() }} plan(s)
            @if(request('search') || request('status'))
                matching your filters
            @endif
        </p>
    @endif

</div>
@endsection
