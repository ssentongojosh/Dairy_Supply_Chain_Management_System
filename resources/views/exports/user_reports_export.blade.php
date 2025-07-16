<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report: {{ $reportPeriodName ?? 'Generated Report' }}</title>
</head>
<body>
    <h1>Report: {{ $reportPeriodName ?? 'Generated Report' }}</h1>
    <p>Generated On: {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>

    @if(isset($reportData['sales']) && !empty($reportData['sales']))
        <h2>Sales Report</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Seller</th>
                    <th>Buyer</th>
                    <th>Status</th>
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
                        <td>{{ $sale['seller'] ?? 'N/A' }}</td>
                        <td>{{ $sale['buyer'] ?? 'N/A' }}</td>
                        <td>{{ $sale['status'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($reportData['inventory']) && !empty($reportData['inventory']))
        <h2>Inventory Report</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Current Stock</th>
                    <th>Reorder Point</th>
                    <th>Unit Cost</th>
                    <th>Selling Price</th>
                    <th>Unit</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Last Restocked</th>
                    <th>Last Updated</th>
                    <th>Owner</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['inventory'] as $item)
                    <tr>
                        <td>{{ $item['name'] ?? 'N/A' }}</td>
                        <td>{{ $item['stock'] ?? 'N/A' }}</td>
                        <td>{{ $item['reorder_point'] ?? 'N/A' }}</td>
                        <td>{{ $item['unit_cost'] ?? 'N/A' }}</td>
                        <td>{{ $item['selling_price'] ?? 'N/A' }}</td>
                        <td>{{ $item['unit'] ?? 'N/A' }}</td>
                        <td>{{ $item['location'] ?? 'N/A' }}</td>
                        <td>{{ $item['status'] ?? 'N/A' }}</td>
                        <td>{{ $item['last_restocked'] ?? 'N/A' }}</td>
                        <td>{{ $item['last_updated'] ?? 'N/A' }}</td>
                        <td>{{ $item['owner'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($reportData['suppliers']) && !empty($reportData['suppliers']))
        <h2>Supplier Report</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Supplier Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Total Orders</th>
                    <th>Total Revenue</th>
                    <th>Products Sold</th>
                    <th>Last Order</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['suppliers'] as $supplier)
                    <tr>
                        <td>{{ $supplier['name'] ?? 'N/A' }}</td>
                        <td>{{ $supplier['email'] ?? 'N/A' }}</td>
                        <td>{{ $supplier['role'] ?? 'N/A' }}</td>
                        <td>{{ $supplier['total_orders'] ?? 'N/A' }}</td>
                        <td>{{ $supplier['total_revenue'] ?? 'N/A' }}</td>
                        <td>{{ $supplier['products_sold'] ?? 'N/A' }}</td>
                        <td>{{ $supplier['last_order'] ?? 'N/A' }}</td>
                        <td>{{ $supplier['status'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($reportData['customers']) && !empty($reportData['customers']))
        <h2>Customer Report</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Total Orders</th>
                    <th>Total Purchases</th>
                    <th>Products Purchased</th>
                    <th>First Order</th>
                    <th>Last Order</th>
                    <th>Customer Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['customers'] as $customer)
                    <tr>
                        <td>{{ $customer['name'] ?? 'N/A' }}</td>
                        <td>{{ $customer['email'] ?? 'N/A' }}</td>
                        <td>{{ $customer['role'] ?? 'N/A' }}</td>
                        <td>{{ $customer['total_orders'] ?? 'N/A' }}</td>
                        <td>{{ $customer['total_purchases'] ?? 'N/A' }}</td>
                        <td>{{ $customer['products_purchased'] ?? 'N/A' }}</td>
                        <td>{{ $customer['first_order'] ?? 'N/A' }}</td>
                        <td>{{ $customer['last_order'] ?? 'N/A' }}</td>
                        <td>{{ $customer['customer_status'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p><strong>Report Summary:</strong> This report includes data for:
        @if(is_array($reportData))
            @foreach(array_keys($reportData) as $type)
                <strong>{{ ucfirst($type) }}</strong>@if(!$loop->last), @endif
            @endforeach
        @else
            No specific data types.
        @endif
    </p>
</body>
</html>
