<!-- layout -->
 @extends('layouts.contentNavbarLayout')

 @section('head')
 <style>
.card.hover-lift {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card.hover-lift:hover {
  transform: translateY(-10px) scale(1.02); /* lift up */
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); /* soft shadow */
  cursor: pointer;
}
</style>
@endsection

@section('content')
<div class="container my-5">
  <h2 class="mb-4 text-center font-bold text-xl">Products Available for Purchase</h2>

  <div class="row">
    @forelse ($products as $product)
      <div class="col-md-3 mb-4">
        <div class="card hover-lift h-100 shadow-sm" style="max-height: 350px; ">
          <div style="overflow: hidden;">
          <!-- Placeholder image only -->
          <img src="{{ asset('images/products/' . $product->image) }}" alt="{{ $product->name }}" class="card-img-top" style="height: 100px; object-fit: cover;">
          </div>

          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ $product->name }}</h5>
            <p class="card-text text-muted">Quantity: {{ number_format($product->quantity) }} {{ $product->unit }}</p>
            <p class="card-text fw-bold">Price: UGX {{ number_format($product->price) }} /</p>
            <!--a href="{{ route('delivery.create', ['product_id' => $product->id]) }}" class="btn btn-primary mt-auto">Order Now</a-->
            <button type="button" onclick="addToOrder({{ $product->id }}, '{{ $product->name }}')" class="btn btn-primary btn-sm">
              Order Now
            </button>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <p class="text-center text-muted">No products are currently available for order.</p>
      </div>
    @endforelse
  </div>
</div>
@endsection

<!-- Business Segmentation Recommendations Popup -->
<!-- Include jQuery (from CDN) if not already present -->
<!-- Include SweetAlert2 (from CDN) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Remove or comment out the alert after testing
    // alert("JS is working!");

    var businessData = {
        annual_revenue: 10000000,
        order_frequency: 12,
        total_quantity_purchased: 1200,
        location: "Kampala",
        business_type: "Wholesaler"
    };

    $.ajax({
        url: '/api/business-segment',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(businessData),
        success: function(response) {
            let recs = response.recommendations ? response.recommendations.join(", ") : "No recommendations";
            Swal.fire({
                icon: 'info',
                title: 'Welcome!',
                html: '<b>People in your segment have bought these products too:</b><br><br><span style="font-size:1.1em;">' + recs + '</span>',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'swal-wide'
                }
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: 'Could not fetch recommendations. Please try again later.'
            });
        }
    });
});
</script>
