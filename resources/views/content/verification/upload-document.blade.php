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

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('verification.upload.submit') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="mb-4">
            <label for="business_document" class="form-label">Business Document (PDF)</label>
            <input type="file" class="form-control" id="business_document" name="business_document"
                   accept=".pdf" required>
            <div class="form-text">
              Upload a PDF containing business registration, license, or other relevant business documents.
              Maximum file size: 10MB
            </div>
          </div>

          <div class="mb-4">
            <label for="business_description" class="form-label">Business Description</label>
            <textarea class="form-control" id="business_description" name="business_description"
                      rows="4" required placeholder="Briefly describe your business activities..."></textarea>
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
