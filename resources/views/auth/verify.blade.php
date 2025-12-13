@extends('layouts.guest')

@section('title', 'Verify Email - RailFlow')

@section('content')
<h5 class="text-center mb-3">{{ __('Verify Your Email Address') }}</h5>

@if (session('resent'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <strong>{{ __('Success!') }}</strong> {{ __('A fresh verification link has been sent to your email address.') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="text-center mb-4">
    <p class="text-muted">{{ __('Before proceeding, please check your email for a verification link.') }}</p>
    <p class="text-muted">
        {{ __('If you did not receive the email') }},
        <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" class="btn btn-link p-0 m-0 align-baseline text-decoration-none">
                {{ __('click here to request another') }}
            </button>.
        </form>
    </p>
</div>

<!-- Back to Dashboard -->
<div class="auth-link">
    <a href="{{ route('dashboard') }}">{{ __('Back to Dashboard') }}</a>
</div>
@endsection
