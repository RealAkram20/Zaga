@extends('layouts.app')
@section('title', 'Home')

@section('content')

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h2>Zaga Tech Credit</h2>
        <h3>Financing Digital Empowerment!</h3>
        <p>Buy Now &amp; Pay Later</p>
        <a href="{{ route('shop.index') }}" class="hero-btn">Apply Now</a>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <h2>Own Now &amp; Pay Later</h2>
        <div class="categories-grid">
            <a href="{{ route('shop.index', ['category' => 'Laptops']) }}" class="category-card" style="text-decoration:none;color:inherit;">
                <div class="category-icon">💻</div>
                <h3>Laptops</h3>
            </a>
            <a href="{{ route('shop.index', ['category' => 'Desktops']) }}" class="category-card" style="text-decoration:none;color:inherit;">
                <div class="category-icon">🖥️</div>
                <h3>Desktops</h3>
            </a>
            <a href="{{ route('shop.index', ['category' => 'Tablets']) }}" class="category-card" style="text-decoration:none;color:inherit;">
                <div class="category-icon">📱</div>
                <h3>Smartphones &amp; Tablets</h3>
            </a>
            <a href="{{ route('shop.index', ['category' => 'Accessories']) }}" class="category-card" style="text-decoration:none;color:inherit;">
                <div class="category-icon">🎧</div>
                <h3>Accessories</h3>
            </a>
            <a href="{{ route('shop.index', ['category' => 'Peripherals']) }}" class="category-card" style="text-decoration:none;color:inherit;">
                <div class="category-icon">🖨️</div>
                <h3>Printers &amp; Peripherals</h3>
            </a>
            <a href="{{ route('shop.index', ['category' => 'Storage']) }}" class="category-card" style="text-decoration:none;color:inherit;">
                <div class="category-icon">🌐</div>
                <h3>Storage &amp; Networking</h3>
            </a>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="featured-section">
    <div class="container">
        <h2>Featured Products</h2>
        <div class="products-grid">
            @foreach($featured as $product)
                <div class="product-card">
                    <div class="product-image">
                        <img src="{{ asset($product->image ?? 'images/logo.png') }}" alt="{{ $product->title }}" style="max-height:180px;object-fit:contain;">
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
                            @for($i = 1; $i <= 5; $i++)
                                <span style="color:{{ $i <= $product->rating ? '#f59e0b' : '#d1d5db' }};">★</span>
                            @endfor
                            <span style="font-size:12px;color:#64748b;">({{ $product->reviews }})</span>
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
                            <form method="POST" action="{{ route('cart.add') }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="payment_type" value="full">
                                <button type="submit" class="btn-secondary">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div style="text-align:center;margin-top:32px;">
            <a href="{{ route('shop.index') }}" class="btn-primary">View All Products</a>
        </div>
    </div>
</section>

<!-- Offline Courses Section -->
<section class="courses-section">
    <div class="container">
        <h2>Offline Courses for Students</h2>

        <h3 style="margin-top:40px;margin-bottom:20px;color:#1f2937;font-size:clamp(18px,4vw,24px);">Digital Skilling Courses</h3>
        <div class="products-grid">
            @foreach([
                ['🖥️','Basic Computer Literacy','Master essential computer operations, file management, and safe browsing.',200000],
                ['📝','Microsoft Office Essentials','Excel, Word, and PowerPoint for business productivity and reporting.',200000],
                ['🎨','Graphic Design Fundamentals','Intro to design principles using Canva and Adobe Express.',200000],
                ['🌐','Web Development Basics','Build your first web pages with HTML, CSS, and a touch of JS.',200000],
                ['🔐','Cybersecurity Awareness','Protect your data: passwords, phishing, backups, and device safety.',200000],
                ['🛠️','PC Maintenance & Networking','Hardware basics, device cleanup, and small office networking.',200000],
            ] as $course)
                <div class="product-card">
                    <div class="product-image" aria-hidden="true" style="font-size:32px;display:flex;align-items:center;justify-content:center;height:80px;">
                        <span>{{ $course[0] }}</span>
                    </div>
                    <div class="product-info">
                        <h3>{{ $course[1] }}</h3>
                        <p class="description">{{ $course[2] }}</p>
                        <div class="price-section">
                            <span class="price">UGX {{ number_format($course[3]) }}</span>
                        </div>
                        <div class="actions">
                            <a href="{{ route('about') }}" class="btn-primary">Enroll Now</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <h3 style="margin-top:40px;margin-bottom:20px;color:#1f2937;font-size:clamp(18px,4vw,24px);">Entrepreneurship Courses</h3>
        <div class="products-grid">
            @foreach([
                ['💼','Business Planning Essentials','Learn to create effective business plans and strategies.',250000],
                ['📊','Financial Management','Master budgeting, accounting, and financial planning for your business.',250000],
                ['📱','Digital Marketing Fundamentals','Social media, SEO, and online advertising strategies for growth.',250000],
            ] as $course)
                <div class="product-card">
                    <div class="product-image" aria-hidden="true" style="font-size:32px;display:flex;align-items:center;justify-content:center;height:80px;">
                        <span>{{ $course[0] }}</span>
                    </div>
                    <div class="product-info">
                        <h3>{{ $course[1] }}</h3>
                        <p class="description">{{ $course[2] }}</p>
                        <div class="price-section">
                            <span class="price">UGX {{ number_format($course[3]) }}</span>
                        </div>
                        <div class="actions">
                            <a href="{{ route('about') }}" class="btn-primary">Enroll Now</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
