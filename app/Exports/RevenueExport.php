<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RevenueExport implements FromCollection, WithHeadings, WithMapping
{
    protected $payments;

    public function __construct($payments)
    {
        $this->payments = $payments;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->payments;
    }

    public function headings(): array
    {
        return [
            'Payment Date',
            'Member',
            'Membership Plan',
            'Amount',
            'Payment Method',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->payment_date->format('Y-m-d'),
            $payment->member->name,
            $payment->memberMembership->membershipPlan->name ?? 'N/A',
            $payment->amount_paid,
            $payment->payment_method,
        ];
    }
}
