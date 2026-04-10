@extends('layouts.admin')
@section('title', 'Edit Product')

@section('content')
<div style="max-width:700px;">
    <a href="{{ route('admin.products.index') }}" style="color:#2563eb;font-size:14px;">&larr; Back to Products</a>

    <div class="admin-card" style="margin-top:16px;">
        <h2 style="font-size:16px;font-weight:600;margin-bottom:24px;">Edit: {{ $product->title }}</h2>

        <form method="POST" action="{{ route('admin.products.update', $product) }}">
            @csrf @method('PATCH')
            @include('admin.products._form', ['categories' => $categories, 'product' => $product])
            <div style="margin-top:24px;display:flex;gap:12px;">
                <button type="submit" class="btn-primary">Update Product</button>
                <a href="{{ route('admin.products.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
