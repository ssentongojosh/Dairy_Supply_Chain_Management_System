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

        @if(session('auto_redirect'))
          <div class="mt-3 p-3 bg-light rounded">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm text-success me-2" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <span class="text-muted">Redirecting to your dashboard in <span id="countdown">5</span> seconds...</span>
              </div>
              <a href="@if(Auth::user()->role === 'admin'){{ route('dashboard.analytics') }}@elseif(Auth::user()->role === 'retailer'){{ route('dashboard.retailer') }}@elseif(Auth::user()->role === 'wholesaler'){{ route('wholesaler.dashboard') }}@elseif(Auth::user()->role === 'farmer'){{ route('farmer.dashboard') }}@elseif(Auth::user()->role === 'driver'){{ route('driver.dashboard') }}@elseif(Auth::user()->role === 'warehouse_manager'){{ route('warehouse.dashboard') }}@elseif(Auth::user()->role === 'executive'){{ route('executive.dashboard') }}@elseif(Auth::user()->role === 'inspector'){{ route('inspector.dashboard') }}@elseif(Auth::user()->role === 'quality_assurance'){{ route('quality.dashboard') }}@else{{ route('dashboard.analytics') }}@endif" class="btn btn-sm btn-success">
                <i class="ri-dashboard-line me-1"></i>
                Go Now
              </a>
            </div>
          </div>
        @endif

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="ri-error-warning-line me-2"></i>
        <strong>Verification Failed!</strong>
        <br>{{ session('error') }}

        <!-- Enhanced error guidance -->
        <div class="mt-3 p-3 bg-light rounded">
          <small class="text-muted">
            <strong>Common reasons for verification failure:</strong>
            <ul class="mb-2 mt-2">
              <li>Document quality is too poor or blurry</li>
              <li>Wrong document type uploaded</li>
              <li>Document is not a valid National ID or URSB Certificate</li>
              <li>File format is not supported</li>
            </ul>
            <strong>💡 Tips for successful verification:</strong>
            <ul class="mb-0 mt-2">
              <li>Ensure documents are clear and readable</li>
              <li>Upload actual government-issued documents</li>
              <li>Make sure PDFs are not corrupted</li>
            </ul>
          </small>
        </div>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if(session('warning'))
      <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <i class="ri-alert-line me-2"></i>
        <strong>Manual Review Required!</strong>
        <br>{{ session('warning') }}
        <div class="mt-2">
          <small class="text-muted">Our team will review your documents within 24-48 hours and contact you via email.</small>
        </div>
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
          <h4 class="card-title mb-3 text-success">🎉 Account Verified!</h4>
          <p class="card-text mb-4 text-success">
            <strong>Congratulations!</strong> Your business documents have been successfully verified.
            You now have full access to all platform features.
          </p>

          @if(Auth::user()->verification_notes && strpos(Auth::user()->verification_notes, 'successfully') !== false)
            <div class="alert alert-success">
              <i class="ri-check-circle-line me-2"></i>
              <strong>Verification Complete:</strong><br>
              Both your National ID and URSB Certificate have been successfully verified by our automated system.
            </div>
          @endif

          <div class="mt-4">
            <a href="@if(Auth::user()->role === 'admin'){{ route('dashboard.analytics') }}@elseif(Auth::user()->role === 'retailer'){{ route('dashboard.retailer') }}@elseif(Auth::user()->role === 'wholesaler'){{ route('wholesaler.dashboard') }}@elseif(Auth::user()->role === 'farmer'){{ route('farmer.dashboard') }}@elseif(Auth::user()->role === 'driver'){{ route('driver.dashboard') }}@elseif(Auth::user()->role === 'warehouse_manager'){{ route('warehouse.dashboard') }}@elseif(Auth::user()->role === 'executive'){{ route('executive.dashboard') }}@elseif(Auth::user()->role === 'inspector'){{ route('inspector.dashboard') }}@elseif(Auth::user()->role === 'quality_assurance'){{ route('quality.dashboard') }}@else{{ route('dashboard.analytics') }}@endif" class="btn btn-success btn-lg">
              <i class="ri-dashboard-line me-2"></i>
              Go to Dashboard
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
              <strong>Verification Status:</strong><br>
              Your documents could not be automatically verified. Please ensure your documents meet the requirements below and try uploading again.
              @if(Auth::user()->updated_at)
                <small class="d-block mt-2 text-muted">
                  <i class="ri-time-line me-1"></i>
                  Last attempt: {{ Auth::user()->updated_at->format('M j, Y \a\t g:i A') }}
                </small>
              @endif
            </div>

            <!-- Additional help for failed verification -->
            <div class="alert alert-info">
              <i class="ri-lightbulb-line me-2"></i>
              <strong>Need Help?</strong>
              <p class="mb-2 mt-2">If your documents are being rejected:</p>
              <ul class="mb-2">
                <li>Ensure your National ID is a clear, valid government-issued document</li>
                <li>URSB Certificate should be an official business registration document</li>
                <li>All documents must be in PDF format and clearly readable</li>
                <li>Avoid using scanned copies if possible - use original digital documents</li>
              </ul>
            </div>
          @else
            <!-- Show default pending message if no specific notes -->
            <div class="alert alert-info">
              <i class="ri-information-line me-2"></i>
              <strong>What happens next?</strong>
              <ul class="mb-0 mt-2">
                <li>Our AI system processes your documents automatically</li>
                <li>If automatic verification fails, our team reviews manually</li>
                <li>You'll receive an email notification with the results</li>
                <li>Typical processing time: 5 minutes (automatic) or 24-48 hours (manual)</li>
              </ul>
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

@if(session('auto_redirect'))
<script>
// Auto-redirect countdown for successful verification
let countdown = 5;
const countdownElement = document.getElementById('countdown');

function updateCountdown() {
    if (countdown > 0) {
        countdownElement.textContent = countdown;
        countdown--;
        setTimeout(updateCountdown, 1000);
    } else {
        // Redirect to dashboard
        @if(Auth::user()->role === 'admin')
            window.location.href = "{{ route('dashboard.analytics') }}";
        @elseif(Auth::user()->role === 'retailer')
            window.location.href = "{{ route('dashboard.retailer') }}";
        @elseif(Auth::user()->role === 'wholesaler')
            window.location.href = "{{ route('wholesaler.dashboard') }}";
        @elseif(Auth::user()->role === 'farmer')
            window.location.href = "{{ route('farmer.dashboard') }}";
        @elseif(Auth::user()->role === 'driver')
            window.location.href = "{{ route('driver.dashboard') }}";
        @elseif(Auth::user()->role === 'warehouse_manager')
            window.location.href = "{{ route('warehouse.dashboard') }}";
        @elseif(Auth::user()->role === 'executive')
            window.location.href = "{{ route('executive.dashboard') }}";
        @elseif(Auth::user()->role === 'inspector')
            window.location.href = "{{ route('inspector.dashboard') }}";
        @elseif(Auth::user()->role === 'quality_assurance')
            window.location.href = "{{ route('quality.dashboard') }}";
        @else
            window.location.href = "{{ route('dashboard.analytics') }}";
        @endif
    }
}

// Start the countdown
updateCountdown();
</script>
@endif

@endsection
