<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $attendances;

    public function __construct($attendances)
    {
        $this->attendances = $attendances;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->attendances;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Member',
            'Status',
            'Check In Time',
            'Check Out Time',
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->date->gymDateFormat(),
            $attendance->member->name,
            ucfirst($attendance->status),
            $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->gymTimeFormat() : '-',
            $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->gymTimeFormat() : '-',
        ];
    }
}
