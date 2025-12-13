@extends('layouts.guest')

@section('title', 'Login - RailFlow')

@section('content')
<form method="POST" action="{{ route('login') }}">
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

    <!-- Password Field -->
    <div class="form-group">
        <label for="password" class="form-label">{{ __('Password') }}</label>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
               name="password" required autocomplete="current-password" placeholder="Enter your password">
        @error('password')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <!-- Remember Me Checkbox -->
    <div class="form-group">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">
                {{ __('Remember Me') }}
            </label>
        </div>
    </div>

    <!-- Forgot Password Link -->
    @if (Route::has('password.request'))
        <div class="text-start mb-3">
            <a href="{{ route('password.request') }}" class="forgot-link text-sm">
                {{ __('Forgot Your Password?') }}
            </a>
        </div>
    @endif

    <!-- Submit Button -->
    <button type="submit" class="btn btn-auth">
        {{ __('Login') }}
    </button>

    <!-- Register Link -->
    <div class="auth-link">
        Don't have an account? <a href="{{ route('register') }}">{{ __('Register here') }}</a>
    </div>
</form>
@endsection

