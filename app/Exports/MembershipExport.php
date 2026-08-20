<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MembershipExport implements FromCollection, WithHeadings, WithMapping
{
    protected $memberships;

    public function __construct($memberships)
    {
        $this->memberships = $memberships;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->memberships;
    }

    public function headings(): array
    {
        return [
            'Member Name',
            'Plan Name',
            'Start Date',
            'End Date',
            'Status',
            'Total Amount',
            'Paid Amount',
            'Remaining Amount',
        ];
    }

    public function map($membership): array
    {
        return [
            $membership->member->name,
            $membership->membershipPlan->name,
            $membership->start_date->format('Y-m-d'),
            $membership->end_date->format('Y-m-d'),
            $membership->status,
            $membership->total_amount,
            $membership->paid_amount,
            $membership->remaining_amount,
        ];
    }
}
