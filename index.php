<?php
// ============================================================
// Zaga Technologies - Homepage
// ============================================================

require_once __DIR__ . '/includes/config.php';

$page_title = 'Home';
$current_page = 'home';

// Load categories from database
$conn = getDbConnection();

$catResult = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY name ASC");
$categories = [];
while ($row = $catResult->fetch_assoc()) { $categories[] = $row; }

// Load courses from database
$digitalResult = $conn->query("SELECT * FROM courses WHERE course_type = 'digital_skilling' ORDER BY id ASC");
$digitalCourses = [];
while ($row = $digitalResult->fetch_assoc()) { $digitalCourses[] = $row; }

$entreprenResult = $conn->query("SELECT * FROM courses WHERE course_type = 'entrepreneurship' ORDER BY id ASC");
$entreprenCourses = [];
while ($row = $entreprenResult->fetch_assoc()) { $entreprenCourses[] = $row; }

$conn->close();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h2>Zaga Tech Credit</h2>
        <h3>Financing Digital Empowerment!</h3>
        <p>Buy Now &amp; Pay Later</p>
        <a href="<?php echo SITE_URL; ?>/shop" class="hero-btn">Apply Now</a>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <h2>Own Now &amp; Pay Later</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <div class="category-card" onclick="filterByCategory('<?php echo safe_output($cat['name']); ?>')">
                <div class="category-icon"><?php echo $cat['icon']; ?></div>
                <h3><?php echo safe_output($cat['name']); ?></h3>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="featured-section">
    <div class="container">
        <div class="section-header">
            <h2>Featured Products</h2>
            <a href="<?php echo SITE_URL; ?>/shop" class="view-all-btn">
                View All
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
        <div id="featuredProducts" class="products-grid">
            <!-- Products will be loaded here by JavaScript -->
        </div>
    </div>
</section>

<!-- Offline Courses for Students Section -->
<section class="courses-section">
    <div class="container">
        <h2 style="margin-bottom:8px;">Offline Courses for Students</h2>

        <!-- Digital Skilling Courses Subsection -->
        <div class="section-header" style="margin-top:40px;">
            <h3 class="subsection-heading">Digital Skilling Courses</h3>
            <a href="<?php echo SITE_URL; ?>/courses" onclick="sessionStorage.setItem('selectedCourseType','digital_skilling')" class="view-all-btn">
                View All
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>

        <!-- Horizontal scroller -->
        <div class="courses-scroller-wrap">
            <button class="scroller-arrow scroller-arrow--left" id="scrollLeft" aria-label="Scroll left">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div class="courses-scroller" id="digitalScroller">
                <?php foreach ($digitalCourses as $course): ?>
                <div class="product-card course-scroll-card">
                    <div class="product-image" style="display:flex;align-items:center;justify-content:center;font-size:48px;background:#f8fafc;position:relative;">
                        <span><?php echo $course['icon']; ?></span>
                        <?php if (!empty($course['credit_available']) && floatval($course['default_apr'] ?? 0) > 0): ?>
                        <span class="credit-badge credit-badge--text">Available on <?php echo number_format($course['default_apr'], 0); ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <span class="category-badge">Digital Skills</span>
                        <h3><?php echo safe_output($course['title']); ?></h3>
                        <div class="rating">
                            <span class="stars"><?php echo str_repeat('&#11088;', (int)($course['rating'] ?? 4)); ?></span>
                            <span class="rating-value"><?php echo $course['rating'] ?? '4.0'; ?> (<?php echo $course['reviews'] ?? 0; ?>)</span>
                        </div>
                        <p class="description"><?php echo safe_output(mb_substr($course['description'], 0, 80)); ?>...</p>
                        <div class="price-section">
                            <span class="price">UGX <?php echo number_format($course['price'], 0, '.', ','); ?></span>
                        </div>
                        <div class="actions">
                            <?php
                            $courseSlug = $course['slug'] ?? strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($course['title'])), '-'));
                            $linkUrl = SITE_URL . '/course/' . $courseSlug;
                            ?>
                            <a href="<?php echo $linkUrl; ?>" class="btn btn-primary">Learn More</a>
                            <a href="<?php echo SITE_URL; ?>/courses" class="btn btn-secondary">Enroll Now</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="scroller-arrow scroller-arrow--right" id="scrollRight" aria-label="Scroll right">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>

        <!-- Entrepreneurship Courses Subsection -->
        <div class="section-header" style="margin-top:60px;">
            <h3 class="subsection-heading">Entrepreneurship Courses</h3>
            <a href="<?php echo SITE_URL; ?>/courses" onclick="sessionStorage.setItem('selectedCourseType','entrepreneurship')" class="view-all-btn">
                View All
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
        <div class="products-grid">
            <?php foreach ($entreprenCourses as $course): ?>
            <div class="product-card">
                <div class="product-image" style="display:flex;align-items:center;justify-content:center;font-size:48px;background:#f8fafc;position:relative;">
                    <span><?php echo $course['icon']; ?></span>
                    <?php if (!empty($course['credit_available']) && floatval($course['default_apr'] ?? 0) > 0): ?>
                    <span class="credit-badge credit-badge--text">Available on <?php echo number_format($course['default_apr'], 0); ?>%</span>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="category-badge">Entrepreneurship</span>
                    <h3><?php echo safe_output($course['title']); ?></h3>
                    <div class="rating">
                        <span class="stars"><?php echo str_repeat('&#11088;', (int)($course['rating'] ?? 4)); ?></span>
                        <span class="rating-value"><?php echo $course['rating'] ?? '4.0'; ?> (<?php echo $course['reviews'] ?? 0; ?>)</span>
                    </div>
                    <p class="description"><?php echo safe_output(mb_substr($course['description'], 0, 80)); ?>...</p>
                    <div class="price-section">
                        <span class="price">UGX <?php echo number_format($course['price'], 0, '.', ','); ?></span>
                    </div>
                    <div class="actions">
                        <a href="<?php echo SITE_URL; ?>/courses" class="btn btn-primary">View Details</a>
                        <a href="<?php echo SITE_URL; ?>/courses" class="btn btn-secondary">Enroll Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<style>
