@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="login-wrap">
    <div class="login-card">
        

        <div class="login-box">
            <h4 class="login-title">SPDRRMO Inventory</h4>
            <p class="login-sub">Sign in to your account</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}"
                        class="form-control @error('username') is-invalid @enderror" required autofocus>
                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-check mb-4">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label small" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Sign in</button>
            </form>
            <div class="login-hint">
                Forgot your username or password?<br>Please contact your system administrator.
            </div>
        </div>

        <div class="login-foot">Sorsogon Provincial Disaster Risk Reduction and Management Office</div>
    </div>
</div>
@endsection