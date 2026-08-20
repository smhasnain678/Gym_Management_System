<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FeeCollectionExport implements FromCollection, WithHeadings, WithMapping
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
            'Receipt Number',
            'Member Name',
            'Membership Plan',
            'Amount',
            'Payment Method',
            'Due Date',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->payment_date->format('Y-m-d'),
            $payment->receipt_number ?? '-',
            $payment->member->name,
            $payment->memberMembership->membershipPlan->name ?? '-',
            $payment->amount_paid,
            $payment->payment_method,
            $payment->due_date ? $payment->due_date->format('Y-m-d') : '-',
        ];
    }
}
