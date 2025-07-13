{{-- filepath: c:\xampp\htdocs\DSCMS\resources\views\content\verification\success.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'Verification Successful')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-8 col-lg-6">
        <div class="card border-success shadow">
            <div class="card-header bg-success text-white text-center">
                <h4 class="mb-0">Verification Successful!</h4>
            </div>
            <div class="card-body text-center">
                <p class="card-text fs-5 mb-3">
                    Your documents have been verified successfully.
                </p>
                <p class="card-text mb-4">
                    Please check your <strong>chat inbox</strong> for further instructions from our team.<br>
                    You will be redirected to your dashboard in <span id="countdown">30</span> seconds.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('app-chat') }}" class="btn btn-outline-primary">
                        <i class="ri-chat-3-line me-1"></i> Go to Chat
                    </a>
                    <a href="{{ $dashboardUrl }}" class="btn btn-success">
                        <i class="ri-dashboard-line me-1"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let seconds = 30;
    const countdown = document.getElementById('countdown');
    const dashboardUrl = "{{ $dashboardUrl }}";
    countdown.textContent = seconds;
    const timer = setInterval(function() {
        seconds--;
        if (seconds <= 0) {
            clearInterval(timer);
            window.location.href = dashboardUrl;
        } else {
            countdown.textContent = seconds;
        }
    }, 1000);
</script>
@endsection
