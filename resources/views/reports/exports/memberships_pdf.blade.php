@extends('reports.exports.layout')

@section('title', 'Membership Report')

@section('filters')
    @if($request->status) <strong>Status:</strong> {{ $request->status }}<br> @endif
@endsection

@section('summary')
    <div class="summary">
        <table style="margin-bottom: 0; border: none;">
            <tr>
                <td style="border: none;"><strong>Total Memberships:</strong> {{ $totalMemberships }}</td>
                <td style="border: none;"><strong>Total Value:</strong> ${{ number_format($totalAmount, 2) }}</td>
                <td style="border: none;"><strong>Total Paid:</strong> ${{ number_format($paidAmount, 2) }}</td>
                <td style="border: none;"><strong>Total Remaining:</strong> ${{ number_format($remainingAmount, 2) }}</td>
            </tr>
        </table>
    </div>
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th>Member</th>
                <th>Plan</th>
                <th>Dates</th>
                <th>Status</th>
                <th>Financials</th>
            </tr>
        </thead>
        <tbody>
            @foreach($memberships as $membership)
                <tr>
                    <td>{{ $membership->member->name }}</td>
                    <td>{{ $membership->membershipPlan->name }}</td>
                    <td>
                        Start: {{ $membership->start_date->gymDateFormat() }}<br>
                        End: {{ $membership->end_date->gymDateFormat() }}
                    </td>
                    <td>{{ $membership->status }}</td>
                    <td>
                        Total: ${{ number_format($membership->total_amount, 2) }}<br>
                        Paid: ${{ number_format($membership->paid_amount, 2) }}<br>
                        Due: ${{ number_format($membership->remaining_amount, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
