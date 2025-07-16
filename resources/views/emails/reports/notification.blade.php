@extends('layouts.contentNavbarLayout')
@section('content')
    <div class="container">
        <p>Dear {{ $userName }},</p>

        <p>Your scheduled report, "<strong>{{ $report->report_name }}</strong>" ({{ ucfirst($report->frequency) }}), has been generated successfully and is attached to this email.</p>

        @if($report->report_start_date && $report->report_end_date)
            <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($report->report_start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($report->report_end_date)->format('M d, Y') }}</p>
        @endif

        <p>You can also view your full report history and re-download reports anytime by visiting your account dashboard.</p>

        <a href="{{ url('/reports/history') }}" class="button">View Report History</a>

        <div class="footer">
            <p>This is an automated email. Please do not reply.</p>
            <p>&copy; {{ date('Y') }} Your Application Name. All rights reserved.</p>
        </div>
    </div>

@endsection