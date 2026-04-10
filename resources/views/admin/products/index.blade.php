@extends('layouts.admin')
@section('title', 'Products')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h2 style="font-size:16px;font-weight:600;">All Products ({{ $products->total() }})</h2>
    <a href="{{ route('admin.products.create') }}" class="btn-sm btn-primary-sm" style="padding:8px 16px;">+ Add Product</a>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('admin.products.index') }}" class="search-filter-bar">
    <input type="text" name="search" placeholder="Search by title..." value="{{ request('search') }}">
    <select name="category">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-sm btn-primary-sm" style="padding:8px 14px;">Filter</button>
    <a href="{{ route('admin.products.index') }}" class="btn-sm" style="padding:8px 14px;background:#f1f5f9;color:#374151;border-radius:4px;text-decoration:none;">Clear</a>
</form>

<div class="admin-card" style="overflow-x:auto;">
    @if($products->isEmpty())
        <p style="text-align:center;color:#64748b;padding:40px 0;">No products found.</p>
    @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price (UGX)</th>
                    <th>Stock</th>
                    <th>Rating</th>
                    <th>Credit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>
                            <img src="{{ asset($product->image ?? 'images/logo.png') }}" alt="{{ $product->title }}"
                                 style="width:48px;height:48px;object-fit:contain;border:1px solid #e2e8f0;border-radius:4px;">
                        </td>
                        <td><strong>{{ Str::limit($product->title, 35) }}</strong></td>
                        <td><span class="badge badge-info">{{ $product->category }}</span></td>
                        <td>{{ number_format($product->price) }}</td>
                        <td>
                            <span class="{{ $product->stock > 0 ? 'badge badge-success' : 'badge badge-danger' }}">
                                {{ $product->stock }}
                            </span>
                        </td>
                        <td>{{ $product->rating }}</td>
                        <td>
                            <span class="badge {{ $product->credit_available ? 'badge-success' : 'badge-secondary' }}">
                                {{ $product->credit_available ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn-sm btn-warning-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="display:inline;"
                                  onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-sm btn-danger-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:16px;">{{ $products->links() }}</div>
    @endif
</div>

@endsection