/* ── Section header: title left, View All right ── */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 12px;
}
.section-header h2 { margin: 0; }
.subsection-heading {
    margin: 0;
    color: #1f2937;
    font-size: 22px;
}

/* ── View All button ── */
.view-all-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    padding: 8px 18px;
    border: 2px solid #2563eb;
    border-radius: 8px;
    color: #2563eb;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.18s, color 0.18s;
    flex-shrink: 0;
}
.view-all-btn:hover { background: #2563eb; color: #fff; }

/* ── Scroller wrapper & arrows ── */
.courses-scroller-wrap {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
}
.scroller-arrow {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    background: #fff;
    color: #374151;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.18s, border-color 0.18s, color 0.18s, opacity 0.18s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    z-index: 2;
}
.scroller-arrow:hover { background: #2563eb; border-color: #2563eb; color: #fff; }
.scroller-arrow:disabled { opacity: 0.3; cursor: default; }

/* ── Horizontal scroller ── */
.courses-scroller {
    flex: 1;
    display: flex;
    gap: 14px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 10px;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.courses-scroller::-webkit-scrollbar { height: 5px; }
.courses-scroller::-webkit-scrollbar-track { background: transparent; }
.courses-scroller::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

/* ── Compact course card ── */
.course-scroll-card {
    flex: 0 0 200px;
    scroll-snap-align: start;
    min-width: 0;
}
/* Smaller image area */
.course-scroll-card .product-image {
    height: 110px;
    font-size: 36px !important;
}
/* Tighter info block */
.course-scroll-card .product-info {
    padding: 10px 12px 12px;
}
.course-scroll-card .product-info h3 {
    font-size: 13px;
    line-height: 1.3;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.course-scroll-card .category-badge { font-size: 10px; padding: 2px 6px; }
.course-scroll-card .rating { font-size: 11px; margin-bottom: 4px; }
.course-scroll-card .description { font-size: 11px; line-height: 1.4; margin-bottom: 6px;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.course-scroll-card .price { font-size: 13px; font-weight: 700; }
.course-scroll-card .price-section { margin-bottom: 8px; }
.course-scroll-card .actions { display: flex; flex-direction: column; gap: 6px; }
.course-scroll-card .actions .btn { padding: 6px 10px; font-size: 11px; text-align: center; }

/* ── Mobile ── */
@media (max-width: 600px) {
    .subsection-heading { font-size: 18px; }
    .view-all-btn { padding: 6px 12px; font-size: 13px; }
    .course-scroll-card { flex: 0 0 170px; }
    .scroller-arrow { width: 30px; height: 30px; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
    // --- Featured products loaded via JS from API ---
    function displayFeaturedProducts() {
        var featured = products.slice(0, 8);
        var container = document.getElementById('featuredProducts');
        if (!container) return;
        container.innerHTML = featured.map(function(product) {
            var badgeHtml = '';
            var apr = Number(product.defaultAPR || product.default_apr || 0);
            if (product.creditAvailable !== false && apr > 0) {
                badgeHtml = '<span class="credit-badge credit-badge--text">Available on ' + apr + '%</span>';
            }
            var productUrl = product.slug ? '<?php echo SITE_URL; ?>/product/' + product.slug : '<?php echo SITE_URL; ?>/product-detail?id=' + product.id;
            return '<div class="product-card">' +
                '<div class="product-image" style="position:relative;">' +
                    '<a href="' + productUrl + '"><img src="' + escapeHtml(product.image) + '" alt="' + escapeHtml(product.title) + '" loading="lazy"></a>' +
                    (product.discount ? '<span class="discount-badge">' + product.discount + '% OFF</span>' : '') +
                    badgeHtml +
                '</div>' +
                '<div class="product-info">' +
                    '<span class="category-badge">' + escapeHtml(product.category || '') + '</span>' +
                    '<h3><a href="' + productUrl + '" style="color:inherit;text-decoration:none;">' + escapeHtml(product.title) + '</a></h3>' +
                    '<div class="rating"><span class="stars">' + getStars(product.rating) + '</span>' +
                    '<span class="rating-value">' + product.rating + ' (' + product.reviews + ')</span></div>' +
                    '<p class="description">' + escapeHtml((product.description || '').substring(0, 80)) + '...</p>' +
                    '<div class="price-section"><span class="price">' + formatPrice(product.price) + '</span></div>' +
                    '<div class="actions">' +
                        '<a href="' + productUrl + '" class="btn btn-primary">View Details</a>' +
                        '<button onclick="addToCartWithChoice(' + product.id + ')" class="btn btn-secondary">Add to Cart</button>' +
                    '</div>' +
                '</div></div>';
        }).join('');
    }

    // Listen for products loaded from API
    window.addEventListener('products-loaded', displayFeaturedProducts);

    window.addEventListener('DOMContentLoaded', function () {
        if (window._productsLoaded) displayFeaturedProducts();
    });

    function filterByCategory(category) {
        sessionStorage.setItem('selectedCategory', category);
        window.location.href = '<?php echo SITE_URL; ?>/shop';
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

    // ── Scroller arrows ──
    (function () {
        var scroller  = document.getElementById('digitalScroller');
        var btnLeft   = document.getElementById('scrollLeft');
        var btnRight  = document.getElementById('scrollRight');
        if (!scroller || !btnLeft || !btnRight) return;

        var STEP = 214; // card width (200) + gap (14)

        function updateArrows() {
            btnLeft.disabled  = scroller.scrollLeft <= 0;
            btnRight.disabled = scroller.scrollLeft >= scroller.scrollWidth - scroller.clientWidth - 2;
        }

        btnLeft.addEventListener('click', function () {
            scroller.scrollBy({ left: -STEP * 2, behavior: 'smooth' });
        });
        btnRight.addEventListener('click', function () {
            scroller.scrollBy({ left: STEP * 2, behavior: 'smooth' });
        });

        scroller.addEventListener('scroll', updateArrows, { passive: true });
        updateArrows(); // set initial state
    })();
</script>
