@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container my-5">
   <div class="d-flex justify-content-between align-items-center mb-4">
       <h3 class="mb-4">Raw Material: {{ $item->name }}</h3>
       <!-- back button -->
        <a href="{{ route('plant_manager.inventory') }}" class="btn btn-primary">
            <i class="ri-arrow-left-line me-1"></i> Back
        </a>
   </div>


    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-4">
                <div class="card-body">
                    <h5 class="card-title">Current Stock</h5>
                    <p class="fw-bold">{{ $item->quantity }} {{ $item->unit }}</p>
                </div>
            </div>
        </div>

        <!-- image of the product-->
        <div class="col-md-4">
            <div class="card border-start border-4">
                <div class="card-body">
                    <img src="{{ asset('images/raw_materials/' . $item->image) }}" alt="{{ $item->name }}" class="card-img-top" style="height: 100px; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>

    <!-- Incoming Batches -->
    <h5 class="mt-4">📦 Incoming Batches</h5>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Batch ID</th>
                    <th>Quantity Received</th>
                    <th>Supplier</th>
                    <th>Delivered On</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($batches as $batch)
                    <tr>
                        <td>{{ $batch->batch_id }}</td>
                        <td>{{ $batch->quantity_received }} </td>
                        <td>{{ $batch->supplier->name ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($batch->delivered_on)->format('M d, Y') }}</td>
                        <td>{{ $batch->notes }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Usage History -->
    <h5 class="mt-4">🧪 Usage History</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Production ID</th>
                    <th>Quantity Used</th>
                    <th>Used On</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usages as $item)
                    <tr>
                        <td>{{ $item->production_id ?? 'Manual Task' }}</td>
                        <td>{{ $item->quantity_used }} </td>
                        <td>{{ \Carbon\Carbon::parse($item->used_on)->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
