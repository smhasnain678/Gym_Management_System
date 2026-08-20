<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Member Details — {{ $member->name }}</title>
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 14px; color: #333; line-height: 1.5; }
        .print-container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #22C55E; padding-bottom: 20px; }
        .logo { font-size: 28px; font-weight: bold; color: #111827; }
        .logo span { color: #22C55E; }
        .title { font-size: 20px; font-weight: bold; margin-top: 10px; color: #374151; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 12px; color: #6B7280; }
        .section { margin-bottom: 30px; }
        .section-title { font-size: 14px; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #E5E7EB; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 30px; }
        .info-item { }
        .info-label { font-size: 11px; font-weight: 600; color: #6B7280; text-transform: uppercase; letter-spacing: 0.05em; }
        .info-value { font-size: 14px; font-weight: 500; color: #111827; margin-top: 2px; }
        .status-badge { display: inline-block; padding: 3px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .status-active { background-color: #DCFCE7; color: #166534; }
        .status-inactive { background-color: #F3F4F6; color: #374151; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border-bottom: 1px solid #E5E7EB; padding: 10px 8px; text-align: left; }
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
            <div class="title">Member Details</div>
        </div>

        <div class="meta">
            <div>
                <strong>Member:</strong> {{ $member->name }}
            </div>
            <div>
                <strong>Generated:</strong> {{ \Carbon\Carbon::now('Asia/Karachi')->format('M d, Y h:i A') }}
            </div>
        </div>

        {{-- Personal Information --}}
        <div class="section">
            <div class="section-title">Personal Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Full Name</div>
                    <div class="info-value">{{ $member->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge {{ strtolower($member->status) === 'active' ? 'status-active' : 'status-inactive' }}">
                            {{ ucfirst($member->status) }}
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Gender</div>
                    <div class="info-value">{{ ucfirst($member->gender ?? '-') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value">{{ $member->date_of_birth ? $member->date_of_birth->format('M d, Y') : '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Joining Date</div>
                    <div class="info-value">{{ $member->joining_date->format('M d, Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Blood Group</div>
                    <div class="info-value">{{ $member->blood_group ?? '-' }}</div>
                </div>
            </div>
        </div>

        {{-- Contact Information --}}
        <div class="section">
            <div class="section-title">Contact Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Phone</div>
                    <div class="info-value">{{ $member->phone }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $member->email ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value">{{ $member->address ?? '-' }}</div>
                </div>
            </div>
        </div>

        {{-- Emergency Contact --}}
        @if($member->emergency_contact_name || $member->emergency_contact_phone)
        <div class="section">
            <div class="section-title">Emergency Contact</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Contact Name</div>
                    <div class="info-value">{{ $member->emergency_contact_name ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Phone</div>
                    <div class="info-value">{{ $member->emergency_contact_phone ?? '-' }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Medical Notes --}}
        @if($member->medical_notes)
        <div class="section">
            <div class="section-title">Medical Notes</div>
            <div class="info-value">{{ $member->medical_notes }}</div>
        </div>
        @endif

        {{-- Physical Info --}}
        @if($member->height || $member->weight)
        <div class="section">
            <div class="section-title">Physical Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Height (cm)</div>
                    <div class="info-value">{{ $member->height ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Weight (kg)</div>
                    <div class="info-value">{{ $member->weight ?? '-' }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Assigned Trainer --}}
        <div class="section">
            <div class="section-title">Assigned Trainer</div>
            @if($member->trainer)
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Trainer Name</div>
                        <div class="info-value">{{ $member->trainer->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Specialization</div>
                        <div class="info-value">{{ $member->trainer->specialization ?? '-' }}</div>
                    </div>
                </div>
            @else
                <div class="info-value" style="color: #6B7280;">No trainer assigned.</div>
            @endif
        </div>

        {{-- Membership History --}}
        <div class="section">
            <div class="section-title">Membership History</div>
            @if($member->memberships->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($member->memberships as $membership)
                            <tr>
                                <td>{{ $membership->membershipPlan->name ?? '-' }}</td>
                                <td>{{ $membership->start_date->format('M d, Y') }}</td>
                                <td>{{ $membership->end_date->format('M d, Y') }}</td>
                                <td>{{ $membership->status }}</td>
                                <td>${{ number_format($membership->total_amount, 2) }}</td>
                                <td>${{ number_format($membership->paid_amount, 2) }}</td>
                                <td>${{ number_format($membership->remaining_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="info-value" style="color: #6B7280;">No memberships found.</div>
            @endif
        </div>

        <div class="footer">
            Generated by WarmUp Gym Management System
        </div>
    </div>
</body>
</html>
