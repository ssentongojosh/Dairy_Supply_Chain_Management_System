@extends('layouts/blankLayout')

@section('title', 'Register Basic - Pages')

@section('page-style')
@vite([
  'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection


@section('content')
<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6 mx-4">

      <!-- Register Card -->
      <div class="card p-7">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-5">
          <a href="{{url('/')}}" class="app-brand-link gap-3">
            <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20])</span>
            <span class="app-brand-text demo text-heading fw-semibold">{{ config('variables.templateName') }}</span>
          </a>
        </div>
        <!-- /Logo -->
        <div class="card-body mt-1">
          <h4 class="mb-1">Adventure starts here🥛</h4>
          <p class="mb-5">Make your Dairy management easy and fun!</p>

          <!-- Make sure your form has this structure -->
          <form id="formAuthentication" class="mb-5" action="{{ route('register.submit') }}" method="POST">
            @csrf

            <!-- Display general error messages -->
            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="form-floating form-floating-outline mb-5">
              <input type="text" class="form-control @error('name') is-invalid @enderror"
                     id="name" name="name" value="{{ old('name') }}" placeholder="Enter your name" autofocus>
              <label for="name">Name</label>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-floating form-floating-outline mb-5">
              <input type="email" class="form-control @error('email') is-invalid @enderror"
                     id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email">
              <label for="email">Email</label>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-5 form-password-toggle">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input type="password" id="password"
                         class="form-control @error('password') is-invalid @enderror"
                         name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                  <label for="password">Password</label>
                </div>
                <span class="input-group-text cursor-pointer"><i class="ri-eye-off-line ri-20px"></i></span>
              </div>
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-5 form-password-toggle">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input type="password" id="password_confirmation" class="form-control"
                         name="password_confirmation" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                  <label for="password_confirmation">Confirm Password</label>
                </div>
                <span class="input-group-text cursor-pointer"><i class="ri-eye-off-line ri-20px"></i></span>
              </div>
            </div>

            <div class="mb-5">
              <label class="form-label">Register as</label>
              <select class="form-select" name="role">
                <option value="retailer">Retailer</option>
                <option value="wholesaler">Wholesaler</option>
                <option value="farmer">Dairy Farmer</option>
              </select>
            </div>

            <div class="mb-5 py-2">
              <div class="form-check mb-0">
                <input class="form-check-input @error('terms') is-invalid @enderror"
                       type="checkbox" id="terms-conditions" name="terms">
                <label class="form-check-label" for="terms-conditions">
                  I agree to the <a href="javascript:void(0);">privacy policy & terms</a>
                </label>
                @error('terms')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <button class="btn btn-primary d-grid w-100 mb-5" type="submit">
              Sign up
            </button>
          </form>

          <p class="text-center mb-5">
            <span>Already have an account?</span>
            <a href="{{url('auth/login-basic')}}">
              <span>Sign in instead</span>
            </a>
          </p>
        </div>
      </div>
      <!-- Register Card -->
      <img src="{{asset('assets/img/illustrations/tree-3.png')}}" alt="auth-tree" class="authentication-image-object-left d-none d-lg-block">
      <img src="{{asset('assets/img/illustrations/auth-basic-mask-light.png')}}" class="authentication-image d-none d-lg-block" height="172" alt="triangle-bg">
      <img src="{{asset('assets/img/illustrations/tree.png')}}" alt="auth-tree" class="authentication-image-object-right d-none d-lg-block">
    </div>
  </div>
</div>
@endsection
