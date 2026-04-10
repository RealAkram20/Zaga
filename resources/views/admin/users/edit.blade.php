@extends('layouts.admin')
@section('title', 'Edit Customer')

@section('content')
<div style="max-width:500px;">
    <a href="{{ route('admin.users.show', $user) }}" style="color:#2563eb;font-size:14px;">&larr; Back to Customer</a>

    <div class="admin-card" style="margin-top:16px;">
        <h2 style="font-size:16px;font-weight:600;margin-bottom:24px;">Edit: {{ $user->name }}</h2>

        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PATCH')

            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
                @error('name')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Email *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
                @error('email')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Address</label>
                <textarea name="address" rows="2"
                          style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">{{ old('address', $user->address) }}</textarea>
            </div>

            <div style="margin-bottom:24px;">
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Status *</label>
                <select name="status" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
                    <option value="active"    {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn-primary">Update Customer</button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
