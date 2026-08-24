<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    protected $fillable = [
        'member_id',
        'member_membership_id',
        'amount_paid',
        'payment_date',
        'due_date',
        'payment_method',
        'receipt_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'payment_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class)->withTrashed();
    }

    public function memberMembership(): BelongsTo
    {
        return $this->belongsTo(MemberMembership::class);
    }
}
