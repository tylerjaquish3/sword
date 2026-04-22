@extends('auth.layout')

@section('title', 'Forgot Password')
@section('subtitle', 'Reset your password.')

@section('content')
<p class="text-muted mb-4">
    Enter your email address and we'll send you a link to reset your password.
</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="form-group mb-4">
        <label for="email" class="form-label">Email</label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            class="form-control form-control-lg @error('email') is-invalid @enderror"
            placeholder="Email address"
            autofocus
            required
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg w-100 font-weight-medium auth-form-btn">
            Send Reset Link
        </button>
    </div>

    <div class="text-center mt-3">
        <a href="{{ route('login') }}" class="auth-link text-muted">Back to sign in</a>
    </div>
</form>
@endsection
