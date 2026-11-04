<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\FeePayment;
use App\Models\MemberMembership;
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

        // 4. Validate payment_date and due_date.
        // offline.js always sends YYYY-MM-DD (from <input type="date">).
        // We validate strictly against that format so relative words ('tomorrow')
        // and garbage strings are rejected with a clean InvalidArgumentException
        // rather than a raw Carbon exception message.
        $paymentDate = $this->parseStrictDate($data['payment_date'] ?? null, 'payment_date', true);
        $dueDate     = $this->parseStrictDate($data['due_date'] ?? null, 'due_date', false);

        // 5. Transaction with row-level locking
        return DB::transaction(function () use ($membership, $amountPaid, $paymentDate, $paymentMethod, $dueDate, $data) {
            // Lock the membership row and refresh its balances
            /** @var \App\Models\MemberMembership $lockedMembership */
            $lockedMembership = MemberMembership::where('id', $membership->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Strict overpayment guard against fresh balance (compare after rounding to 2dp)
            if (round($amountPaid, 2) > round((float) $lockedMembership->remaining_amount, 2)) {
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

    /**
     * Validate a date string against the strict YYYY-MM-DD format that
     * offline.js always produces from <input type="date">.
     *
     * Relative words ('tomorrow', 'next week') and malformed strings are
     * rejected with a named InvalidArgumentException so the sync controller
     * can return a clean per-action error message.
     *
     * @param  mixed        $value      Raw value from the client payload (must be string|null at runtime)
     * @param  string       $field      Field name used in the error message
     * @param  bool         $required   Whether a missing/empty value falls back to today
     * @return string|null             Validated YYYY-MM-DD string, or null if optional and absent
     *
     * @throws \InvalidArgumentException
     */
    private function parseStrictDate(mixed $value, string $field, bool $required): ?string
    {
        // Guard against non-string types (array, bool, int, object) that a
        // malicious or buggy client could send. PHP would throw a TypeError
        // before we ever reach the format check; convert it to a named
        // InvalidArgumentException instead.
        if ($value !== null && ! is_string($value)) {
            throw new \InvalidArgumentException(
                "Invalid {$field}: must be a YYYY-MM-DD string, got " . gettype($value) . '.'
            );
        }

        if ($value === null || $value === '') {
            if ($required) {
                // Fall back to today when payment_date is omitted (matches old behaviour)
                return now()->toDateString();
            }
            return null;
        }

        // DateTime::createFromFormat is strict: it rejects relative words and
        // garbage strings that Carbon::parse() would otherwise accept.
        $parsed = \DateTime::createFromFormat('Y-m-d', $value);

        if (! $parsed || $parsed->format('Y-m-d') !== $value) {
            throw new \InvalidArgumentException(
                "Invalid {$field}: expected YYYY-MM-DD format, got \"{$value}\"."
            );
        }

        return $value;
    }
}
