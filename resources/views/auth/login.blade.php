@extends('layouts.guest')
@section('title', 'Sign in · MyLedger')
@section('content')
    <div class="auth-card">
        <div class="mb-4">
            <h2 class="h5 fw-bold mb-1">Welcome back</h2>
            <p class="text-secondary small mb-0">Sign in to continue to your financial dashboard.</p>
        </div>
        <form method="POST" action="{{ route('login.attempt') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror" autocomplete="email" autofocus required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                    autocomplete="current-password" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <label class="form-check small"><input class="form-check-input" type="checkbox" name="remember"
                        value="1"><span class="form-check-label">Remember me</span></label>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Sign in</button>
        </form>
        <p class="text-center text-secondary small mt-4 mb-0">New to MyLedger? <a href="{{ route('register') }}"
                class="fw-semibold">Create an account</a></p>
    </div>
@endsection
