 @extends('layouts.contentNavbarLayout')

@section('content')
<div class="container">
    <h2 class="mb-4" style="text-align:center;">Product Recipes</h2>
    <!-- back button -->
    <div class="d-flex justify-content-end mb-3"><a href="{{ route('plant_manager.dashboard') }}" class="btn btn-outline-primary">
        Back
    </a></div>

     @foreach ($products as $product)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <strong>{{ $product->name }}</strong>
                <div>
                    <a href="{{ route('recipe.create', $product->id) }}" class="btn btn-sm btn-warning me-2">Update Recipe</a>
                    <button data-product-id="{{ $product->id }}"
                            data-product-name="{{ $product->name }}" class="btn btn-sm btn-success produce-btn">Produce</button>
                </div>
            </div>
            <div class="card-body p-0">
                @if ($product->recipeItems->count())
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Raw Material</th>
                                <th>Quantity Required (per unit)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($product->recipeItems as $item)
                                <tr>
                                    <td>{{ $item->rawMaterial->name ?? 'N/A' }}</td>
                                    <td>{{ $item->quantity_required }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-3">
                        <em>No recipe set for this product.</em>
                        <a href="{{ route('recipe.create', $product->id) }}" class="btn btn-sm btn-outline-secondary">Create Recipe</a>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>

<!-- Modal -->
<div class="modal fade" id="produceModal" tabindex="-1" aria-labelledby="produceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="produceForm" method="POST" action="{{ route('production.store') }}">
        @csrf
        <input type="hidden" name="product_id" id="modalProductId">

        <div class="modal-header">
          <h5 class="modal-title" id="produceModalLabel">Produce Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <p id="recipeWarning" class="text-danger d-none"></p>

          <div class="mb-3">
            <label for="quantityToProduce" class="form-label">Quantity to Produce</label>
            <input type="number" class="form-control" id="quantityToProduce" name="quantity" required min="1">
          </div>

          <div id="materialWarning" class="alert alert-warning d-none">
            Not enough raw materials to produce this quantity.
          </div>
        </div>

        <div class="modal-footer">
          <a href="#" id="createRecipeLink" class="btn btn-secondary d-none">Create Recipe</a>
          <button type="submit" class="btn btn-success">Produce</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('.produce-btn').on('click', function () {
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');

        // Reset modal
        $('#modalProductId').val(productId);
        $('#quantityToProduce').val('');
        $('#materialWarning').addClass('d-none');
        $('#recipeWarning').addClass('d-none');
        $('#createRecipeLink').addClass('d-none');

        // AJAX check for recipe and materials
        $.get(`/api/check-production/${productId}`, function (response) {
            if (!response.has_recipe) {
                $('#recipeWarning').text('This product has no recipe. Please create one.');
                $('#recipeWarning').removeClass('d-none');
                $('#createRecipeLink')
                    .attr('href', `/recipe/${productId}/create`)
                    .removeClass('d-none');
            } else if (!response.has_enough_materials) {
                $('#materialWarning').removeClass('d-none');
            }
        });

        $('#produceModal').modal('show');
    });
});
</script>

