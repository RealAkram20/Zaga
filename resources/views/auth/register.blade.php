@extends('layouts.app')
@section('title', 'Register')

@section('content')
<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:40px 16px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:36px;width:100%;max-width:440px;">
        <div style="text-align:center;margin-bottom:28px;">
            <img src="{{ asset('images/logo.png') }}" alt="Zaga Technologies" style="height:50px;margin-bottom:12px;">
            <h2 style="font-size:22px;color:#1e293b;">Create Account</h2>
            <p style="color:#64748b;font-size:14px;">Join Zaga Tech Credit and buy now, pay later</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       style="width:100%;padding:10px 12px;border:1px solid {{ $errors->has('name') ? '#dc3545' : '#e2e8f0' }};border-radius:6px;font-size:14px;">
                @error('name')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Email Address *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       style="width:100%;padding:10px 12px;border:1px solid {{ $errors->has('email') ? '#dc3545' : '#e2e8f0' }};border-radius:6px;font-size:14px;">
                @error('email')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px;" placeholder="+256 700 000000">
            </div>

            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Password *</label>
                <input type="password" name="password" required
                       style="width:100%;padding:10px 12px;border:1px solid {{ $errors->has('password') ? '#dc3545' : '#e2e8f0' }};border-radius:6px;font-size:14px;">
                @error('password')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>

            <div style="margin-bottom:24px;">
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Confirm Password *</label>
                <input type="password" name="password_confirmation" required
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px;">
            </div>

            <button type="submit" class="btn-primary" style="width:100%;padding:12px;font-size:15px;">
                Create Account
            </button>
        </form>

        <p style="text-align:center;margin-top:20px;font-size:14px;color:#64748b;">
            Already have an account?
            <a href="{{ route('login') }}" style="color:#2563eb;font-weight:600;">Sign In</a>
        </p>
    </div>
</div>
@endsection
