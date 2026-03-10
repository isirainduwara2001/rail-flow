@extends('layouts.guest')

@section('title', 'Reset Password - RailFlow')

@section('content')
<h5 class="text-center mb-3">{{ __('Reset Password') }}</h5>

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <!-- Email Field -->
    <div class="form-group">
        <label for="email" class="form-label">{{ __('Email Address') }}</label>
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
               name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus
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
               name="password" required autocomplete="new-password"
               placeholder="Enter your new password">
        @error('password')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <!-- Confirm Password Field -->
    <div class="form-group">
        <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
        <input id="password-confirm" type="password" class="form-control"
               name="password_confirmation" required autocomplete="new-password"
               placeholder="Confirm your new password">
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-auth">
        {{ __('Reset Password') }}
    </button>

    <!-- Back to Login -->
    <div class="auth-link">
        <a href="{{ route('login') }}">{{ __('Back to Login') }}</a>
    </div>
</form>
@endsection


                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Reset Password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
