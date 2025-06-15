<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Factory Dashboard - DSCM</title>
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
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar">
        <h2 class="text-center">DSCM</h2>
        <a href="#">Dashboard</a>
        <a href="#">Production</a>
        <a href="#">Shipments</a>
        <a href="#">Reports</a>
    </div>

    <div class="main flex-grow-1">
        <h2>Factory Dashboard</h2>

        <div class="row my-4">
            <div class="col-md-6">
                <div class="card p-3">
                    <h4>Production Output</h4>
                    <ul>
                        @foreach ($production as $item => $qty)
                            <li>{{ $item }}: {{ $qty }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-3">
                    <h4>Production Delays</h4>
                    @if (count($delays))
                        <ul>
                            @foreach ($delays as $item)
                                <li style="color:red">{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>No delays reported</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="card p-4 mb-4">
            <h4>Production Chart</h4>
            <canvas id="productionChart"></canvas>
        </div>

        <div class="card p-4">
            <h4>Recent Shipments</h4>
            <ul>
                @foreach ($shipments as $shipment)
                    <li>Shipment #{{ $shipment['id'] }} to {{ $shipment['to'] }} - {{ $shipment['product'] }} (Qty: {{ $shipment['quantity'] }})</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('productionChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($production)) !!},
            datasets: [{
                label: 'Production Units',
                data: {!! json_encode(array_values($production)) !!},
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
    const userRole = 'factory';  // dynamically passed in backend
    const receiverRole = 'supplier'; // fixed for factory

    document.addEventListener('DOMContentLoaded', () => {
        loadChatMessages();
    });

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();

        if (!message) {
            alert('Please enter a message.');
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
            loadChatMessages();
        });
    }

    function loadChatMessages() {
        fetch(`/chat/messages?user_role=${userRole}&partner_role=${receiverRole}`)
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
    <h5>Chat with Supplier</h5>
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
