@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Order History</h2>
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
        
        <div class="card-body">
            <table class="table">
                <!-- Table headers and rows showing orders -->
            </table>
            
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection