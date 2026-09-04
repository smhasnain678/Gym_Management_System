<?php

namespace App\Services;

use App\Models\MemberMembership;
use App\Models\Notification;
use Illuminate\Support\Carbon;

class NotificationService
{
    /**
     * Synchronize and generate notifications for the Gym Owner.
     * This method is idempotent and prevents duplicate unread notifications.
     */
    public function syncDashboardNotifications()
    {
        $this->generateExpiryNotifications();
        $this->generatePendingFeeNotifications();
        $this->generateRenewalReminders();
    }

    protected function generateExpiryNotifications()
    {
        $expiringMemberships = MemberMembership::with('member')
            ->whereIn('status', ['active', 'expiring_soon'])
            ->whereBetween('end_date', [today(), today()->addDays(7)])
            ->get();

        foreach ($expiringMemberships as $membership) {
            $exists = Notification::where('type', 'membership_expiry')
                ->where('notifiable_type', MemberMembership::class)
                ->where('notifiable_id', $membership->id)
                ->where('is_read', false)
                ->exists();

            if (!$exists && $membership->member) {
                Notification::create([
                    'type'            => 'membership_expiry',
                    'notifiable_type' => MemberMembership::class,
                    'notifiable_id'   => $membership->id,
                    'title'           => 'Membership Expiring Soon',
                    'message'         => "Membership for {$membership->member->name} expires on " . Carbon::parse($membership->end_date)->gymDateFormat() . ".",
                    'is_read'         => false,
                ]);
            }
        }
    }

    protected function generatePendingFeeNotifications()
    {
        $pendingFeesMemberships = MemberMembership::with('member')
            ->whereIn('status', ['active', 'expiring_soon'])
            ->where('remaining_amount', '>', 0)
            ->get();

        foreach ($pendingFeesMemberships as $membership) {
            $exists = Notification::where('type', 'pending_fee')
                ->where('notifiable_type', MemberMembership::class)
                ->where('notifiable_id', $membership->id)
                ->where('is_read', false)
                ->exists();

            if (!$exists && $membership->member) {
                Notification::create([
                    'type'            => 'pending_fee',
                    'notifiable_type' => MemberMembership::class,
                    'notifiable_id'   => $membership->id,
                    'title'           => 'Pending Fee Alert',
                    'message'         => "{$membership->member->name} has a pending fee of Rs. " . number_format($membership->remaining_amount, 2) . ".",
                    'is_read'         => false,
                ]);
            }
        }
    }

    protected function generateRenewalReminders()
    {
        $expiringMemberships = MemberMembership::with('member')
            ->whereIn('status', ['active', 'expiring_soon'])
            ->whereBetween('end_date', [today(), today()->addDays(7)])
            ->get();

        foreach ($expiringMemberships as $membership) {
            $exists = Notification::where('type', 'renewal_reminder')
                ->where('notifiable_type', MemberMembership::class)
                ->where('notifiable_id', $membership->id)
                ->where('is_read', false)
                ->exists();

            if (!$exists && $membership->member) {
                Notification::create([
                    'type'            => 'renewal_reminder',
                    'notifiable_type' => MemberMembership::class,
                    'notifiable_id'   => $membership->id,
                    'title'           => 'Renewal Reminder',
                    'message'         => "Remind {$membership->member->name} to renew their membership plan.",
                    'is_read'         => false,
                ]);
            }
        }
    }
}
