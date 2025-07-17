@extends('layouts.contentNavbarLayout')
@section('content')
<head>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .card {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }
        .btn-success {
            background-color: #10b981;
            color: white;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            transition: background-color 0.2s;
        }
        .btn-success:hover {
            background-color: #059669;
        }
        .form-select, .form-input {
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            padding: 0.75rem 1rem;
            width: 100%;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-4xl bg-white p-8 rounded-lg shadow-xl">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Milk Product Demand Forecast</h1>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('demand.forecast.predict') }}" method="POST" class="mb-8">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="product" class="block text-gray-700 text-sm font-semibold mb-2">Product:</label>
                    <select id="product" name="product" class="form-select focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select a Product</option>
                        @foreach($products as $productOption)
                            <option value="{{ $productOption }}" {{ (old('product', $selected_product ?? '') == $productOption) ? 'selected' : '' }}>
                                {{ $productOption }}
                            </option>
                        @endforeach
                    </select>
                    @error('product')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="wholesaler_id" class="block text-gray-700 text-sm font-semibold mb-2">Wholesaler ID:</label> <!-- CHANGED LABEL -->
                    <select id="wholesaler_id" name="wholesaler_id" class="form-select focus:ring-blue-500 focus:border-blue-500" required> <!-- CHANGED ID AND NAME -->
                        <option value="">Select a Wholesaler</option>
                        @foreach($wholesalerIDs as $wholesalerIdOption) <!-- CHANGED VARIABLE NAME -->
                            <option value="{{ $wholesalerIdOption }}" {{ (old('wholesaler_id', $selected_wholesaler_id ?? '') == $wholesalerIdOption) ? 'selected' : '' }}> <!-- CHANGED VARIABLE NAME -->
                                {{ $wholesalerIdOption }}
                            </option>
                        @endforeach
                    </select>
                    @error('wholesaler_id') <!-- CHANGED ERROR KEY -->
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="start_date" class="block text-gray-700 text-sm font-semibold mb-2">Forecast Start Date:</label>
                    <input type="date" id="start_date" name="start_date" class="form-input focus:ring-blue-500 focus:border-blue-500" value="{{ old('start_date', $selected_start_date ?? \Carbon\Carbon::now()->addDay()->format('Y-m-d')) }}" required>
                    @error('start_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-gray-700 text-sm font-semibold mb-2">Forecast End Date:</label>
                    <input type="date" id="end_date" name="end_date" class="form-input focus:ring-blue-500 focus:border-blue-500" value="{{ old('end_date', $selected_end_date ?? \Carbon\Carbon::now()->addDays(7)->format('Y-m-d')) }}" required>
                    @error('end_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn-primary">Get Forecast</button>
            </div>
        </form>

        @isset($chart_labels)
            <div class="mt-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">Predicted Demand for {{ $selected_product }} for Wholesaler {{ $selected_wholesaler_id }}</h2> <!-- CHANGED TITLE -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <canvas id="demandForecastChart" class="w-full h-80"></canvas>
                </div>
                <div class="text-center text-sm text-gray-500 mt-4">
                    <p>Forecast from {{ \Carbon\Carbon::parse($selected_start_date)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($selected_end_date)->format('M d, Y') }}</p>
                    @php
                        $totalPredictedDemand = array_sum(json_decode($chart_data));
                    @endphp
                    <p class="text-lg font-semibold mt-2">Total Predicted Demand for Period: {{ number_format($totalPredictedDemand, 2) }} units</p>
                </div>

                {{-- Action Buttons for Plant Manager --}}
                @auth
                    @php
                        $userRole = is_object(Auth::user()->role) && method_exists(Auth::user()->role, 'value')
                                    ? Auth::user()->role->value
                                    : (string) Auth::user()->role;
                    @endphp
                    @if($userRole === 'plant_manager')
                        <div class="mt-8 p-6 bg-blue-50 rounded-lg card">
                            <h3 class="text-xl font-semibold text-blue-800 mb-4 text-center">Plant Manager Actions</h3>
                            <div class="flex flex-col md:flex-row justify-center gap-4">
                                <button
                                    type="button"
                                    class="btn-success btn-generate-task"
                                    data-task-type="production_adjustment"
                                    data-product="{{ $selected_product }}"
                                    data-wholesaler-id="{{ $selected_wholesaler_id }}" {{-- CHANGED DATA ATTRIBUTE --}}
                                    data-start-date="{{ $selected_start_date }}"
                                    data-end-date="{{ $selected_end_date }}"
                                    data-predicted-demand-total="{{ $totalPredictedDemand }}"
                                >
                                    Generate Production Adjustment Task
                                </button>
                                <button
                                    type="button"
                                    class="btn-success btn-generate-task"
                                    data-task-type="raw_material_order"
                                    data-product="{{ $selected_product }}"
                                    data-wholesaler-id="{{ $selected_wholesaler_id }}" {{-- CHANGED DATA ATTRIBUTE --}}
                                    data-start-date="{{ $selected_start_date }}"
                                    data-end-date="{{ $selected_end_date }}"
                                    data-predicted-demand-total="{{ $totalPredictedDemand }}"
                                >
                                    Generate Raw Material Order Task
                                </button>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>
        @endisset
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <!-- Axios CDN for AJAX requests -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            @isset($chart_labels)
                const labels = {!! $chart_labels !!};
                const data = {!! $chart_data !!};

                const ctx = document.getElementById('demandForecastChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Predicted Daily Demand',
                            data: data,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            tension: 0.1,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            title: {
                                display: false,
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Predicted Demand (Units)'
                                }
                            }
                        }
                    }
                });

                // Event listener for Generate Task buttons
                document.querySelectorAll('.btn-generate-task').forEach(button => {
                    button.addEventListener('click', function() {
                        const taskType = this.dataset.taskType;
                        const product = this.dataset.product;
                        const wholesalerId = this.dataset.wholesalerId; // CHANGED DATA ATTRIBUTE
                        const startDate = this.dataset.startDate;
                        const endDate = this.dataset.endDate;
                        const predictedDemandTotal = this.dataset.predictedDemandTotal;

                        let confirmationMessage = `Are you sure you want to generate a task for "${taskType.replace('_', ' ')}" based on the forecast for ${product} for Wholesaler ${wholesalerId} from ${startDate} to ${endDate}?`; // CHANGED CONFIRM MESSAGE

                        if (confirm(confirmationMessage)) {
                            axios.post('{{ route('demand.forecast.generate_task') }}', {
                                task_type: taskType,
                                product: product,
                                wholesaler_id: wholesalerId, // CHANGED PAYLOAD KEY
                                start_date: startDate,
                                end_date: endDate,
                                predicted_demand_total: predictedDemandTotal
                            })
                            .then(response => {
                                alert(response.data.message);
                                location.reload();
                            })
                            .catch(error => {
                                console.error('Error generating task:', error.response ? error.response.data : error.message);
                                alert(error.response.data.message || 'Failed to generate task.');
                            });
                        }
                    });
                });
            @endisset
        });
    </script>
</body>
@endsection
