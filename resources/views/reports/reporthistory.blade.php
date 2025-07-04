@extends('layouts.contentNavbarLayout')
@section('content')
    <div class="container">
        <h1>My Report History</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if($reports->isEmpty())
            <p style="text-align: center; margin-top: 30px;">You haven't generated any reports yet. Go to <a href="{{ route('report-settings') }}">Report Settings</a> to generate one!</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Report Name</th>
                        <th>Type(s)</th>
                        <th>Format</th>
                        <th>Period</th>
                        <th>Generated On</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr>
                            <td>{{ $report->report_name }}</td>
                            <td>
                                @if(is_array($report->report_types))
                                    {{ implode(', ', array_map('ucfirst', $report->report_types)) }}
                                @else
                                    {{ ucfirst($report->report_types) }}
                                @endif
                            </td>
                            <td>{{ ucfirst($report->format) }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->report_start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($report->report_end_date)->format('M d, Y') }}</td>
                            <td>{{ $report->generated_at->format('M d, Y H:i') }}</td>
                            <td>
                                @if($report->status === 'success')
                                    <span style="color: green;">Success</span>
                                @else
                                    <span style="color: red;">Failed</span>
                                @endif
                            </td>
                            <td>
                                @if($report->status === 'success')
                                    <a href="{{ route('reports.history.download', $report->id) }}" class="download-btn">Download</a>
                                @else
                                    <span style="color: grey;">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Optional: If you use pagination in controller: $reports->links() --}}
            {{-- <div class="pagination">
                {{ $reports->links() }}
            </div> --}}
        @endif

        <div class="back-link">
            <a href="{{ route('report-settings') }}">Back to Report Settings</a>
        </div>
    </div>
@endsection