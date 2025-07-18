<!DOCTYPE html>
<html>
<head>
    <title>{{ $reportPeriodName ?? 'Generated Report' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        h2 {
            color: #666;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .page-break {
            page-break-before: always;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        .no-data {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 20px;
        }
        .summary-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Comprehensive Business Report</h1>
        <h3>{{ $reportPeriodName ?? 'Generated Report' }}</h3>
        <p><strong>Generated On:</strong> {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="summary-box">
        <h3>Report Summary</h3>
        <p>This report includes data for:
            @if(is_array($reportData))
                @foreach(array_keys($reportData) as $type)
                    <strong>{{ ucfirst($type) }}</strong>@if(!$loop->last), @endif
                @endforeach
            @else
                No specific data types.
            @endif
        </p>
    </div>

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
                        <td>${{ number_format((float)($sale['price'] ?? 0), 2) }}</td>
                        <td>${{ number_format((float)($sale['total'] ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <h2>Sales Report</h2>
        <div class="no-data">No sales data available for this period.</div>
    @endif

    @if(isset($reportData['inventory']) && !empty($reportData['inventory']))
        <div class="page-break"></div>
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
    @else
        <div class="page-break"></div>
        <h2>Inventory Report</h2>
        <div class="no-data">No inventory data available.</div>
    @endif

    @if(isset($reportData['suppliers']) && !empty($reportData['suppliers']))
        <div class="page-break"></div>
        <h2>Key Suppliers Report</h2>
        <table>
            <thead>
                <tr>
                    <th>Supplier Name</th>
                    <th>Contact Email</th>
                    <th>Total Orders</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['suppliers'] as $supplier)
                    <tr>
                        <td>{{ $supplier['name'] ?? 'N/A' }}</td>
                        <td>{{ $supplier['email'] ?? 'N/A' }}</td>
                        <td>{{ $supplier['total_orders'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="page-break"></div>
        <h2>Key Suppliers Report</h2>
        <div class="no-data">No supplier data available.</div>
    @endif

    @if(isset($reportData['customers']) && !empty($reportData['customers']))
        <div class="page-break"></div>
        <h2>Key Customers Report</h2>
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Total Purchases</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['customers'] as $customer)
                    <tr>
                        <td>{{ $customer['name'] ?? 'N/A' }}</td>
                        <td>{{ $customer['email'] ?? 'N/A' }}</td>
                        <td>${{ number_format((float)($customer['total_purchases'] ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="page-break"></div>
        <h2>Key Customers Report</h2>
        <div class="no-data">No customer data available.</div>
    @endif

    <div class="footer">
        <p>&copy; {{ date('Y') }} Dairy Supply Chain Management System. All rights reserved.</p>
        <p>Generated on {{ \Carbon\Carbon::now()->format('l, F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
