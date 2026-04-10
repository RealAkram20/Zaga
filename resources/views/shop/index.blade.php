@extends('layouts.app')
@section('title', 'Shop')

@section('content')
<div class="container shop-container">

    <!-- ===== Sidebar Filters ===== -->
    <aside class="sidebar" role="complementary" aria-label="Product filters">
        <form method="GET" action="{{ route('shop.index') }}" id="filterForm">

            <div class="filter-section">
                <h3>Categories</h3>
                <div class="filter-group">
                    @foreach($categories as $cat)
                        <label>
                            <input type="checkbox" name="category[]" value="{{ $cat }}"
                                {{ in_array($cat, (array) request('category', [])) ? 'checked' : '' }}
                                onchange="document.getElementById('filterForm').submit()">
                            {{ $cat }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="filter-section">
                <h3>Price Range</h3>
                <input type="range" name="max_price" id="priceRange" class="price-slider"
                       min="0" max="{{ $maxPrice }}" step="10000"
                       value="{{ request('max_price', $maxPrice) }}"
                       oninput="document.getElementById('priceValue').textContent = Number(this.value).toLocaleString()"
                       onchange="document.getElementById('filterForm').submit()">
                <p>Max Price: UGX <span id="priceValue">{{ number_format(request('max_price', $maxPrice), 0, '.', ',') }}</span></p>
            </div>

            <div class="filter-section">
                <h3>Rating</h3>
                <div class="filter-group">
                    @foreach([5,4,3] as $star)
                        <label>
                            <input type="checkbox" name="rating[]" value="{{ $star }}"
                                {{ in_array($star, (array) request('rating', [])) ? 'checked' : '' }}
                                onchange="document.getElementById('filterForm').submit()">
                            @for($i = 1; $i <= 5; $i++)
                                <span style="color:{{ $i <= $star ? '#f59e0b' : '#d1d5db' }};">★</span>
                            @endfor
                            {{ $star === 5 ? '5 Stars' : $star.'+ Stars' }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="filter-section">
                <h3>Sort By</h3>
                <select name="sort" class="sort-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Default</option>
                    <option value="price_asc"  {{ request('sort') == 'price_asc'  ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="rating"     {{ request('sort') == 'rating'     ? 'selected' : '' }}>Rating: High to Low</option>
                    <option value="newest"     {{ request('sort') == 'newest'     ? 'selected' : '' }}>Newest</option>
                </select>
            </div>

            {{-- hidden search so it persists when sidebar filters change --}}
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

        </form>

        <a href="{{ route('shop.index') }}" class="btn-clear-filters">Clear Filters</a>
    </aside>

    <!-- ===== Main Content ===== -->
    <main class="main-content">
        <div class="shop-header">
            <h1>Shop All Products</h1>
            @if($products->total())
                <p>Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} products</p>
            @else
                <p>No products found</p>
            @endif
        </div>

        @if($products->isEmpty())
            <div class="no-products">
                <p>No products match your filters.</p>
                <a href="{{ route('shop.index') }}" class="btn-primary" style="display:inline-block;margin-top:16px;text-decoration:none;">Clear Filters</a>
            </div>
        @else
            <div class="products-grid">
                @foreach($products as $product)
                    <div class="product-card">
                        <div class="product-image">
                            <img src="{{ asset($product->image ?? 'images/logo.png') }}" alt="{{ $product->title }}">
                            @if($product->discount)
                                <span class="discount-badge">-{{ $product->discount }}%</span>
                            @endif
                            @if($product->credit_available)
                                <span class="credit-badge">Credit Available</span>
                            @endif
                        </div>
                        <div class="product-info">
                            <h3>{{ $product->title }}</h3>
                            <div class="rating">
                                <span class="stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span style="color:{{ $i <= $product->rating ? '#f59e0b' : '#d1d5db' }};">★</span>
                                    @endfor
                                </span>
                                <span class="rating-value">({{ $product->reviews }})</span>
                            </div>
                            <p class="description">{{ Str::limit($product->description, 80) }}</p>
                            <div class="price-section">
                                <span class="price">UGX {{ number_format($product->price) }}</span>
                                @if($product->original_price)
                                    <span class="original-price">UGX {{ number_format($product->original_price) }}</span>
                                @endif
                            </div>
                            <div class="actions">
                                <a href="{{ route('shop.show', $product) }}" class="btn-primary">View Details</a>
                                @auth
                                    <form method="POST" action="{{ route('cart.add') }}" style="display:contents;">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="payment_type" value="full">
                                        <button type="submit" class="btn-secondary">Add to Cart</button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="btn-secondary">Sign In to Buy</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:32px;text-align:center;">
                {{ $products->appends(request()->query())->links() }}
            </div>
        @endif
    </main>

</div>
@endsection
