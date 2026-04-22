@extends('auth.layout')

@section('title', 'Register')
@section('subtitle', 'Create your account.')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group mb-3">
        <label for="name" class="form-label">Name</label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name') }}"
            class="form-control form-control-lg @error('name') is-invalid @enderror"
            placeholder="Your name"
            autofocus
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-3">
        <label for="email" class="form-label">Email</label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            class="form-control form-control-lg @error('email') is-invalid @enderror"
            placeholder="Email address"
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
            placeholder="Password (min. 8 characters)"
            required
        >
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-4">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input
            id="password_confirmation"
            type="password"
            name="password_confirmation"
            class="form-control form-control-lg"
            placeholder="Confirm password"
            required
        >
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg w-100 font-weight-medium auth-form-btn">
            Create Account
        </button>
    </div>

    <div class="text-center mt-3">
        <a href="{{ route('login') }}" class="auth-link text-muted">Already have an account? Sign in</a>
    </div>
</form>
@endsection
