 @extends('layouts.contentNavbarLayout')

@section('content')
<div class="container">

    {{-- Flash success or error messages --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <h2>My Orders to Factories</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>To (Plant Manager)</th>
                <th>Status</th>
                <th>Total (UGX)</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->seller->name ?? 'Unknown Plant Manager' }}</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td>{{ number_format($order->total_amount, 0) }}</td>
                <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                <td>
                    @if($order->payment_status === 'pending' || $order->payment_status === 'pending_verification')
                        <a href="{{ route('payments.verify.form', $order) }}" class="btn btn-outline-primary btn-sm mb-1">
                            Verify Payment
                        </a>
                    @endif

                    @if($order->status === 'pending')
                        <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="cancelled">
                            <button class="btn btn-outline-danger btn-sm">Cancel</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $orders->links() }}

</div>
@endsection
