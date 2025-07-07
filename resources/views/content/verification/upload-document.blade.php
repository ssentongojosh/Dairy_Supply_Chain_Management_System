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

          <!-- Progress indicator -->
          <div class="mb-4 fade-in">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted small">Upload Progress</span>
              <span class="text-muted small" id="progressText">0% Complete</span>
            </div>
            <div class="progress" style="height: 6px;">
              <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" id="uploadProgress"></div>
            </div>
          </div>

          <div class="mb-4 form-step">
            <label for="national_id_input" class="form-label">
              <i class="ri-id-card-line me-2"></i>National ID (PDF)
            </label>
            {{-- Drag and Drop Zone for National ID --}}
            <div id="nationalIdDropZone" class="mt-1 p-4 border border-dashed rounded-3 text-center cursor-pointer position-relative">
              <div class="upload-overlay"></div>
              <div class="upload-spinner">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Uploading...</span>
                </div>
              </div>
              <i class="ri-id-card-line ri-3x text-muted icon-pulse"></i>
              <p class="mt-2 mb-0">
                <span class="fw-semibold text-primary">Click to upload</span> or drag and drop National ID PDF here
              </p>
              <p class="text-muted small mb-0">Maximum file size: 10MB</p>
              <p id="nationalIdFileDisplay" class="mt-2 text-muted small"></p>
              <div class="checkmark" id="nationalIdCheck"></div>
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

          <div class="mb-4 form-step">
            <label for="ursb_certificate_input" class="form-label">
              <i class="ri-file-certificate-line me-2"></i>URSB Certificate (PDF)
            </label>
            {{-- Drag and Drop Zone for URSB Certificate --}}
            <div id="ursbCertificateDropZone" class="mt-1 p-4 border border-dashed rounded-3 text-center cursor-pointer position-relative">
              <div class="upload-overlay"></div>
              <div class="upload-spinner">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Uploading...</span>
                </div>
              </div>
              <i class="ri-file-certificate-line ri-3x text-muted icon-pulse"></i>
              <p class="mt-2 mb-0">
                <span class="fw-semibold text-primary">Click to upload</span> or drag and drop URSB Certificate PDF here
              </p>
              <p class="text-muted small mb-0">Maximum file size: 10MB</p>
              <p id="ursbCertificateFileDisplay" class="mt-2 text-muted small"></p>
              <div class="checkmark" id="ursbCertificateCheck"></div>
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

          <div class="mb-4 form-step">
            <label for="business_description" class="form-label">
              <i class="ri-file-text-line me-2"></i>Business Description
            </label>
            <textarea class="form-control @error('business_description') is-invalid @enderror" id="business_description" name="business_description"
                      rows="4" required placeholder="Briefly describe your business activities...">{{ old('business_description') }}</textarea>
            @error('business_description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="d-grid form-step">
            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
              <span class="btn-text">
                <i class="ri-upload-2-line me-2"></i>
                Submit for Verification
              </span>
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
  /* Drop zone animations */
  #nationalIdDropZone,
  #ursbCertificateDropZone {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  #nationalIdDropZone.dragover,
  #ursbCertificateDropZone.dragover {
    border-color: var(--bs-primary);
    background-color: rgba(var(--bs-primary-rgb), 0.1);
    transform: scale(1.02);
    box-shadow: 0 8px 25px rgba(var(--bs-primary-rgb), 0.15);
  }

  /* Hover effects */
  #nationalIdDropZone:hover,
  #ursbCertificateDropZone:hover {
    border-color: var(--bs-primary);
    background-color: rgba(var(--bs-primary-rgb), 0.05);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  /* File uploaded state */
  .file-uploaded {
    border-color: var(--bs-success) !important;
    background-color: rgba(var(--bs-success-rgb), 0.1) !important;
  }

  .file-uploaded i {
    color: var(--bs-success) !important;
  }

  /* Loading spinner */
  .upload-spinner {
    display: none;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10;
  }

  .upload-overlay {
    display: none;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    z-index: 5;
  }

  /* Pulse animation for icons */
  @keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
  }

  .icon-pulse {
    animation: pulse 2s infinite;
  }

  /* Fade in animation for form elements */
  .fade-in {
    animation: fadeIn 0.8s ease-in;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Progress bar animation */
  .progress-bar-animated {
    background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
    background-size: 1rem 1rem;
    animation: progress-bar-stripes 1s linear infinite;
  }

  @keyframes progress-bar-stripes {
    0% { background-position: 1rem 0; }
    100% { background-position: 0 0; }
  }

  /* Submit button loading state */
  .btn-loading {
    position: relative;
    pointer-events: none;
  }

  .btn-loading .btn-text {
    opacity: 0;
  }

  .btn-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid transparent;
    border-top: 2px solid #fff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  /* Success checkmark animation */
  .checkmark {
    display: none;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--bs-success);
    position: relative;
    margin: 10px auto;
  }

  .checkmark::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 12px;
    height: 20px;
    border: solid white;
    border-width: 0 3px 3px 0;
    transform: translate(-50%, -60%) rotate(45deg);
  }

  .checkmark.show {
    display: block;
    animation: checkmarkScale 0.5s ease-in-out;
  }

  @keyframes checkmarkScale {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
  }

  /* Cursor pointer */
  .cursor-pointer {
    cursor: pointer;
  }

  /* Form step animation */
  .form-step {
    opacity: 0;
    transform: translateX(30px);
    animation: slideInRight 0.6s ease forwards;
  }

  .form-step:nth-child(1) { animation-delay: 0.1s; }
  .form-step:nth-child(2) { animation-delay: 0.2s; }
  .form-step:nth-child(3) { animation-delay: 0.3s; }
  .form-step:nth-child(4) { animation-delay: 0.4s; }

  @keyframes slideInRight {
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }
</style>
@endpush

@vite(['resources/js/upload-document.js'])
