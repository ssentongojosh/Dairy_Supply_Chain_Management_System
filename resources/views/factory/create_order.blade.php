@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Create Order to Supplier</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('factory.orders.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="seller_id" class="form-label">Select Supplier</label>
                <select name="seller_id" id="seller_id" class="form-select" required>
                    <option value="" disabled selected>Choose a supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('seller_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <h4>Order Items</h4>
            <div id="order-items">
                <div class="order-item row mb-3">
                    <div class="col-md-6">
                        <label>Product</label>
                        <select name="items[0][product_id]" class="form-select" required>
                            <option value="" disabled selected>Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('items.0.product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} (${{ number_format($product->price, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Quantity</label>
                        <input type="number" name="items[0][quantity]" class="form-control" min="1" value="{{ old('items.0.quantity', 1) }}" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-remove-item">Remove</button>
                    </div>
                </div>
            </div>

            <button type="button" id="btn-add-item" class="btn btn-secondary mb-3">Add Another Item</button>

            <div>
                <button type="submit" class="btn btn-primary">Send Order</button>
                <a href="{{ route('factory.dashboard') }}" class="btn btn-link">Cancel</a>
            </div>
            <div class="mb-3">
    <label for="payment_method" class="form-label">Payment Method</label>
    <select name="payment_method" id="payment_method" class="form-select" required>
        <option value="" disabled selected>Choose payment method</option>
        <option value="mpesa">M-Pesa</option>
        <option value="bank">Bank Transfer</option>
        <option value="credit">Credit (30 days)</option>
    </select>
</div>

<div id="mpesa-details" class="mb-3" style="display: none;">
    <label for="mpesa_number" class="form-label">M-Pesa Phone Number</label>
    <input type="text" name="mpesa_number" id="mpesa_number" class="form-control" placeholder="e.g. 254712345678">
</div>

<div id="bank-details" class="mb-3" style="display: none;">
    <label for="bank_reference" class="form-label">Bank Reference Number</label>
    <input type="text" name="bank_reference" id="bank_reference" class="form-control">
</div>
<div class="card mb-3">
    <div class="card-header">Order Summary</div>
    <div class="card-body">
        <div id="order-summary">
            <p>Total Items: <span id="total-items">0</span></p>
            <p>Subtotal: Ksh <span id="subtotal">0.00</span></p>
            <p>Tax (16%): Ksh <span id="tax">0.00</span></p>
            <p class="fw-bold">Total: Ksh <span id="total">0.00</span></p>
        </div>
    </div>
</div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let itemIndex = {{ old('items') ? count(old('items')) : 1 }};
            const orderItemsContainer = document.getElementById('order-items');
            const btnAddItem = document.getElementById('btn-add-item');

            btnAddItem.addEventListener('click', () => {
                const newItem = document.createElement('div');
                newItem.classList.add('order-item', 'row', 'mb-3');

                newItem.innerHTML = `
                    <div class="col-md-6">
                        <label>Product</label>
                        <select name="items[${itemIndex}][product_id]" class="form-select" required>
                            <option value="" disabled selected>Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} (${{ number_format($product->price, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Quantity</label>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-remove-item">Remove</button>
                    </div>
                    <div class="mb-3">
    <label for="notes" class="form-label">Order Notes (Optional)</label>
    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
</div>
                `;

                orderItemsContainer.appendChild(newItem);
                itemIndex++;
            });

            orderItemsContainer.addEventListener('click', e => {
                if (e.target.classList.contains('btn-remove-item')) {
                    const item = e.target.closest('.order-item');
                    item.remove();
                }
            });
        });

        // Payment method toggle
const paymentMethod = document.getElementById('payment_method');
const mpesaDetails = document.getElementById('mpesa-details');
const bankDetails = document.getElementById('bank-details');

paymentMethod.addEventListener('change', () => {
    mpesaDetails.style.display = 'none';
    bankDetails.style.display = 'none';
    
    if (paymentMethod.value === 'mpesa') {
        mpesaDetails.style.display = 'block';
    } else if (paymentMethod.value === 'bank') {
        bankDetails.style.display = 'block';
    }
});
// Price calculation
function calculateTotal() {
    let totalItems = 0;
    let subtotal = 0;
    
    document.querySelectorAll('.order-item').forEach(item => {
        const productSelect = item.querySelector('select[name^="items"]');
        const quantityInput = item.querySelector('input[name^="items"]');
        
        if (productSelect && quantityInput) {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const priceText = selectedOption.text.match(/\$([\d.]+)/);
            
            if (priceText && priceText[1]) {
                const price = parseFloat(priceText[1]);
                const quantity = parseInt(quantityInput.value) || 0;
                
                totalItems += quantity;
                subtotal += price * quantity;
            }
        }
    });
    
    const tax = subtotal * 0.16;
    const total = subtotal + tax;
    
    document.getElementById('total-items').textContent = totalItems;
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('tax').textContent = tax.toFixed(2);
    document.getElementById('total').textContent = total.toFixed(2);
}

// Recalculate when products or quantities change
orderItemsContainer.addEventListener('change', (e) => {
    if (e.target.name.includes('product_id') || e.target.name.includes('quantity')) {
        calculateTotal();
    }
});

// Initial calculation
calculateTotal();
document.querySelector('form').addEventListener('submit', (e) => {
    const paymentMethod = document.getElementById('payment_method').value;
    
    if (paymentMethod === 'mpesa' && !document.getElementById('mpesa_number').value) {
        e.preventDefault();
        alert('Please enter your M-Pesa phone number');
        return;
    }
    
    if (paymentMethod === 'bank' && !document.getElementById('bank_reference').value) {
        e.preventDefault();
        alert('Please enter your bank reference number');
        return;
    }
    
    // Additional validation if needed
});
    </script>
@endsection
