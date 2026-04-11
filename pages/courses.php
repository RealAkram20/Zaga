<?php
// ============================================================
// Zaga Technologies - Courses Page
// ============================================================

require_once __DIR__ . '/../includes/config.php';

$page_title = 'Courses';
$current_page = 'courses';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container shop-container">
    <!-- Mobile Filter Toggle -->
    <button class="filter-toggle-btn" id="filterToggleBtn" onclick="document.getElementById('courseSidebar').classList.toggle('sidebar-hidden')">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filters
    </button>

    <!-- Sidebar Filters -->
    <aside class="sidebar sidebar-hidden" id="courseSidebar">
        <div class="filter-section">
            <h3>Course Type</h3>
            <div class="filter-group">
                <label><input type="checkbox" name="courseType" value="digital_skilling"> Digital Skilling</label>
                <label><input type="checkbox" name="courseType" value="entrepreneurship"> Entrepreneurship</label>
            </div>
        </div>

        <div class="filter-section">
            <h3>Level</h3>
            <div class="filter-group">
                <label><input type="checkbox" name="level" value="Beginner"> Beginner</label>
                <label><input type="checkbox" name="level" value="Beginner to Intermediate"> Beginner to Intermediate</label>
                <label><input type="checkbox" name="level" value="Intermediate"> Intermediate</label>
            </div>
        </div>

        <div class="filter-section">
            <h3>Price Range</h3>
            <input type="range" id="priceRange" min="0" max="500000" value="500000" step="10000" class="price-slider">
            <p>Max Price: UGX <span id="priceValue">500,000</span></p>
        </div>

        <div class="filter-section">
            <h3>Rating</h3>
            <div class="filter-group">
                <label><input type="checkbox" name="rating" value="5"> &#11088;&#11088;&#11088;&#11088;&#11088; 5 Stars</label>
                <label><input type="checkbox" name="rating" value="4"> &#11088;&#11088;&#11088;&#11088; 4+ Stars</label>
                <label><input type="checkbox" name="rating" value="3"> &#11088;&#11088;&#11088; 3+ Stars</label>
            </div>
        </div>

        <div class="filter-section">
            <h3>Sort By</h3>
            <select id="sortBy" class="sort-select">
                <option value="default">Default</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
                <option value="rating">Rating: High to Low</option>
            </select>
        </div>

        <button id="clearFilters" class="btn-clear-filters">Clear Filters</button>
    </aside>

    <!-- Main Content -->
    <div class="shop-main">
        <div class="shop-header">
            <h1>Offline Courses</h1>
            <p id="courseCount">Loading courses...</p>
        </div>

        <div id="coursesContainer" class="products-grid">
            <!-- Courses will be loaded here -->
        </div>

        <div id="noCourses" class="no-products" style="display: none;">
            <p>No courses found. Try adjusting your filters.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
    var allCourses = [];
    var filteredCourses = [];

    var siteUrl = '<?php echo SITE_URL; ?>';

    window.addEventListener('DOMContentLoaded', function () {
        updateCartCount();
        loadCourses();
        setupFilters();

        // Check if course type filter was set from another page
        var selectedType = sessionStorage.getItem('selectedCourseType');
        if (selectedType) {
            var cb = document.querySelector('input[name="courseType"][value="' + selectedType + '"]');
            if (cb) cb.checked = true;
            sessionStorage.removeItem('selectedCourseType');
        }
    });

    function loadCourses() {
        fetch('<?php echo SITE_URL; ?>/api/courses.php?action=list')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success && data.data) {
                    allCourses = data.data;
                    filteredCourses = allCourses.slice();
                    applyFilters();
                } else {
                    document.getElementById('courseCount').textContent = 'Failed to load courses';
                }
            })
            .catch(function () {
                document.getElementById('courseCount').textContent = 'Failed to load courses';
            });
    }

    function setupFilters() {
        document.querySelectorAll('input[name="courseType"]').forEach(function (cb) {
            cb.addEventListener('change', applyFilters);
        });
        document.querySelectorAll('input[name="level"]').forEach(function (cb) {
            cb.addEventListener('change', applyFilters);
        });

        document.getElementById('priceRange').addEventListener('input', function () {
            document.getElementById('priceValue').textContent = Number(this.value).toLocaleString();
            applyFilters();
        });

        document.querySelectorAll('input[name="rating"]').forEach(function (cb) {
            cb.addEventListener('change', applyFilters);
        });

        document.getElementById('sortBy').addEventListener('change', applyFilters);
        document.getElementById('clearFilters').addEventListener('click', clearAllFilters);

        var searchBtn = document.getElementById('searchBtn');
        var searchInput = document.getElementById('searchInput');
        if (searchBtn) searchBtn.addEventListener('click', searchCourses);
        if (searchInput) searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') searchCourses();
        });
    }

    function applyFilters() {
        var selectedTypes = Array.from(document.querySelectorAll('input[name="courseType"]:checked')).map(function (cb) { return cb.value; });
        var selectedLevels = Array.from(document.querySelectorAll('input[name="level"]:checked')).map(function (cb) { return cb.value; });
        var maxPrice = parseInt(document.getElementById('priceRange').value);
        var selectedRatings = Array.from(document.querySelectorAll('input[name="rating"]:checked')).map(function (cb) { return parseInt(cb.value); });
        var sortBy = document.getElementById('sortBy').value;

        filteredCourses = allCourses.filter(function (course) {
            var typeMatch = selectedTypes.length === 0 || selectedTypes.indexOf(course.course_type) !== -1;
            var levelMatch = selectedLevels.length === 0 || selectedLevels.indexOf(course.level) !== -1;
            var priceMatch = course.price <= maxPrice;
            var ratingMatch = selectedRatings.length === 0 || selectedRatings.some(function (r) { return course.rating >= r; });
            return typeMatch && levelMatch && priceMatch && ratingMatch;
        });

        // Also apply search if there's text in the search bar
        var searchInput = document.getElementById('searchInput');
        var searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        if (searchTerm) {
            filteredCourses = filteredCourses.filter(function (c) {
                return c.title.toLowerCase().indexOf(searchTerm) !== -1 ||
                    (c.description || '').toLowerCase().indexOf(searchTerm) !== -1 ||
                    (c.instructor || '').toLowerCase().indexOf(searchTerm) !== -1;
            });
        }

        if (sortBy === 'price-low') {
            filteredCourses.sort(function (a, b) { return a.price - b.price; });
        } else if (sortBy === 'price-high') {
            filteredCourses.sort(function (a, b) { return b.price - a.price; });
        } else if (sortBy === 'rating') {
            filteredCourses.sort(function (a, b) { return b.rating - a.rating; });
        }

        displayCourses(filteredCourses);
    }

    function clearAllFilters() {
        document.querySelectorAll('input[type="checkbox"]').forEach(function (cb) { cb.checked = false; });
        document.getElementById('priceRange').value = 500000;
        document.getElementById('priceValue').textContent = '500,000';
        document.getElementById('sortBy').value = 'default';
        var searchInput = document.getElementById('searchInput');
        if (searchInput) searchInput.value = '';
        filteredCourses = allCourses.slice();
        displayCourses(filteredCourses);
    }

    function searchCourses() {
        applyFilters();
    }

    function displayCourses(coursesToDisplay) {
        var container = document.getElementById('coursesContainer');
        var noCourses = document.getElementById('noCourses');
        var courseCount = document.getElementById('courseCount');

        if (coursesToDisplay.length === 0) {
            container.innerHTML = '';
            noCourses.style.display = 'block';
            courseCount.textContent = 'No courses found';
            return;
        }

        noCourses.style.display = 'none';
        courseCount.textContent = 'Showing ' + coursesToDisplay.length + ' courses';

        container.innerHTML = coursesToDisplay.map(function (course) {
            var desc = course.description || '';
            if (desc.length > 100) desc = desc.substring(0, 100) + '...';
            var priceFormatted = Number(course.price).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            var typeLabel = course.course_type === 'digital_skilling' ? 'Digital Skilling' : 'Entrepreneurship';

            // Credit badge - only show if APR > 0
            var badgeHtml = '';
            var courseApr = Number(course.default_apr || 0);
            if (course.credit_available && courseApr > 0) {
                var aprText = 'Available on ' + courseApr + '%';
                badgeHtml = '<span class="credit-badge credit-badge--text" onclick="openCreditForCourse(' + course.id + ')" style="cursor:pointer;">' + aprText + '</span>';
            }

            return '<div class="product-card">' +
                '<div class="product-image" style="position:relative;display:flex;align-items:center;justify-content:center;font-size:48px;min-height:120px;background:#f8fafc;">' +
                    '<span>' + escapeHtml(course.icon || '') + '</span>' +
                    badgeHtml +
                '</div>' +
                '<div class="product-info">' +
                    '<span class="category-badge">' + escapeHtml(typeLabel) + '</span>' +
                    '<h3>' + escapeHtml(course.title) + '</h3>' +
                    '<div class="rating">' +
                        '<span class="stars">' + getStars(course.rating) + '</span>' +
                        '<span class="rating-value">' + course.rating + ' (' + course.reviews + ')</span>' +
                    '</div>' +
                    '<p class="description">' + escapeHtml(desc) + '</p>' +
                    '<div style="font-size:12px;color:#64748b;margin-bottom:6px;">' +
                        '<span>' + escapeHtml(course.duration || '') + '</span>' +
                        (course.modules ? ' &middot; ' + course.modules + ' modules' : '') +
                        (course.lessons ? ' &middot; ' + course.lessons + ' lessons' : '') +
                        ' &middot; ' + escapeHtml(course.level || 'Beginner') +
                    '</div>' +
                    (course.instructor ? '<div style="font-size:12px;color:#94a3b8;margin-bottom:6px;">By: ' + escapeHtml(course.instructor) + '</div>' : '') +
                    '<div class="price-section">' +
                        '<span class="price">UGX ' + priceFormatted + '</span>' +
                    '</div>' +
                    '<div class="actions">' +
                        '<button onclick="viewCourseDetail(' + course.id + ')" class="btn btn-primary">View Details</button>' +
                        '<button onclick="enrollCourse(' + course.id + ')" class="btn btn-secondary">Enroll Now</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function viewCourseDetail(id) {
        var course = allCourses.find(function (c) { return c.id === id; });
        if (!course) return;

        if (course.slug) {
            window.location.href = siteUrl + '/course/' + course.slug;
        } else {
            // Fallback: generate slug from title
            var slug = course.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            window.location.href = siteUrl + '/course/' + slug;
        }
    }

    function enrollCourse(id) {
        var course = allCourses.find(function (c) { return c.id === id; });
        if (!course) return;

        showPaymentChoice({
            item: {
                price: course.price,
                title: course.title,
                creditAvailable: course.credit_available == 1,
                defaultAPR: Number(course.default_apr || 0),
                creditTermsMonths: course.credit_terms_months || '3,6'
            },
            quantity: 1,
            onConfirm: function (paymentPlan) {
                addCourseToCart('course-' + course.id, 1, paymentPlan, {
                    id: 'course-' + course.id,
                    type: 'course',
                    title: course.title,
                    price: course.price,
                    description: course.description,
                    duration: course.duration,
                    modules: course.modules,
                    lessons: course.lessons,
                    level: course.level,
                    image: course.image || '',
                    icon: course.icon || '',
                    sku: course.sku || '',
                    rating: course.rating,
                    reviews: course.reviews
                });
                showToast('Course added to cart!');
            }
        });
    }

    function openCreditForCourse(id) {
        var course = allCourses.find(function (c) { return c.id === id; });
        if (!course) return;

        showPaymentChoice({
            item: {
                price: course.price,
                title: course.title,
                creditAvailable: course.credit_available == 1,
                defaultAPR: Number(course.default_apr || 0),
                creditTermsMonths: course.credit_terms_months || '3,6'
            },
            quantity: 1,
            defaultToCredit: true,
            onConfirm: function (paymentPlan) {
                addCourseToCart('course-' + course.id, 1, paymentPlan, {
                    id: 'course-' + course.id,
                    type: 'course',
                    title: course.title,
                    price: course.price,
                    description: course.description,
                    duration: course.duration,
                    modules: course.modules,
                    lessons: course.lessons,
                    level: course.level,
                    image: course.image || '',
                    icon: course.icon || '',
                    sku: course.sku || '',
                    rating: course.rating,
                    reviews: course.reviews
                });
                showToast('Course added to cart!');
            }
        });
    }
</script>
