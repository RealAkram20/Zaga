@extends('layouts.app')
@section('title', 'Sign In')

@section('content')
<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:40px 16px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:36px;width:100%;max-width:420px;">
        <div style="text-align:center;margin-bottom:28px;">
            <img src="{{ asset('images/logo.png') }}" alt="Zaga Technologies" style="height:50px;margin-bottom:12px;">
            <h2 style="font-size:22px;color:#1e293b;">Sign In</h2>
            <p style="color:#64748b;font-size:14px;">Welcome back to Zaga Tech Credit</p>
        </div>

        @if(session('status'))
            <div style="background:#dcfce7;color:#166534;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:14px;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       style="width:100%;padding:10px 12px;border:1px solid {{ $errors->has('email') ? '#dc3545' : '#e2e8f0' }};border-radius:6px;font-size:14px;">
                @error('email')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Password</label>
                <input type="password" name="password" required
                       style="width:100%;padding:10px 12px;border:1px solid {{ $errors->has('password') ? '#dc3545' : '#e2e8f0' }};border-radius:6px;font-size:14px;">
                @error('password')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="color:#2563eb;font-size:13px;">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="btn-primary" style="width:100%;padding:12px;font-size:15px;">
                Sign In
            </button>
        </form>

        <p style="text-align:center;margin-top:20px;font-size:14px;color:#64748b;">
            Don't have an account?
            <a href="{{ route('register') }}" style="color:#2563eb;font-weight:600;">Register</a>
        </p>
    </div>
</div>
@endsection
