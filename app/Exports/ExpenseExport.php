<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpenseExport implements FromCollection, WithHeadings, WithMapping
{
    protected $expenses;
    protected $categoryTotals;

    public function __construct($expenses, $categoryTotals = null)
    {
        $this->expenses = $expenses;
        $this->categoryTotals = $categoryTotals;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Title',
            'Category',
            'Amount',
            'Paid To',
            'Notes',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->expense_date->gymDateFormat(),
            $expense->title,
            $expense->expenseCategory->name ?? '-',
            $expense->amount,
            $expense->paid_to ?? '-',
            $expense->notes ?? '-',
        ];
    }
}
