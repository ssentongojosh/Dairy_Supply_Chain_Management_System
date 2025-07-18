@extends('layouts.contentNavbarLayout')

@section('content')
<div class="container">
    <h2>Edit Recipe for: {{ $product->name }}</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('recipes.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')

        <table class="table">
            <thead>
                <tr>
                    <th>Raw Material</th>
                    <th>Quantity Required</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rawMaterials as $rawMaterial)
                @php
                    // Find if this raw material is already in the recipe
                    $existingItem = $recipeItems->firstWhere('raw_material_id', $rawMaterial->id);
                    $qty = $existingItem ? $existingItem->quantity_required : 0;
                @endphp
                <tr>
                    <td>
                        <input type="checkbox" name="raw_materials[]" value="{{ $rawMaterial->id }}" 
                            {{ $qty > 0 ? 'checked' : '' }}>
                        {{ $rawMaterial->name }}
                    </td>
                    <td>
                        <input type="number" name="quantities[]" value="{{ $qty }}" min="0" step="0.01" class="form-control" 
                               {{ $qty > 0 ? '' : 'disabled' }}>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Save Recipe</button>
    </form>
</div>

<script>
    // Enable/disable quantity inputs based on checkbox
    document.querySelectorAll('input[type="checkbox"][name="raw_materials[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const quantityInput = this.closest('tr').querySelector('input[name="quantities[]"]');
            quantityInput.disabled = !this.checked;
            if (!this.checked) quantityInput.value = 0;
        });
    });
</script>
@endsection
