<?php
// ============================================================
// Zaga Technologies - Shop Page
// ============================================================

require_once __DIR__ . '/../includes/config.php';

$page_title = 'Shop';
$current_page = 'shop';

// Fetch categories and price range from database
$conn = getDbConnection();
$catResult = $conn->query("SELECT c.id, c.name, c.icon, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON p.category_id = c.id WHERE c.status = 1 GROUP BY c.id ORDER BY c.name ASC");
$categories = [];
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}
$priceRange = $conn->query("SELECT COALESCE(MIN(price), 0) as min_price, COALESCE(MAX(price), 10000000) as max_price FROM products")->fetch_assoc();
$minPrice = intval($priceRange['min_price']);
$maxPrice = intval($priceRange['max_price']);
// Round max up to nearest 100k for a cleaner slider
$maxPrice = intval(ceil($maxPrice / 100000) * 100000);
$step = max(10000, intval(round(($maxPrice - $minPrice) / 100 / 10000) * 10000));
$conn->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container shop-container">
    <!-- Mobile Filter Toggle -->
    <button class="filter-toggle-btn" id="filterToggleBtn" onclick="document.getElementById('shopSidebar').classList.toggle('sidebar-hidden')">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filters
    </button>

    <!-- Sidebar Filters -->
    <aside class="sidebar sidebar-hidden" id="shopSidebar">
        <div class="filter-section">
            <h3>Categories</h3>
            <div class="filter-group">
                <?php foreach ($categories as $cat): ?>
                <label><input type="checkbox" name="category" value="<?php echo safe_output($cat['name']); ?>"> <?php echo safe_output($cat['name']); ?> <small style="color:var(--color-text-muted);">(<?php echo $cat['product_count']; ?>)</small></label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="filter-section">
            <h3>Price Range</h3>
            <input type="range" id="priceRange" min="0" max="<?php echo $maxPrice; ?>" value="<?php echo $maxPrice; ?>" step="<?php echo $step; ?>" class="price-slider">
            <p>Max Price: UGX <span id="priceValue"><?php echo number_format($maxPrice); ?></span></p>
        </div>

        <div class="filter-section">
            <h3>Rating</h3>
            <div class="filter-group">
                <label><input type="checkbox" name="rating" value="5"> &#11088;&#11088;&#11088;&#11088;&#11088; 5 Stars</label>
                <label><input type="checkbox" name="rating" value="4"> &#11088;&#11088;&#11088;&#11088; 4+ Stars</label>
                <label><input type="checkbox" name="rating" value="3"> &#11088;&#11088;&#11088; 3+ Stars</label>
            </div>
        </div>

        <!-- Sort moved to toolbar above products -->

        <button id="clearFilters" class="btn-clear-filters">Clear Filters</button>
    </aside>

    <!-- Main Content -->
    <div class="shop-main">
        <div class="shop-header">
            <h1>Shop All Products</h1>
        </div>

        <!-- Sort toolbar -->
        <div class="sort-toolbar">
            <span id="productCount" class="product-count-label">Showing products</span>
            <div class="sort-toolbar-right">
                <label for="sortBy" class="sort-label">Sort by:</label>
                <select id="sortBy" class="sort-select-toolbar">
                    <option value="newest" selected>Newest First</option>
                    <option value="az">Name: A → Z</option>
                    <option value="za">Name: Z → A</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                    <option value="rating">Top Rated</option>
                </select>
            </div>
        </div>

        <div id="productsContainer" class="products-grid">
            <!-- Products will be loaded here -->
        </div>

        <div id="noProducts" class="no-products" style="display: none;">
            <p>No products found. Try adjusting your filters.</p>
        </div>
    </div>
</div>

