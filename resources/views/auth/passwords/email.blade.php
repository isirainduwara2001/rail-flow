@extends('layouts.guest')

@section('title', 'Reset Password - RailFlow')

@section('content')
<h5 class="text-center mb-3">{{ __('Reset Password') }}</h5>

@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <!-- Email Field -->
    <div class="form-group">
        <label for="email" class="form-label">{{ __('Email Address') }}</label>
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
               name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
               placeholder="Enter your email">
        @error('email')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-auth">
        {{ __('Send Password Reset Link') }}
    </button>

    <!-- Back to Login -->
    <div class="auth-link">
        <a href="{{ route('login') }}">{{ __('Back to Login') }}</a>
    </div>
</form>
@endsection@endsection
