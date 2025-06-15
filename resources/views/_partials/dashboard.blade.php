<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Retailer Dashboard - DSCM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #000;
            color: #fff;
        }

        .sidebar {
            background-color: #111;
            min-height: 100vh;
            padding: 20px;
            color: red;
        }

        .sidebar a {
            color: red;
            text-decoration: none;
            display: block;
            margin-bottom: 15px;
        }

        .sidebar a:hover {
            color: green;
        }

        .main {
            padding: 30px;
        }

        .card {
            background-color: #111;
            border: 1px solid #444;
            color: white;
        }

        .btn-success {
            background-color: green;
            border-color: green;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar">
        <h2 class="text-center">DSCM</h2>
        <a href="#">Dashboard</a>
        <a href="#">Products</a>
        <a href="#">Orders</a>
        <a href="#">Chat (Wholesalers)</a>
    </div>

    <div class="main flex-grow-1">
        <h2>Retailer Dashboard</h2>

        <div class="row my-4">
            <div class="col-md-4">
                <div class="card p-3">
                    <h4>Stock Levels</h4>
                    <ul>
                        @foreach ($stockLevels as $item => $qty)
                            <li>{{ $item }}: {{ $qty }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <h4>Active Orders</h4>
                    <p>{{ $activeOrders }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <h4>Low Stock Alerts</h4>
                    @if (count($lowStockAlerts))
                        <ul>
                            @foreach ($lowStockAlerts as $item)
                                <li style="color:red">{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>No alerts</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="card p-4 mb-4">
            <h4>Stock Overview (Graph)</h4>
            <canvas id="stockChart"></canvas>
        </div>

        <div class="card p-4">
            <h4>Recent Orders</h4>
            <ul>
                @foreach ($recentOrders as $order)
                    <li>Order #{{ $order['id'] }} - {{ $order['product'] }} (Qty: {{ $order['quantity'] }})</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('stockChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($stockLevels)) !!},
            datasets: [{
                label: 'Stock Quantity',
                data: {!! json_encode(array_values($stockLevels)) !!},
                backgroundColor: 'green',
                borderColor: 'lime',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    ticks: { color: 'white' },
                    beginAtZero: true
                },
                x: {
                    ticks: { color: 'white' }
                }
            },
            plugins: {
                legend: {
                    labels: { color: 'white' }
                }
            }
        }
    });
</script>

</body>
</html>
