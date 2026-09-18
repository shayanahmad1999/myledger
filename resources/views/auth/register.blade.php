@extends('layouts.guest')
@section('title', 'Create account · MyLedger')
@section('content')
    <div class="auth-card">
        <div class="mb-4">
            <h2 class="h5 fw-bold mb-1">Create your workspace</h2>
            <p class="text-secondary small mb-0">Start tracking accounts, expenses, savings, loans and budgets.</p>
        </div>
        <form method="POST" action="{{ route('register.store') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label class="form-label">Full name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="form-control @error('name') is-invalid @enderror" autocomplete="name" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror" autocomplete="email" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                    autocomplete="new-password" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm password</label>
                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password"
                    required>
            </div>
            <div class="mb-3">
                <label class="form-label">Primary Base Currency</label>
                <select name="currency_id" class="form-select @error('currency_id') is-invalid @enderror" required>
                    @foreach ($currencies ?? [] as $curr)
                        <option value="{{ $curr->id }}"
                            {{ old('currency_id') == $curr->id || $curr->code === 'PKR' ? 'selected' : '' }}>
                            {{ $curr->code }} - {{ $curr->name }} ({{ $curr->symbol }})
                        </option>
                    @endforeach
                </select>
                <div class="form-text text-secondary small">This will be your primary reporting currency across all
                    accounts.</div>
                @error('currency_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Create account</button>
        </form>
        <p class="text-center text-secondary small mt-4 mb-0">Already have an account? <a href="{{ route('login') }}"
                class="fw-semibold">Sign in</a></p>
    </div>
@endsection
