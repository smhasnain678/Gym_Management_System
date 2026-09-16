@extends('reports.exports.layout')

@section('title', __('Fee Collection Report'))

@section('filters')
    @if($request->start_date) <strong>{{ __('Start Date') }}:</strong> {{ $request->start_date }}<br> @endif
    @if($request->end_date) <strong>{{ __('End Date') }}:</strong> {{ $request->end_date }}<br> @endif
    @if($request->payment_method) <strong>{{ __('Method') }}:</strong> {{ $request->payment_method }}<br> @endif
@endsection

@section('summary')
    <div class="summary">
        <table style="margin-bottom: 0; border: none;">
            <tr>
                <td style="border: none;"><strong>{{ __('Total Collected') }}:</strong> ${{ number_format($totalCollected, 2) }}</td>
                <td style="border: none;"><strong>{{ __('Payments Count') }}:</strong> {{ $paymentsCount }}</td>
            </tr>
        </table>
    </div>
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th>{{ __('Payment Date') }}</th>
                <th>{{ __('Member') }}</th>
                <th>{{ __('Membership') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Method') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>
                        {{ $payment->payment_date->gymDateFormat() }}<br>
                        @if($payment->receipt_number) <small>Rec: {{ $payment->receipt_number }}</small> @endif
                    </td>
                    <td>{{ $payment->member->name }}</td>
                    <td>{{ $payment->memberMembership->membershipPlan->name ?? '-' }}</td>
                    <td>${{ number_format($payment->amount_paid, 2) }}</td>
                    <td>{{ $payment->payment_method }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
