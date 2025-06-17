{{-- filepath: c:\xampp\htdocs\DSCMS\resources\views\content\verification\pending.blade.php --}}
@extends('layouts/contentNavbarLayout')

@section('title', 'Verification Pending')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card text-center">
      <div class="card-body py-5">
        <div class="mb-4">
          <i class="ri-time-line text-warning" style="font-size: 4rem;"></i>
        </div>
        <h4 class="card-title mb-3">Verification Pending</h4>
        <p class="card-text mb-4">
          Your business document has been submitted successfully.
          Our team is reviewing your information and will verify your account shortly.
        </p>
        <p class="text-muted small">
          You will receive an email notification once your account is verified.
        </p>
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
