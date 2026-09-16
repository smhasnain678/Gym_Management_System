@extends('reports.exports.layout')

@section('title', __('Membership Report'))

@section('filters')
    @if($request->status) <strong>{{ __('Status') }}:</strong> {{ $request->status }}<br> @endif
@endsection

@section('summary')
    <div class="summary">
        <table style="margin-bottom: 0; border: none;">
            <tr>
                <td style="border: none;"><strong>{{ __('Total Memberships') }}:</strong> {{ $totalMemberships }}</td>
                <td style="border: none;"><strong>{{ __('Total Value') }}:</strong> ${{ number_format($totalAmount, 2) }}</td>
                <td style="border: none;"><strong>{{ __('Total Paid') }}:</strong> ${{ number_format($paidAmount, 2) }}</td>
                <td style="border: none;"><strong>{{ __('Total Remaining') }}:</strong> ${{ number_format($remainingAmount, 2) }}</td>
            </tr>
        </table>
    </div>
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th>{{ __('Member') }}</th>
                <th>{{ __('Plan') }}</th>
                <th>{{ __('Dates') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Financials') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($memberships as $membership)
                <tr>
                    <td>{{ $membership->member->name }}</td>
                    <td>{{ $membership->membershipPlan->name }}</td>
                    <td>
                        {{ __('Start:') }} {{ $membership->start_date->gymDateFormat() }}<br>
                        {{ __('End:') }} {{ $membership->end_date->gymDateFormat() }}
                    </td>
                    <td>{{ $membership->status }}</td>
                    <td>
                        {{ __('Total:') }} ${{ number_format($membership->total_amount, 2) }}<br>
                        {{ __('Paid:') }} ${{ number_format($membership->paid_amount, 2) }}<br>
                        {{ __('Due:') }} ${{ number_format($membership->remaining_amount, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
