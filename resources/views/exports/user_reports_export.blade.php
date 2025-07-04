@extends('layouts.contentNavbarLayout')
@section('content')
    <h1>Report: {{ $reportPeriodName ?? 'Generated Report' }}</h1>
    <p>Generated On: {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>

    @if(isset($reportData['sales']) && !empty($reportData['sales']))
        <h2>Sales Report</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['sales'] as $sale)
                    <tr>
                        <td>{{ $sale['date'] ?? 'N/A' }}</td>
                        <td>{{ $sale['product'] ?? 'N/A' }}</td>
                        <td>{{ $sale['quantity'] ?? 'N/A' }}</td>
                        <td>{{ $sale['price'] ?? 'N/A' }}</td>
                        <td>{{ $sale['total'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($reportData['inventory']) && !empty($reportData['inventory']))
        <h2>Inventory Report</h2>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Current Stock</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['inventory'] as $item)
                    <tr>
                        <td>{{ $item['name'] ?? 'N/A' }}</td>
                        <td>{{ $item['stock'] ?? 'N/A' }}</td>
                        <td>{{ $item['last_updated'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- You can add more sections for 'suppliers' and 'customers' data here,
         following the same table structure as above, checking for isset($reportData['suppliers']), etc. --}}

    <p>This report includes data for:
        @if(is_array($reportData))
            @foreach(array_keys($reportData) as $type)
                <strong>{{ ucfirst($type) }}</strong>@if(!$loop->last), @endif
            @endforeach
        @else
            No specific data types.
        @endif
    </p>
@endsection
