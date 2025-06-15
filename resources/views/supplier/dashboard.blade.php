<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Dashboard - DSCM</title>
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
        <a href="#">Stock</a>
        <a href="#">Deliveries</a>
        <a href="#">Chat (Wholesalers)</a>
    </div>

    <div class="main flex-grow-1">
        <h2>Supplier Dashboard</h2>

        <div class="row my-4">
            <div class="col-md-6">
                <div class="card p-3">
                    <h4>Current Stock</h4>
                    <ul>
                        @foreach ($stock as $item => $qty)
                            <li>{{ $item }}: {{ $qty }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-md-6">
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
            <h4>Stock Overview</h4>
            <canvas id="stockChart"></canvas>
        </div>

        <div class="card p-4">
            <h4>Recent Deliveries to Wholesalers</h4>
            <ul>
                @foreach ($deliveries as $delivery)
                    <li>Delivery #{{ $delivery['id'] }} to {{ $delivery['wholesaler'] }} - {{ $delivery['product'] }} (Qty: {{ $delivery['quantity'] }})</li>
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
            labels: {!! json_encode(array_keys($stock)) !!},
            datasets: [{
                label: 'Stock Levels',
                data: {!! json_encode(array_values($stock)) !!},
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
    const userRole = 'supplier';  // dynamically passed from backend

    let receiverRole = null;

    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('receiverSelect');
        select.addEventListener('change', () => {
            receiverRole = select.value;
            loadChatMessages(receiverRole);
        });

        receiverRole = select.value;
        loadChatMessages(receiverRole);
    });

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();

        if (!message || !receiverRole) {
            alert('Please select a chat partner and enter a message.');
            return;
        }

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
            loadChatMessages(receiverRole);
        });
    }

    function loadChatMessages(partnerRole) {
        fetch(`/chat/messages?user_role=${userRole}&partner_role=${partnerRole}`)
            .then(res => res.json())
            .then(messages => {
                const chatBox = document.getElementById('chatMessages');
                chatBox.innerHTML = '';
                messages.forEach(msg => {
                    const div = document.createElement('div');
                    div.textContent = `[${msg.sender_role}] ${msg.message}`;
                    chatBox.appendChild(div);
                });
            });
    }
</script>

<div>
    <label for="receiverSelect">Chat with:</label>
    <select id="receiverSelect" class="form-select">
        <option value="factory">Factory</option>
        <option value="wholesaler">Wholesaler</option>
    </select>
</div>

<div id="chatMessages" style="height: 300px; overflow-y: scroll; border: 1px solid #ccc; margin: 10px 0; padding: 5px;"></div>

<textarea id="messageInput" placeholder="Type your message"></textarea><br>
<button onclick="sendMessage()">Send</button>


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
