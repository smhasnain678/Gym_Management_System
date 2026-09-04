<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Fee Receipt') }} #{{ $payment->id }} — WarmUp</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #111827; background: #fff; font-size: 13px; }
        .page { max-width: 600px; margin: 30px auto; padding: 40px; border: 1px solid #e5e7eb; border-radius: 12px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 2px solid #22C55E; padding-bottom: 20px; }
        .gym-name { font-size: 24px; font-weight: 800; color: #111827; }
        .gym-name span { color: #22C55E; }
        .gym-info { font-size: 12px; color: #6B7280; margin-top: 4px; }
        .receipt-label { text-align: right; }
        .receipt-label h2 { font-size: 18px; font-weight: 700; color: #22C55E; }
        .receipt-label p { font-size: 12px; color: #6B7280; margin-top: 2px; }
        .section { margin-bottom: 24px; }
        .section-title { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #9CA3AF; margin-bottom: 10px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .info-item label { display: block; font-size: 11px; color: #6B7280; margin-bottom: 2px; }
        .info-item p { font-size: 13px; font-weight: 600; color: #111827; }
        .amount-box { background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 16px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; text-align: center; }
        .amount-box .label { font-size: 11px; color: #6B7280; }
        .amount-box .value { font-size: 18px; font-weight: 800; margin-top: 4px; }
        .amount-box .paid .value { color: #15803D; }
        .amount-box .remaining .value { color: #DC2626; }
        .amount-box .total .value { color: #111827; }
        .highlight { background: #22C55E; border-radius: 8px; padding: 16px; text-align: center; margin: 20px 0; }
        .highlight .amount-paid-label { font-size: 13px; color: rgba(255,255,255,0.8); }
        .highlight .amount-paid { font-size: 32px; font-weight: 900; color: #fff; margin-top: 4px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9CA3AF; font-size: 11px; }
        .method-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; background: #DBEAFE; color: #1D4ED8; font-size: 11px; font-weight: 600; text-transform: capitalize; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .page { border: none; margin: 0; padding: 20px; }
        }
    </style>
</head>
<body>

<div class="no-print" style="background:#f3f4f6; padding:12px; text-align:center; border-bottom:1px solid #e5e7eb;">
    <button onclick="window.print()" style="background:#22C55E; color:white; border:none; padding:10px 24px; border-radius:8px; font-weight:600; cursor:pointer; font-size:14px; margin-right:8px;">
        🖨️ {{ __('Print Receipt') }}
    </button>
    <a href="{{ route('fees.index') }}" style="color:#6B7280; font-size:13px;">← {{ __('Back to Fee Management') }}</a>
</div>

<div class="page">
    {{-- Header --}}
    <div class="header">
        <div>
            <div class="gym-name">Warm<span>Up</span></div>
            @if($gymSettings)
                <div class="gym-info">
                    @if($gymSettings->address) {{ $gymSettings->address }}<br>@endif
                    @if($gymSettings->contact_phone) {{ $gymSettings->contact_phone }}<br>@endif
                    @if($gymSettings->contact_email) {{ $gymSettings->contact_email }}@endif
                </div>
            @else
                <div class="gym-info">{{ __('Gym Management System') }}</div>
            @endif
        </div>
        <div class="receipt-label">
            <h2>{{ __('PAYMENT RECEIPT') }}</h2>
            <p>{{ __('Receipt') }} #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p>{{ $payment->payment_date->gymDateFormat() }}</p>
        </div>
    </div>

    {{-- Payment Highlight --}}
    <div class="highlight">
        <div class="amount-paid-label">{{ __('Amount Paid') }}</div>
        <div class="amount-paid">
            {{ $gymSettings?->currency_symbol ?? '' }}{{ number_format($payment->amount_paid, 2) }}
        </div>
        <div style="color:rgba(255,255,255,0.8); font-size:12px; margin-top:4px;">
            {{ __('via') }} <span class="method-badge" style="background:rgba(255,255,255,0.2); color:white;">{{ str_replace('_', ' ', $payment->payment_method ?? 'Cash') }}</span>
            &nbsp;{{ __('on') }} {{ $payment->payment_date->gymDateFormat() }}
        </div>
    </div>

    {{-- Member Info --}}
    <div class="section">
        <div class="section-title">{{ __('Member Information') }}</div>
        <div class="info-grid">
            <div class="info-item">
                <label>{{ __('Member Name') }}</label>
                <p>{{ $payment->member->name }}</p>
            </div>
            <div class="info-item">
                <label>{{ __('Member ID') }}</label>
                <p>#{{ str_pad($payment->member->id, 5, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div class="info-item">
                <label>{{ __('Phone') }}</label>
                <p>{{ $payment->member->phone }}</p>
            </div>
            <div class="info-item">
                <label>{{ __('Membership Plan') }}</label>
                <p>{{ $payment->memberMembership?->membershipPlan?->name ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- Balance Info --}}
    @if($payment->memberMembership)
    <div class="section">
        <div class="section-title">{{ __('Membership Balance') }}</div>
        <div class="amount-box">
            <div class="total">
                <div class="label">{{ __('Total Fee') }}</div>
                <div class="value">{{ number_format($payment->memberMembership->total_amount, 2) }}</div>
            </div>
            <div class="paid">
                <div class="label">{{ __('Total Paid') }}</div>
                <div class="value">{{ number_format($payment->memberMembership->paid_amount, 2) }}</div>
            </div>
            <div class="remaining">
                <div class="label">{{ __('Remaining') }}</div>
                <div class="value">{{ number_format($payment->memberMembership->remaining_amount, 2) }}</div>
            </div>
        </div>
        <div style="margin-top:10px; font-size:12px; color:#6B7280; text-align:center;">
            {{ __('Membership Period:') }} {{ $payment->memberMembership->start_date->gymDateFormat() }} — {{ $payment->memberMembership->end_date->gymDateFormat() }}
        </div>
    </div>
    @endif

    @if($payment->notes)
    <div class="section">
        <div class="section-title">{{ __('Notes') }}</div>
        <p style="color:#374151; font-size:13px;">{{ $payment->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>{{ __('Thank you for your payment!') }}</p>
        <p style="margin-top:4px;">{{ __('This is a computer-generated receipt. No signature required.') }}</p>
        <p style="margin-top:4px;">{{ __('Generated on') }} {{ now()->gymDateTimeFormat() }}</p>
    </div>
</div>

</body>
</html>
