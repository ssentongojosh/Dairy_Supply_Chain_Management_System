{{-- filepath: c:\xampp\htdocs\DSCMS\resources\views\content\verification\pending.blade.php --}}
@extends('layouts/contentNavbarLayout')

@section('title', 'Verification Status')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <!-- Display Success/Error Messages from Java Server -->
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="ri-check-circle-line me-2"></i>
        <strong>Verification Successful!</strong>
        <br>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="ri-error-warning-line me-2"></i>
        <strong>Verification Failed!</strong>
        <br>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="card text-center">
      <div class="card-body py-5">
        <div class="mb-4">
          @if(Auth::user()->verified)
            <i class="ri-check-circle-line text-success" style="font-size: 4rem;"></i>
          @else
            <i class="ri-time-line text-warning" style="font-size: 4rem;"></i>
          @endif
        </div>

        @if(Auth::user()->verified)
          <h4 class="card-title mb-3 text-success">✅ Account Verified!</h4>
          <p class="card-text mb-4 text-success">
            <strong>Congratulations!</strong> Your business documents have been successfully verified by our automated system.
            You can now access all features of the platform.
          </p>

          @if(Auth::user()->verification_notes)
            <div class="alert alert-success">
              <i class="ri-information-line me-2"></i>
              <strong>Java Server Response:</strong><br>
              {{ Auth::user()->verification_notes }}
            </div>
          @endif

          <div class="mt-4">
            <a href="{{ route('dashboard.analytics') }}" class="btn btn-success btn-lg">
              <i class="ri-dashboard-line me-2"></i>
              Access Dashboard
            </a>
          </div>
        @else
          <h4 class="card-title mb-3 text-warning">⏳ Verification Pending</h4>
          <p class="card-text mb-4">
            Your business documents have been submitted and are being processed by our verification system.
          </p>

          @if(Auth::user()->verification_notes)
            <div class="alert alert-warning">
              <i class="ri-information-line me-2"></i>
              <strong>Status from Java Server:</strong><br>
              {{ Auth::user()->verification_notes }}
            </div>
          @endif

          <div class="mt-4">
            <a href="{{ route('verification.upload') }}" class="btn btn-primary me-2">
              <i class="ri-upload-2-line me-2"></i>
              Upload New Documents
            </a>
            <button onclick="location.reload()" class="btn btn-outline-primary">
              <i class="ri-refresh-line me-2"></i>
              Check Status
            </button>
          </div>

          <p class="text-muted small mt-3">
            <i class="ri-mail-line me-1"></i>
            You will receive an email notification once your account is verified.
          </p>
        @endif

        <hr class="my-4">

        <form action="{{ route('logout') }}" method="POST" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-outline-secondary">
            <i class="ri-logout-box-line me-2"></i>
            Logout
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
