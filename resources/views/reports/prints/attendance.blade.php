<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Attendance Report</title>
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 14px; color: #333; line-height: 1.5; }
        .print-container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #22C55E; padding-bottom: 20px; }
        .logo { font-size: 28px; font-weight: bold; color: #111827; }
        .logo span { color: #22C55E; }
        .title { font-size: 20px; font-weight: bold; margin-top: 10px; color: #374151; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 12px; color: #6B7280; }
        .summary { display: flex; gap: 20px; margin-bottom: 30px; padding: 15px; background-color: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px; }
        .summary-item { flex: 1; }
        .summary-label { font-size: 12px; color: #6B7280; font-weight: 600; text-transform: uppercase; }
        .summary-value { font-size: 24px; font-weight: bold; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border-bottom: 1px solid #E5E7EB; padding: 12px 8px; text-align: left; }
        th { background-color: #F9FAFB; font-weight: 600; color: #374151; font-size: 12px; text-transform: uppercase; }
        .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #9CA3AF; border-top: 1px solid #E5E7EB; padding-top: 20px; }
        
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-container">
        
        <div class="no-print" style="margin-bottom: 20px; text-align: right;">
            <button onclick="window.print()" style="padding: 8px 16px; background: #22C55E; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Print Document</button>
            <button onclick="window.close()" style="padding: 8px 16px; background: #6B7280; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 10px;">Close</button>
        </div>

        <div class="header">
            <div class="logo">Warm<span>Up</span></div>
            <div class="title">Attendance Report</div>
        </div>

        <div class="meta">
            <div>
                <strong>Date Range:</strong> 
                @if($request->start_date && $request->end_date)
                    {{ \Carbon\Carbon::parse($request->start_date)->gymDateFormat() }} - {{ \Carbon\Carbon::parse($request->end_date)->gymDateFormat() }}
                @elseif($request->month)
                    {{ \Carbon\Carbon::parse($request->month)->format('F Y') }}
                @else
                    All Time
                @endif
                <br>
                @if($request->status) <strong>Status:</strong> {{ $request->status }} @endif
            </div>
            <div>
                <strong>Generated:</strong> {{ \Carbon\Carbon::now('Asia/Karachi')->gymDateTimeFormat() }}
            </div>
        </div>

        <div class="summary">
            <div class="summary-item">
                <div class="summary-label">Total Records</div>
                <div class="summary-value">{{ $totalAttendance }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Present</div>
                <div class="summary-value" style="color: #16A34A;">{{ $presentCount }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Absent</div>
                <div class="summary-value" style="color: #DC2626;">{{ $absentCount }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Rate</div>
                <div class="summary-value">{{ $attendanceRate }}%</div>
            </div>
        </div>

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
                @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->date->gymDateFormat() }}</td>
                        <td><strong>{{ $attendance->member->name }}</strong></td>
                        <td>{{ $attendance->status }}</td>
                        <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->gymTimeFormat() : '-' }}</td>
                        <td>{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->gymTimeFormat() : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #6B7280;">No attendance records found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            Generated by WarmUp Gym Management System
        </div>
    </div>
</body>
</html>
