@extends('reports.exports.layout')

@section('title', 'Attendance Report')

@section('filters')
    @if($request->start_date) <strong>Start Date:</strong> {{ $request->start_date }}<br> @endif
    @if($request->end_date) <strong>End Date:</strong> {{ $request->end_date }}<br> @endif
    @if($request->status) <strong>Status:</strong> {{ $request->status }}<br> @endif
@endsection

@section('summary')
    <div class="summary">
        <table style="margin-bottom: 0; border: none;">
            <tr>
                <td style="border: none;"><strong>Total Records:</strong> {{ $totalAttendance }}</td>
                <td style="border: none;"><strong>Present:</strong> {{ $presentCount }}</td>
                <td style="border: none;"><strong>Absent:</strong> {{ $absentCount }}</td>
                <td style="border: none;"><strong>Rate:</strong> {{ $attendanceRate }}%</td>
            </tr>
        </table>
    </div>
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Member</th>
                <th>Status</th>
                <th>Check In</th>
                <th>Check Out</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->date->gymDateFormat() }}</td>
                    <td>{{ $attendance->member->name }}</td>
                    <td>{{ ucfirst($attendance->status) }}</td>
                    <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->gymTimeFormat() : '-' }}</td>
                    <td>{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->gymTimeFormat() : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
