<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MemberExport implements FromCollection, WithHeadings, WithMapping
{
    protected $members;

    public function __construct($members)
    {
        $this->members = $members;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->members;
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Joining Date',
            'Status',
            'Assigned Trainer',
        ];
    }

    public function map($member): array
    {
        return [
            $member->name,
            $member->email,
            $member->phone,
            $member->joining_date->format('Y-m-d'),
            $member->status,
            $member->trainer ? $member->trainer->name : 'None',
        ];
    }
}
