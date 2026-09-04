@extends('reports.exports.layout')

@section('title', 'Trainer Report')

@section('filters')
    @if($request->status) <strong>Status:</strong> {{ $request->status }}<br> @endif
@endsection

@section('summary')
    <div class="summary">
        <table style="margin-bottom: 0; border: none;">
            <tr>
                <td style="border: none;"><strong>Total Trainers:</strong> {{ $totalTrainers }}</td>
            </tr>
        </table>
    </div>
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Specialization</th>
                <th>Contact</th>
                <th>Joining Date</th>
                <th>Assigned Members</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trainersList as $trainer)
                <tr>
                    <td>{{ $trainer->name }}</td>
                    <td>{{ $trainer->specialization }}</td>
                    <td>{{ $trainer->phone }}</td>
                    <td>{{ $trainer->joining_date->gymDateFormat() }}</td>
                    <td>{{ $trainer->members_count ?? 0 }}</td>
                    <td>{{ $trainer->is_active ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
