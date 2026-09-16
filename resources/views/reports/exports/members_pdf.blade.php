@extends('reports.exports.layout')

@section('title', __('Member Report'))

@section('filters')
    @if($request->status) <strong>{{ __('Status') }}:</strong> {{ $request->status }}<br> @endif
@endsection

@section('summary')
    <div class="summary">
        <table style="margin-bottom: 0; border: none;">
            <tr>
                <td style="border: none;"><strong>{{ __('Total Members') }}:</strong> {{ $totalMembers }}</td>
                <td style="border: none;"><strong>{{ __('Active Members') }}:</strong> {{ $activeMembers }}</td>
            </tr>
        </table>
    </div>
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Contact') }}</th>
                <th>{{ __('Joining Date') }}</th>
                <th>{{ __('Trainer') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($membersList as $member)
                <tr>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->phone }}<br>{{ $member->email }}</td>
                    <td>{{ $member->joining_date->gymDateFormat() }}</td>
                    <td>{{ $member->trainer ? $member->trainer->name : '-' }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $member->status)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
