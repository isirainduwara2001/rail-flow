@extends('layouts.guest')

@section('title', 'Confirm Password - RailFlow')

@section('content')
<h5 class="text-center mb-3">{{ __('Confirm Password') }}</h5>

<p class="text-center text-muted mb-4">{{ __('Please confirm your password before continuing.') }}</p>

<form method="POST" action="{{ route('password.confirm') }}">
    @csrf

    <!-- Password Field -->
    <div class="form-group">
        <label for="password" class="form-label">{{ __('Password') }}</label>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
               name="password" required autocomplete="current-password"
               placeholder="Enter your password">
        @error('password')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-auth">
        {{ __('Confirm Password') }}
    </button>

    <!-- Forgot Password Link -->
    @if (Route::has('password.request'))
        <div class="auth-link">
            <a href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
        </div>
    @endif
</form>
@endsection
