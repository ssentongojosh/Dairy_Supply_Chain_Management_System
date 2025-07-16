@extends('layouts.contentNavbarLayout')

@section('content')
<h2 class="text-xl font-bold mb-4">Order Dashboard</h2>

<div class="grid grid-cols-2 gap-6">
    <!-- Orders Placed -->
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold text-lg mb-2">Your Placed Orders</h3>
        <p>Total: {{ $stats['placed']['total'] }}</p>
        <p>Pending: {{ $stats['placed']['pending'] }}</p>
        <p>Approved: {{ $stats['placed']['approved'] }}</p>
        <p>Completed: {{ $stats['placed']['completed'] }}</p>
    </div>

    <!-- Orders Received -->
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold text-lg mb-2">Orders You Received</h3>
        <p>Total: {{ $stats['received']['total'] }}</p>
        <p>Pending: {{ $stats['received']['pending'] }}</p>
        <p>Approved: {{ $stats['received']['approved'] }}</p>
        <p>Completed: {{ $stats['received']['completed'] }}</p>
    </div>
</div>
@endsection