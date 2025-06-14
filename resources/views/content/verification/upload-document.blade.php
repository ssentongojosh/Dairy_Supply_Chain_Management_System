{{-- filepath: c:\xampp\htdocs\DSCMS\resources\views\content\verification\upload-document.blade.php --}}
@extends('layouts/contentNavbarLayout')

@section('title', 'Business Verification')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h4 class="mb-0">Business Verification Required</h4>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <i class="ri-information-line me-2"></i>
          To access the system, please upload a PDF document containing your business details for verification.
        </div>

        @if (session('success'))
          <div class="alert alert-success">
            {{ session('success') }}
          </div>
        @endif

        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('verification.upload.submit') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
          @csrf

          <div class="mb-4">
            <label for="business_document_input" class="form-label">Business Document (PDF)</label>
            {{-- Drag and Drop Zone --}}
            <div id="dropZone" class="mt-1 p-4 border border-dashed rounded-3 text-center cursor-pointer">
              <i class="ri-upload-cloud-2-line ri-3x text-muted"></i>
              <p class="mt-2 mb-0">
                <span class="fw-semibold text-primary">Click to upload</span> or drag and drop PDF here
              </p>
              <p class="text-muted small mb-0">Maximum file size: 10MB</p>
              <p id="fileNameDisplay" class="mt-2 text-muted small"></p>
            </div>
            {{-- Hidden actual file input --}}
            <input type="file" class="d-none" id="business_document_input" name="business_document" accept=".pdf" required>
            <div class="form-text mt-1">
              Upload a PDF containing business registration, license, or other relevant business documents.
            </div>
            @error('business_document')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <label for="business_description" class="form-label">Business Description</label>
            <textarea class="form-control @error('business_description') is-invalid @enderror" id="business_description" name="business_description"
                      rows="4" required placeholder="Briefly describe your business activities...">{{ old('business_description') }}</textarea>
            @error('business_description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary">
              <i class="ri-upload-2-line me-2"></i>
              Submit for Verification
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('page-styles')
<style>
  #dropZone.dragover {
    border-color: var(--bs-primary);
    background-color: rgba(var(--bs-primary-rgb), 0.05);
  }
  .cursor-pointer {
    cursor: pointer;
  }
</style>
@endpush

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('business_document_input');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    // const uploadForm = document.getElementById('uploadForm'); // If needed for manual submission trigger

    if (dropZone && fileInput && fileNameDisplay) {
        // Trigger file input click when dropZone is clicked
        dropZone.addEventListener('click', () => {
            fileInput.click();
        });

        dropZone.addEventListener('dragover', (event) => {
            event.preventDefault(); // Prevent default behavior (Prevent file from being opened)
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', (event) => {
            event.preventDefault();
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (event) => {
            event.preventDefault();
            dropZone.classList.remove('dragover');

            const files = event.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });

        fileInput.addEventListener('change', (event) => {
            const files = event.target.files;
            if (files.length > 0) {
                handleFile(files[0]);
            } else {
                fileNameDisplay.textContent = ''; // Clear display if no file selected
            }
        });

        function handleFile(file) {
            if (file.type === "application/pdf") {
                // Create a new FileList object to assign to the input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;

                fileNameDisplay.textContent = `Selected file: ${file.name}`;
                fileNameDisplay.classList.remove('text-danger');
                fileNameDisplay.classList.add('text-muted');

                // Clear any existing validation error for business_document
                const errorDiv = fileInput.closest('.mb-4').querySelector('.invalid-feedback.d-block');
                if (errorDiv) {
                    errorDiv.textContent = '';
                    errorDiv.classList.remove('d-block');
                }
                fileInput.removeAttribute('required'); // Temporarily remove required to allow form submission if a file is now present
                fileInput.setAttribute('required', 'required'); // Re-add for subsequent validation if needed

            } else {
                fileNameDisplay.textContent = 'Invalid file type. Please select or drop a PDF.';
