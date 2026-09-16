@extends('reports.exports.layout')

@section('title', __('Expense Report'))

@section('filters')
    @if($request->start_date) <strong>{{ __('Start Date') }}:</strong> {{ $request->start_date }}<br> @endif
    @if($request->end_date) <strong>{{ __('End Date') }}:</strong> {{ $request->end_date }}<br> @endif
@endsection

@section('summary')
    <div class="summary">
        <table style="margin-bottom: 0; border: none;">
            <tr>
                <td style="border: none;"><strong>{{ __('Total Expenses') }}:</strong> ${{ number_format($totalExpenses, 2) }}</td>
                <td style="border: none;"><strong>{{ __('Expense Count') }}:</strong> {{ $expenseCount }}</td>
            </tr>
        </table>
    </div>
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Paid To') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expensesList as $expense)
                <tr>
                    <td>{{ $expense->expense_date->gymDateFormat() }}</td>
                    <td>{{ $expense->title }}</td>
                    <td>{{ $expense->expenseCategory->name ?? '-' }}</td>
                    <td>${{ number_format($expense->amount, 2) }}</td>
                    <td>{{ $expense->paid_to ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    @if(count($categoryTotals) > 0)
    <h3 style="margin-top: 30px;">{{ __('Category Breakdown') }}</h3>
    <table>
        <thead>
            <tr>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Total Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoryTotals as $catTotal)
                <tr>
                    <td>{{ $catTotal->category_name }}</td>
                    <td>${{ number_format($catTotal->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
@endsection
