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
            <label for="national_id_input" class="form-label">National ID (PDF)</label>
            {{-- Drag and Drop Zone for National ID --}}
            <div id="nationalIdDropZone" class="mt-1 p-4 border border-dashed rounded-3 text-center cursor-pointer">
              <i class="ri-id-card-line ri-3x text-muted"></i>
              <p class="mt-2 mb-0">
                <span class="fw-semibold text-primary">Click to upload</span> or drag and drop National ID PDF here
              </p>
              <p class="text-muted small mb-0">Maximum file size: 10MB</p>
              <p id="nationalIdFileDisplay" class="mt-2 text-muted small"></p>
            </div>
            {{-- Hidden actual file input --}}
            <input type="file" class="d-none" id="national_id_input" name="national_id" accept=".pdf" required>
            <div class="form-text mt-1">
              Upload a PDF of your National ID or other valid identification document.
            </div>
            @error('national_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <label for="ursb_certificate_input" class="form-label">URSB Certificate (PDF)</label>
            {{-- Drag and Drop Zone for URSB Certificate --}}
            <div id="ursbCertificateDropZone" class="mt-1 p-4 border border-dashed rounded-3 text-center cursor-pointer">
              <i class="ri-file-certificate-line ri-3x text-muted"></i>
              <p class="mt-2 mb-0">
                <span class="fw-semibold text-primary">Click to upload</span> or drag and drop URSB Certificate PDF here
              </p>
              <p class="text-muted small mb-0">Maximum file size: 10MB</p>
              <p id="ursbCertificateFileDisplay" class="mt-2 text-muted small"></p>
            </div>
            {{-- Hidden actual file input --}}
            <input type="file" class="d-none" id="ursb_certificate_input" name="ursb_certificate" accept=".pdf" required>
            <div class="form-text mt-1">
              Upload a PDF of your URSB (Uganda Registration Services Bureau) Certificate or business registration document.
            </div>
            @error('ursb_certificate')
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
  #nationalIdDropZone.dragover,
  #ursbCertificateDropZone.dragover {
    border-color: var(--bs-primary);
    background-color: rgba(var(--bs-primary-rgb), 0.05);
  }
  .cursor-pointer {
    cursor: pointer;
  }
</style>
@endpush

@vite(['resources/js/upload-document.js'])
