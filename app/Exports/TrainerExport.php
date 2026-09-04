<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TrainerExport implements FromCollection, WithHeadings, WithMapping
{
    protected $trainers;

    public function __construct($trainers)
    {
        $this->trainers = $trainers;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->trainers;
    }

    public function headings(): array
    {
        return [
            'Name',
            'Contact',
            'Specialization',
            'Joining Date',
            'Salary',
            'Status',
            'Assigned Members',
        ];
    }

    public function map($trainer): array
    {
        return [
            $trainer->name,
            $trainer->phone,
            $trainer->specialization,
            $trainer->joining_date->gymDateFormat(),
            $trainer->salary,
            $trainer->is_active ? 'Active' : 'Inactive',
            $trainer->members_count ?? 0,
        ];
    }
}
