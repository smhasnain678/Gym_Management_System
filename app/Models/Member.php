<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trainer_id',
        'name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'profile_photo',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_notes',
        'height',
        'weight',
        'blood_group',
        'joining_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joining_date' => 'date',
            'height' => 'decimal:2',
            'weight' => 'decimal:2',
        ];
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(MemberMembership::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }
}
