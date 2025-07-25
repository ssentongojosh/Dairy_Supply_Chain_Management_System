@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">My Deliveries</h4>
        <span>
        <!--back button-->
        <a href="{{ route('inventory.raw_materials') }}" class="btn btn-warning">
            <i class="ri-arrow-left-line me-1"></i> Back
        </a></span>
    </div>

    @if($delivery->isEmpty())
        <div class="alert alert-info">You haven't submitted any deliveries yet.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($delivery as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                <span class="
                                    d-inline-block px-3 py-1 rounded
                                    text-white
                                    @if($item->status == 'delivered') bg-success
                                    @elseif($item->status == 'pending') bg-warning
                                    @elseif($item->status == 'rejected') bg-danger
                                    @else bg-secondary
                                    @endif
                                    ">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>{{ $item->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>

@endsection
