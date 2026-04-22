@extends('auth.layout')

@section('title', 'Reset Password')
@section('subtitle', 'Set a new password.')

@section('content')
<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group mb-3">
        <label for="email" class="form-label">Email</label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email', $email) }}"
            class="form-control form-control-lg @error('email') is-invalid @enderror"
            placeholder="Email address"
            required
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-3">
        <label for="password" class="form-label">New Password</label>
        <input
            id="password"
            type="password"
            name="password"
            class="form-control form-control-lg @error('password') is-invalid @enderror"
            placeholder="New password (min. 8 characters)"
            autofocus
            required
        >
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-4">
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
        <input
            id="password_confirmation"
            type="password"
            name="password_confirmation"
            class="form-control form-control-lg"
            placeholder="Confirm new password"
            required
        >
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg w-100 font-weight-medium auth-form-btn">
            Reset Password
        </button>
    </div>

    <div class="text-center mt-3">
        <a href="{{ route('login') }}" class="auth-link text-muted">Back to sign in</a>
    </div>
</form>
@endsection
