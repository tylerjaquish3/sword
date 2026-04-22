@extends('auth.layout')

@section('title', 'Login')
@section('subtitle', 'Sign in to continue.')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="form-group mb-3">
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

    <div class="form-group mb-3">
        <label for="password" class="form-label">Password</label>
        <input
            id="password"
            type="password"
            name="password"
            class="form-control form-control-lg @error('password') is-invalid @enderror"
            placeholder="Password"
            required
        >
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="remember" id="remember">
        <label class="form-check-label" for="remember">Keep me signed in</label>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg w-100 font-weight-medium auth-form-btn">
            Sign In
        </button>
    </div>

    <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('password.request') }}" class="auth-link text-muted">Forgot password?</a>
        <a href="{{ route('register') }}" class="auth-link text-muted">Create account</a>
    </div>
</form>
@endsection
