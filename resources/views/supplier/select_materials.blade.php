@extends('layouts/contentNavbarLayout')

@section('content')
<div class="container mx-auto px-4">
    <h2 class="text-2xl font-semibold mb-6" style="text-align:center;">Select Raw Materials You Will Supply</h2>

    <!--back button-->
        <a href="{{ route('supplier.dashboard') }}" class="btn btn-primary">
            <i class="ri-arrow-left-line me-1"></i> Back
        </a><br><br>

    <form action="{{ route('supplier.selection.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach ($rawMaterials as $material)
                <label class="cursor-pointer border rounded-xl overflow-hidden shadow hover:shadow-lg transition-all duration-200 p-4 flex flex-col items-center text-center bg-white relative">

                    {{-- Name --}}
                    <span class="font-medium text-gray-800">{{ $material->name }}</span>

                    {{-- Checkbox --}}
                    <input type="checkbox" name="raw_materials[]" value="{{ $material->id }}"
                        {{ $selectedMaterials->contains($material->id) ? 'checked' : '' }}
                        class="absolute top-2 right-2 w-5 h-5 text-blue-600 border-gray-300 rounded">
                </label>
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <button type="submit" class="bg-blue-600 text-black px-6 py-2 rounded shadow hover:bg-blue-700">
                Save My Selection
            </button>
        </div>
    </form>
</div>
@endsection
