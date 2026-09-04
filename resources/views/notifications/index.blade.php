@extends('layouts.app')

@section('title', __('Notifications'))
@section('meta_description', __('View all system notifications.'))
@section('page_title', __('Notifications'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">{{ __('Your Notifications') }}</h2>
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-green-600 hover:text-green-700 bg-green-50 hover:bg-green-100 px-4 py-2 rounded-xl transition-colors">
                {{ __('Mark all as read') }}
            </button>
        </form>
    </div>

    {{-- Notifications List --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($notifications->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background-color:#F3F4F6;">
                    <i data-lucide="bell" class="w-8 h-8" style="color:#6B7280;"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">{{ __('No notifications') }}</h3>
                <p class="text-gray-500 text-sm max-w-sm">{{ __('You are all caught up!') }}</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($notifications as $notification)
                    <div class="p-4 sm:p-6 hover:bg-gray-50/50 transition-colors {{ !$notification->is_read ? 'bg-green-50/30' : '' }}">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $notification->is_read ? 'bg-gray-100 text-gray-500' : 'bg-green-100 text-green-600' }}">
                                    @if($notification->type === 'membership_expiry' || $notification->type === 'renewal_reminder')
                                        <i data-lucide="calendar" class="w-5 h-5"></i>
                                    @elseif($notification->type === 'pending_fee')
                                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                                    @else
                                        <i data-lucide="bell" class="w-5 h-5"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold {{ $notification->is_read ? 'text-gray-700' : 'text-gray-900' }}">
                                    {{ $notification->title }}
                                </p>
                                <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>
                                <div class="text-xs text-gray-400 mt-2 space-y-1">
                                    <div class="flex items-center gap-1">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        <span>{{ $notification->created_at->gymDateTimeFormat() }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                            @if(!$notification->is_read)
                                <div class="flex-shrink-0">
                                    <form method="POST" action="{{ route('notifications.mark-read', $notification) }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-gray-500 hover:text-green-600 border border-gray-200 hover:border-green-200 bg-white hover:bg-green-50 px-3 py-1.5 rounded-lg transition-colors">
                                            {{ __('Mark read') }}
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($notifications->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
