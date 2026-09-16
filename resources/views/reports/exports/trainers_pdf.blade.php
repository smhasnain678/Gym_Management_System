@extends('reports.exports.layout')

@section('title', __('Trainer Report'))

@section('filters')
    @if($request->status) <strong>{{ __('Status') }}:</strong> {{ $request->status }}<br> @endif
@endsection

@section('summary')
    <div class="summary">
        <table style="margin-bottom: 0; border: none;">
            <tr>
                <td style="border: none;"><strong>{{ __('Total Trainers') }}:</strong> {{ $totalTrainers }}</td>
            </tr>
        </table>
    </div>
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Specialization') }}</th>
                <th>{{ __('Contact') }}</th>
                <th>{{ __('Joining Date') }}</th>
                <th>{{ __('Assigned Members') }}</th>
                <th>{{ __('Status') }}</th>
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
                    <td>{{ $trainer->is_active ? __('Active') : __('Inactive') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
