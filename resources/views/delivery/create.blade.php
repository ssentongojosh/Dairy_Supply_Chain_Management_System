@extends('layouts.contentNavbarLayout')

@section('content')
<div id="delivery-form" class="transition duration-300 min-h-screen flex justify-center items-center px-4">
    <div class="max-w-lg w-full bg-white p-6 rounded shadow-lg text-center">
        
            <h2 class="text-xl font-bold mb-4 text-center">Delivery Form</h2>

            <form id="deliveryForm" method="POST" action="{{ route('delivery.store') }}">
                @csrf

                <!-- Recipient -->
                <div class="mb-4">
                    <label class="block font-semibold">Recipient:</label>
                    <select name="receiver_id" required class="w-full border p-2 rounded">
                        @foreach($users as $user)
                            @if($user->id !== auth()->id())
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Order ID -->
                <div class="mb-4">
                    <label class="block font-semibold">Order ID:</label>
                    <input type="text" name="order_id" class="w-full border p-2 rounded" required>
                </div>

                <!-- Item Name -->
                <div class="mb-4">
                    <label class="block font-semibold">Item Name:</label>
                    <input type="text" name="item_name" class="w-full border p-2 rounded" required>
                </div>

                <!-- Quantity -->
                <div class="mb-4">
                    <label class="block font-semibold">Quantity:</label>
                    <input type="number" name="quantity" class="w-full border p-2 rounded" required>
                </div>

                <!-- Delivery Date -->
                <div class="mb-4">
                    <label class="block font-semibold">Delivery Date:</label>
                    <input type="date" name="delivery_date" class="w-full border p-2 rounded" required>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="block font-semibold">Description:</label>
                    <textarea name="description" class="w-full border p-2 rounded" required></textarea>
                </div>
                  
                <div class="d-flex justify-content-between align-items-center mb-4">
                <button type="submit" id="submit-delivery" class="btn btn-success btn-lg d-flex align-items-center gap-2 shadow-sm">
                    <i class="ri-send-plane-line"></i>Send Delivery
                </button>

                  <!-- back buttons -->
                 <span>  
                    <a href="{{ route('inventory.raw_materials') }}" class="btn btn-warning">
                    <i class="ri-arrow-left-line me-1"></i> Back to raw materials </a>

                    <a href="{{ route('delivery.mine') }}" class="btn btn-warning">
                    <i class="ri-arrow-left-line me-1"></i> Back to my deliveries </a>
                 </span>  
                </div>
            </form>
        
    </div>
</div>


@endsection

@push('scripts')
<script>
document.getElementById('deliveryForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const modal = document.getElementById('delivery-modal');
    const statusText = document.getElementById('delivery-status');
    const closeBtn = document.getElementById('close-modal');
    const cancelBtn = document.getElementById('cancel-delivery');

    // Show modal and blur form
    modal.classList.remove('hidden');
    document.getElementById('delivery-form').classList.add('blur-sm');
    statusText.textContent = 'Goods in transit — wait for arrival confirmation.';

    // Submit form via fetch
    fetch(form.action, {
        method: form.method,
        body: new FormData(form),
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        }
    })
    .then(res => res.text()) // Get plain delivery ID
    .then(deliveryId => {
        checkStatus(deliveryId.trim());
    })
    .catch(() => {
        statusText.textContent = 'Submission failed. Please try again.';
    });

    // Exit buttons
    closeBtn.addEventListener('click', () => window.location.href = "{{ route('delivery.create') }}");
    cancelBtn.addEventListener('click', () => window.location.href = "{{ route('delivery.create') }}");
});

function checkStatus(deliveryId) {
    const statusText = document.getElementById('delivery-status');
    const closeBtn = document.getElementById('close-modal');
    const cancelBtn = document.getElementById('cancel-delivery');

    const interval = setInterval(() => {
        fetch(`/api/delivery/${deliveryId}/status`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'approved') {
                    statusText.textContent = '✅ Delivery approved — thank you!';
                    closeBtn.classList.remove('hidden');
                    clearInterval(interval);
                } else if (data.status === 'rejected') {
                    statusText.textContent = '❌ Delivery rejected — check feedback.';
                    cancelBtn.classList.remove('hidden');
                    clearInterval(interval);
                }
            });
    }, 3000);
}
</script>
@endpush
