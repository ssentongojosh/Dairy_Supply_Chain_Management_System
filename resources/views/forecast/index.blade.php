@extends('layouts.contentNavbarLayout')
@section('content')


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
                {{-- NEW: Forecast Type Selection --}}
                <div class="md:col-span-2">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Forecast Type:</label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="forecast_type" value="wholesaler_specific" class="form-radio text-blue-600"
                                {{ (old('forecast_type', $selected_forecast_type ?? 'wholesaler_specific') == 'wholesaler_specific') ? 'checked' : '' }}
                                id="forecast_type_wholesaler">
                            <span class="ml-2 text-gray-700">Wholesaler Specific</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="forecast_type" value="general" class="form-radio text-blue-600"
                                {{ (old('forecast_type', $selected_forecast_type ?? 'wholesaler_specific') == 'general') ? 'checked' : '' }}
                                id="forecast_type_general">
                            <span class="ml-2 text-gray-700">General Product</span>
                        </label>
                    </div>
                    @error('forecast_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                {{-- END NEW --}}

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

                {{-- NEW: Conditionally visible Wholesaler ID dropdown --}}
              <div id="wholesaler_id_container">
    <label for="wholesaler_id" class="block text-gray-700 text-sm font-semibold mb-2">Wholesaler:</label>
    <select id="wholesaler_id" name="wholesaler_id" class="form-select focus:ring-blue-500 focus:border-blue-500">
        <option value="">Select a Wholesaler</option>
        @foreach($wholesalerIDs as $wholesaler)
            <option value="{{ $wholesaler['id'] }}" {{ (old('wholesaler_id', $selected_wholesaler_id ?? '') == $wholesaler['id']) ? 'selected' : '' }}>
                {{ $wholesaler['name'] }} (ID: {{ $wholesaler['id'] }})
            </option>
        @endforeach
    </select>
    @error('wholesaler_id')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
                {{-- END NEW --}}

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


        @auth
            @php
    $role = Auth::user()->role;
    if (is_object($role) && property_exists($role, 'value')) {
        $userRole = $role->value;
    } elseif (is_object($role) && method_exists($role, 'value')) {
        $userRole = $role->value;
    } else {
        $userRole = (string) $role;
    }
@endphp

            @if($userRole === 'plant_manager')
                <div class="mt-8 p-6 bg-yellow-50 rounded-lg card text-center">
                    <h3 class="text-xl font-semibold text-yellow-800 mb-4">Automated Task Suggestions</h3>
                    <button type="button" id="btn-suggest-tasks" class="btn-warning">
                        Suggest Tasks Based on Forecasts
                    </button>
                    <div id="suggestion-loading-message" class="mt-2 text-yellow-700 hidden">
                        <div class="flex items-center justify-center">
                            <svg class="animate-spin text-yellow-700" width="12" height="12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
</svg>
                            Processing suggestions... This may take a moment.
                        </div>
                    </div>
                </div>
            @endif
        @endauth

        @isset($chart_labels)
            <div class="mt-8">
                {{-- MODIFIED: Dynamic chart title based on forecast type --}}
                <h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">
                    Predicted Demand for {{ $selected_product }}
                    @if(isset($selected_forecast_type) && $selected_forecast_type === 'wholesaler_specific' && $selected_wholesaler_id)
                        for Wholesaler {{ $selected_wholesaler_id }}
                    @else
                        (General Forecast)
                    @endif
                </h2>

                @php
                    $totalPredictedDemand = array_sum(json_decode($chart_data));
                @endphp
                <p class="text-xl font-bold text-blue-700 mb-4 text-center">
                    Total Predicted Demand for Period: {{ number_format($totalPredictedDemand, 2) }} units
                </p>
                <p class="text-center text-sm text-gray-500 mb-4">
                    Forecast from {{ \Carbon\Carbon::parse($selected_start_date)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($selected_end_date)->format('M d, Y') }}
                </p>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <canvas id="demandForecastChart" class="w-full h-80"></canvas>
                </div>

                {{-- Action Buttons for Plant Manager --}}
                @auth
                    @php
    $role = Auth::user()->role;
    if (is_object($role) && property_exists($role, 'value')) {
        $userRole = $role->value;
    } elseif (is_object($role) && method_exists($role, 'value')) {
        $userRole = $role->value;
    } else {
        $userRole = (string) $role;
    }
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
                                    data-wholesaler-id="{{ $selected_wholesaler_id ?? '' }}" {{-- Pass empty string if null --}}
                                    data-start-date="{{ $selected_start_date }}"
                                    data-end-date="{{ $selected_end_date }}"
                                    data-predicted-demand-total="{{ $totalPredictedDemand }}"
                                    data-forecast-type="{{ $selected_forecast_type ?? 'wholesaler_specific' }}" {{-- NEW: Pass forecast type --}}
                                >
                                    Generate Production Adjustment Task
                                </button>
                                <button
                                    type="button"
                                    class="btn-success btn-generate-task"
                                    data-task-type="raw_material_order"
                                    data-product="{{ $selected_product }}"
                                    data-wholesaler-id="{{ $selected_wholesaler_id ?? '' }}" {{-- Pass empty string if null --}}
                                    data-start-date="{{ $selected_start_date }}"
                                    data-end-date="{{ $selected_end_date }}"
                                    data-predicted-demand-total="{{ $totalPredictedDemand }}"
                                    data-forecast-type="{{ $selected_forecast_type ?? 'wholesaler_specific' }}" {{-- NEW: Pass forecast type --}}
                                >
                                    Generate Raw Material Order Task
                                </button>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>
        @endisset

        @auth
            @php
    $role = Auth::user()->role;
    if (is_object($role) && property_exists($role, 'value')) {
        $userRole = $role->value;
    } elseif (is_object($role) && method_exists($role, 'value')) {
        $userRole = $role->value;
    } else {
        $userRole = (string) $role;
    }
@endphp
            @if($userRole === 'plant_manager')
                <div class="mt-8 p-6 bg-purple-50 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-purple-800 mb-4 text-center">Suggested Tasks for Review</h3>
                    @if($suggestedTasks->isEmpty())
                        <p class="text-gray-600 text-center">No automated tasks suggested at this time.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                                <thead class="bg-purple-100">
                                    <tr>
                                        <th class="py-2 px-4 text-left text-sm font-semibold text-purple-700">Product</th>
                                        <th class="py-2 px-4 text-left text-sm font-semibold text-purple-700">Wholesaler</th>
                                        <th class="py-2 px-4 text-left text-sm font-semibold text-purple-700">Type</th>
                                        <th class="py-2 px-4 text-left text-sm font-semibold text-purple-700">Description</th>
                                        <th class="py-2 px-4 text-left text-sm font-semibold text-purple-700">Suggested On</th>
                                        <th class="py-2 px-4 text-left text-sm font-semibold text-purple-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($suggestedTasks as $task)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="py-2 px-4 text-sm text-gray-800">{{ $task->product->name ?? 'N/A' }}</td>
                                            <td class="py-2 px-4 text-sm text-gray-800">
                                                @if($task->wholesaler_id)
                                                    {{ $task->wholesaler_id }}
                                                @else
                                                    General
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 text-sm text-gray-800">{{ ucwords(str_replace('_', ' ', $task->task_type)) }}</td>
                                            <td class="py-2 px-4 text-sm text-gray-800">{{ $task->description }}</td>
                                            <td class="py-2 px-4 text-sm text-gray-800">{{ $task->created_at->format('M d, Y') }}</td>
                                            <td class="py-2 px-4 text-sm">
                                                <button class="btn-success btn-approve-task text-xs px-2 py-1" data-task-id="{{ $task->id }}">Approve</button>
                                                <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded-md text-xs btn-reject-task" data-task-id="{{ $task->id }}">Reject</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        @endauth

    </div>

    <!-- Chart.js CDN -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <!-- Axios CDN for AJAX requests -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const wholesalerIdContainer = document.getElementById('wholesaler_id_container');
            const wholesalerIdSelect = document.getElementById('wholesaler_id');
            const forecastTypeWholesaler = document.getElementById('forecast_type_wholesaler');
            const forecastTypeGeneral = document.getElementById('forecast_type_general');

            function toggleWholesalerIdVisibility() {
                if (forecastTypeWholesaler.checked) {
                    wholesalerIdContainer.style.display = 'block';
                    wholesalerIdSelect.setAttribute('required', 'required');
                } else {
                    wholesalerIdContainer.style.display = 'none';
                    wholesalerIdSelect.removeAttribute('required');
                    wholesalerIdSelect.value = ''; // Clear selection when hidden
                }
            }

            // Initial call to set visibility based on initial state
            toggleWholesalerIdVisibility();

            // Add event listeners to radio buttons
            forecastTypeWholesaler.addEventListener('change', toggleWholesalerIdVisibility);
            forecastTypeGeneral.addEventListener('change', toggleWholesalerIdVisibility);


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
            @endisset

            // Event listener for Manual Generate Task buttons (existing)
            document.querySelectorAll('.btn-generate-task').forEach(button => {
                button.addEventListener('click', function() {
                    const taskType = this.dataset.taskType;
                    const product = this.dataset.product;
                    const wholesalerId = this.dataset.wholesalerId;
                    const startDate = this.dataset.startDate;
                    const endDate = this.dataset.endDate;
                    const predictedDemandTotal = this.dataset.predictedDemandTotal;
                    const forecastType = this.dataset.forecastType;

                    let confirmationMessage = `Are you sure you want to generate a task for "${taskType.replace('_', ' ')}" based on the forecast for ${product} `;
                    if (forecastType === 'wholesaler_specific' && wholesalerId) {
                        confirmationMessage += `for Wholesaler ${wholesalerId} `;
                    } else {
                        confirmationMessage += `(General Forecast) `;
                    }
                    confirmationMessage += `from ${startDate} to ${endDate}?`;


                    if (confirm(confirmationMessage)) {
                        axios.post('{{ route('demand.forecast.generate_task') }}', {
                            task_type: taskType,
                            product: product,
                            wholesaler_id: wholesalerId,
                            start_date: startDate,
                            end_date: endDate,
                            predicted_demand_total: predictedDemandTotal,
                            forecast_type: forecastType,
                            is_automated_suggestion: false // Explicitly false for manual generation
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

            // NEW: Event listener for Automated Suggest Tasks button
            const btnSuggestTasks = document.getElementById('btn-suggest-tasks');
            const suggestionLoadingMessage = document.getElementById('suggestion-loading-message');

            if (btnSuggestTasks) {
                btnSuggestTasks.addEventListener('click', function() {
                    if (confirm('Are you sure you want to run the automated task suggestion based on current forecasts? This may take a moment.')) {
                        btnSuggestTasks.disabled = true; // Disable button
                        suggestionLoadingMessage.classList.remove('hidden'); // Show loading message

                        axios.post('{{ route('demand.forecast.suggest_automated_tasks') }}')
                            .then(response => {
                                alert(response.data.message);
                                location.reload(); // Reload to show new suggested tasks
                            })
                            .catch(error => {
                                console.error('Error suggesting automated tasks:', error.response ? error.response.data : error.message);
                                alert(error.response.data.message || 'Failed to suggest automated tasks.');
                            })
                            .finally(() => {
                                btnSuggestTasks.disabled = false; // Re-enable button
                                suggestionLoadingMessage.classList.add('hidden'); // Hide loading message
                            });
                    }
                });
            }

            // NEW: Event listeners for Approve/Reject Suggested Tasks
            document.querySelectorAll('.btn-approve-task').forEach(button => {
                button.addEventListener('click', function() {
                    const taskId = this.dataset.taskId;
                    if (confirm('Are you sure you want to APPROVE this suggested task?')) {
                        axios.post(`/tasks/${taskId}/approve`)
                            .then(response => {
                                alert(response.data.message);
                                location.reload();
                            })
                            .catch(error => {
                                console.error('Error approving task:', error.response ? error.response.data : error.message);
                                alert(error.response.data.message || 'Failed to approve task.');
                            });
                    }
                });
            });

            document.querySelectorAll('.btn-reject-task').forEach(button => {
                button.addEventListener('click', function() {
                    const taskId = this.dataset.taskId;
                    if (confirm('Are you sure you want to REJECT this suggested task?')) {
                        axios.post(`/tasks/${taskId}/reject`)
                            .then(response => {
                                alert(response.data.message);
                                location.reload();
                            })
                            .catch(error => {
                                console.error('Error rejecting task:', error.response ? error.response.data : error.message);
                                alert(error.response.data.message || 'Failed to reject task.');
                            });
                    }
                });
            });
        });
    </script>


@endsection
