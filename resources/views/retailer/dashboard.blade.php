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
<div class="card p-3 mt-4">
    <h4>Chat with Other Roles</h4>
    <div id="chatBox" style="height: 250px; overflow-y: scroll; background: #222; padding: 10px; border: 1px solid #444;">
        <div id="messages"></div>
    </div>
    <div class="mt-3 d-flex">
        <input type="text" id="messageInput" class="form-control me-2" placeholder="Type your message here...">
        <button onclick="sendMessage()" class="btn btn-success">Send</button>
    </div>
</div>
<script>
    const userRole = "{{ strtolower(Request::segment(1)) }}"; // 'factory', 'supplier', 'wholesaler'
    let receiverRole;
<script>
    const userRole = 'retailer';  // You can dynamically inject this from backend

    const receiverRole = 'wholesaler'; // fixed receiver for Retailer

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        if (!message) return;

        fetch('/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                sender_role: userRole,
                receiver_role: receiverRole,
                message: message
            })
        }).then(() => {
            input.value = '';
            fetchMessages(); // Your function to reload messages
        });
    }
</script>

<div class="chat-box">
    <textarea id="messageInput" placeholder="Type a message..."></textarea>
    <button onclick="sendMessage()">Send</button>
</div>


    function fetchMessages() {
        fetch(`/chat/messages?user=${userRole}`)
            .then(response => response.json())
            .then(messages => {
                const container = document.getElementById('messages');
                container.innerHTML = '';
                messages.forEach(msg => {
                    const color = msg.sender_role === userRole ? 'lime' : 'orange';
                    container.innerHTML += `<div style="color:${color}"><strong>${msg.sender_role}:</strong> ${msg.message}</div>`;
                });
                document.getElementById('chatBox').scrollTop = chatBox.scrollHeight;
            });
    }

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        if (!message) return;

        fetch('/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                sender: userRole,
                receiver: receiverRole,
                message: message
            })
        }).then(() => {
            input.value = '';
            fetchMessages();
        });
    }

    // Refresh chat every 3 seconds
    setInterval(fetchMessages, 3000);
    fetchMessages();
</script>
