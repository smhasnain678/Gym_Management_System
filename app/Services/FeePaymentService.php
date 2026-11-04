<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\FeePayment;
use App\Models\MemberMembership;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FeePaymentService
{
    /**
     * Record a fee payment against a membership, updating paid and remaining balances.
     *
     * @param  \App\Models\MemberMembership|int  $membershipOrId
     * @param  array  $data
     * @return \App\Models\FeePayment
     *
     * @throws \InvalidArgumentException
     */
    public function recordPayment(MemberMembership|int $membershipOrId, array $data): FeePayment
    {
        // 1. Resolve and validate membership & member
        if ($membershipOrId instanceof MemberMembership) {
            $membership = $membershipOrId->relationLoaded('member')
                ? $membershipOrId
                : $membershipOrId->load('member');
        } else {
            $membership = MemberMembership::with('member')->find($membershipOrId);
        }

        if (! $membership) {
            throw new \InvalidArgumentException('Invalid membership: membership record not found.');
        }

        if (! $membership->member || $membership->member->trashed()) {
            throw new \InvalidArgumentException('Invalid membership: member record not found or has been removed.');
        }

        // 2. Validate amount_paid (numeric and > 0)
        if (! isset($data['amount_paid']) || ! is_numeric($data['amount_paid']) || (float) $data['amount_paid'] <= 0) {
            throw new \InvalidArgumentException('Payment amount must be a positive number.');
        }

        $amountPaid = round((float) $data['amount_paid'], 2);

        // 3. Validate payment_method
        $allowedMethods = ['cash', 'bank_transfer', 'easypaisa', 'jazzcash', 'card'];
        $paymentMethod  = $data['payment_method'] ?? 'cash';
        if (! in_array($paymentMethod, $allowedMethods, true)) {
            throw new \InvalidArgumentException("Invalid payment method: {$paymentMethod}.");
        }

        // 4. Validate payment_date
        $paymentDate = ! empty($data['payment_date']) ? Carbon::parse($data['payment_date'])->toDateString() : now()->toDateString();
        $dueDate     = ! empty($data['due_date']) ? Carbon::parse($data['due_date'])->toDateString() : null;

        // 5. Transaction with row-level locking
        return DB::transaction(function () use ($membership, $amountPaid, $paymentDate, $paymentMethod, $dueDate, $data) {
            // Lock the membership row and refresh its balances
            /** @var \App\Models\MemberMembership $lockedMembership */
            $lockedMembership = MemberMembership::where('id', $membership->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Strict overpayment guard against fresh balance
            if ($amountPaid > (float) $lockedMembership->remaining_amount) {
                throw new \InvalidArgumentException(
                    'Payment of ' . number_format($amountPaid, 2) . ' exceeds the remaining balance of ' . number_format($lockedMembership->remaining_amount, 2) . '.'
                );
            }

            // Create FeePayment record (member_id always comes from membership; client receipt_number is ignored)
            $payment = FeePayment::create([
                'member_id'            => $lockedMembership->member_id,
                'member_membership_id' => $lockedMembership->id,
                'amount_paid'          => $amountPaid,
                'payment_date'         => $paymentDate,
                'due_date'             => $dueDate,
                'payment_method'       => $paymentMethod,
                'receipt_number'       => null,
                'notes'                => $data['notes'] ?? null,
            ]);

            // Recalculate totals from sum of all fee payments (source of truth)
            $totalPaid = (float) $lockedMembership->feePayments()->sum('amount_paid');
            $lockedMembership->update([
                'paid_amount'      => $totalPaid,
                'remaining_amount' => max(0, (float) $lockedMembership->total_amount - $totalPaid),
            ]);

            // Activity Log
            if (auth()->check()) {
                ActivityLog::create([
                    'user_id'      => auth()->id(),
                    'action'       => 'Fee Received',
                    'description'  => 'Payment of ' . number_format($amountPaid, 2) . ' received from ' . $membership->member->name . ' for membership #' . $lockedMembership->id . '.',
                    'subject_type' => FeePayment::class,
                    'subject_id'   => $payment->id,
                    'ip_address'   => request()->ip(),
                    'user_agent'   => request()->userAgent(),
                ]);
            }

            return $payment;
        });
    }
}