<style>
/* ── Sort toolbar ── */
.sort-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.product-count-label {
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
}
.sort-toolbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
}
.sort-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
}
.sort-select-toolbar {
    appearance: none;
    -webkit-appearance: none;
    padding: 7px 36px 7px 12px;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center;
    font-size: 14px;
    font-weight: 500;
    color: #111827;
    cursor: pointer;
    transition: border-color 0.18s, box-shadow 0.18s;
    min-width: 170px;
}
.sort-select-toolbar:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
}
.sort-select-toolbar:hover { border-color: #2563eb; }

@media (max-width: 600px) {
    .sort-toolbar { padding: 8px 12px; }
    .sort-select-toolbar { min-width: 140px; font-size: 13px; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
    var filteredProducts = [];

    // FIX: Wait for products to load from API before initializing display
    window.addEventListener('products-loaded', function() {
        filteredProducts = products.slice();
        // Check if category filter was set from home page
        var selectedCategory = sessionStorage.getItem('selectedCategory');
        if (selectedCategory) {
            var cb = document.querySelector('input[name="category"][value="' + selectedCategory + '"]');
            if (cb) cb.checked = true;
            sessionStorage.removeItem('selectedCategory');
        }
        // Check for search query from navbar
        var urlParams = new URLSearchParams(window.location.search);
        var searchQuery = urlParams.get('q');
        if (searchQuery) {
            var term = searchQuery.toLowerCase();
            filteredProducts = products.filter(function(p) {
                return p.title.toLowerCase().indexOf(term) !== -1 ||
                    (p.description && p.description.toLowerCase().indexOf(term) !== -1) ||
                    (p.category && p.category.toLowerCase().indexOf(term) !== -1);
            });
            displayProducts(filteredProducts);
            return;
        }
        applyFilters();
    });

    window.addEventListener('DOMContentLoaded', function () {
        setupFilters();
        updateCartCount();

        // If products already loaded before DOMContentLoaded
        if (window._productsLoaded) {
            filteredProducts = products.slice();
            var selectedCategory = sessionStorage.getItem('selectedCategory');
            if (selectedCategory) {
                var cb = document.querySelector('input[name="category"][value="' + selectedCategory + '"]');
                if (cb) cb.checked = true;
                sessionStorage.removeItem('selectedCategory');
            }
            applyFilters();
        }
    });

    function setupFilters() {
        // Category filters
        document.querySelectorAll('input[name="category"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', applyFilters);
        });

        // Price filter
        document.getElementById('priceRange').addEventListener('input', function () {
            document.getElementById('priceValue').textContent = Number(this.value).toLocaleString();
            applyFilters();
        });

        // Rating filters
        document.querySelectorAll('input[name="rating"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', applyFilters);
        });

        // Sort toolbar
        document.getElementById('sortBy').addEventListener('change', applyFilters);

        // Clear filters
        document.getElementById('clearFilters').addEventListener('click', clearAllFilters);

        // Search
        var searchBtn = document.getElementById('searchBtn');
        var searchInput = document.getElementById('searchInput');
        if (searchBtn) searchBtn.addEventListener('click', searchProducts);
        if (searchInput) searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') searchProducts();
        });
    }

    function applyFilters() {
        var selectedCategories = Array.from(document.querySelectorAll('input[name="category"]:checked')).map(function(cb) { return cb.value; });
        var maxPrice = parseInt(document.getElementById('priceRange').value);
        var selectedRatings = Array.from(document.querySelectorAll('input[name="rating"]:checked')).map(function(cb) { return parseInt(cb.value); });
        var sortBy = document.getElementById('sortBy').value;

        filteredProducts = products.filter(function(product) {
            var categoryMatch = selectedCategories.length === 0 || selectedCategories.indexOf(product.category) !== -1;
            var priceMatch = product.price <= maxPrice;
            var ratingMatch = selectedRatings.length === 0 || selectedRatings.some(function(r) { return product.rating >= r; });
            return categoryMatch && priceMatch && ratingMatch;
        });

        // Apply sorting
        if (sortBy === 'newest') {
            filteredProducts.sort(function(a, b) { return b.id - a.id; });
        } else if (sortBy === 'az') {
            filteredProducts.sort(function(a, b) { return a.title.localeCompare(b.title); });
        } else if (sortBy === 'za') {
            filteredProducts.sort(function(a, b) { return b.title.localeCompare(a.title); });
        } else if (sortBy === 'price-low') {
            filteredProducts.sort(function(a, b) { return a.price - b.price; });
        } else if (sortBy === 'price-high') {
            filteredProducts.sort(function(a, b) { return b.price - a.price; });
        } else if (sortBy === 'rating') {
            filteredProducts.sort(function(a, b) { return b.rating - a.rating; });
        }

        displayProducts(filteredProducts);
    }

    function clearAllFilters() {
        document.querySelectorAll('input[type="checkbox"]').forEach(function(cb) { cb.checked = false; });
        var slider = document.getElementById('priceRange');
        slider.value = slider.max;
        document.getElementById('priceValue').textContent = Number(slider.max).toLocaleString();
        document.getElementById('sortBy').value = 'newest';
        var searchInput = document.getElementById('searchInput');
        if (searchInput) searchInput.value = '';
        filteredProducts = products.slice();
        displayProducts(filteredProducts);
    }

    function searchProducts() {
        var searchInput = document.getElementById('searchInput');
        var searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        filteredProducts = products.filter(function(p) {
            return p.title.toLowerCase().indexOf(searchTerm) !== -1 ||
                p.description.toLowerCase().indexOf(searchTerm) !== -1;
        });
        displayProducts(filteredProducts);
    }

    function displayProducts(productsToDisplay) {
        var container = document.getElementById('productsContainer');
        var noProducts = document.getElementById('noProducts');
        var productCount = document.getElementById('productCount');

        if (productsToDisplay.length === 0) {
            container.innerHTML = '';
            noProducts.style.display = 'block';
            productCount.textContent = 'No products found';
            return;
        }

        noProducts.style.display = 'none';
        productCount.textContent = 'Showing ' + productsToDisplay.length + ' products';

        container.innerHTML = productsToDisplay.map(function(product) {
            // Badge eligibility - only show if credit available AND APR > 0
            var badgeHtml = '';
            var apr = Number(product.defaultAPR || product.default_apr || 0);
            var creditOn = (product.creditAvailable !== false) && apr > 0;
            if (creditOn) {
                var aprText = 'Available on ' + apr + '%';
                badgeHtml =
                    '<span class="credit-badge credit-badge--text" tabindex="0" role="button" aria-label="' + aprText + '" onclick="openCreditForProduct(' + product.id + ', ' + apr + ')" aria-describedby="credit-tooltip-' + product.id + '">' + aprText + '</span>' +
                    '<span id="credit-tooltip-' + product.id + '" class="credit-tooltip" role="tooltip">' + aprText + '</span>';
            }

            return '<div class="product-card">' +
                '<div class="product-image">' +
                    '<img src="' + escapeHtml(product.image) + '" alt="' + escapeHtml(product.title) + '" loading="lazy">' +
                    (product.discount ? '<span class="discount-badge">' + product.discount + '% OFF</span>' : '') +
                    badgeHtml +
                '</div>' +
                '<div class="product-info">' +
                    '<span class="category-badge">' + escapeHtml(product.category) + '</span>' +
                    '<h3>' + escapeHtml(product.title) + '</h3>' +
                    '<div class="rating">' +
                        '<span class="stars">' + getStars(product.rating) + '</span>' +
                        '<span class="rating-value">' + product.rating + ' (' + product.reviews + ')</span>' +
                    '</div>' +
                    '<p class="description">' + escapeHtml(product.description.substring(0, 80)) + '...</p>' +
                    '<div class="price-section">' +
                        '<span class="price">' + formatPrice(product.price) + '</span>' +
                    '</div>' +
                    '<div class="actions">' +
                        '<button onclick="viewProductDetail(' + product.id + ')" class="btn btn-primary">View Details</button>' +
                        '<button onclick="addToCartWithChoice(' + product.id + ')" class="btn btn-secondary">Add to Cart</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function viewProductDetail(id) {
        var product = products.find(function(p) { return p.id === id; });
        if (product && product.slug) {
            window.location.href = '<?php echo SITE_URL; ?>/product/' + product.slug;
        } else {
            window.location.href = '<?php echo SITE_URL; ?>/product-detail?id=' + id;
        }
    }

    function openCreditForProduct(id, apr) {
        sessionStorage.setItem('productId', id);
        sessionStorage.setItem('openCredit', '1');
        if (apr !== null && typeof apr !== 'undefined') {
            sessionStorage.setItem('openCreditAPR', String(apr));
        } else {
            sessionStorage.removeItem('openCreditAPR');
        }
        var product = products.find(function(p) { return p.id === id; });
        if (product && product.slug) {
            window.location.href = '<?php echo SITE_URL; ?>/product/' + product.slug;
        } else {
            window.location.href = '<?php echo SITE_URL; ?>/product-detail?id=' + id;
        }
    }

    function addToCartWithChoice(id) {
        showPaymentChoice({
            productId: id,
            quantity: 1,
            onConfirm: function(paymentPlan) {
                addToCart(id, 1, paymentPlan);
                showToast('Product added to cart!');
            }
        });
    }
</script>
