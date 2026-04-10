<?php
require_once __DIR__ . '/config/database.php';

// Load courses from database
$conn = getDbConnection();
$digitalResult = $conn->query("SELECT * FROM courses WHERE course_type = 'digital_skilling' ORDER BY id ASC");
$digitalCourses = [];
while ($row = $digitalResult->fetch_assoc()) { $digitalCourses[] = $row; }

$entreprenResult = $conn->query("SELECT * FROM courses WHERE course_type = 'entrepreneurship' ORDER BY id ASC");
$entreprenCourses = [];
while ($row = $entreprenResult->fetch_assoc()) { $entreprenCourses[] = $row; }

// Load categories from database
$catResult = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY name ASC");
$categories = [];
while ($row = $catResult->fetch_assoc()) { $categories[] = $row; }

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zaga Technologies</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/png" href="images/zz.png">
    <link rel="apple-touch-icon" href="images/zz.png">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <p><img class="logo" src="images/logo.png"></p>
            </div>
            <button class="menu-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="search-bar-container">
                <input type="text" id="searchInput" class="search-bar" placeholder="Search products...">
                <button id="searchBtn" class="search-btn">Search</button>
            </div>
            <div class="nav-links">
                <a href="/Zaga/" class="nav-link active">Home</a>
                <a href="/Zaga/shop" class="nav-link">Shop</a>
                <a href="/Zaga/about" class="nav-link">About Us</a>
                <a href="/Zaga/admin" class="nav-link admin-link">Admin</a>
                <a href="/Zaga/cart" class="nav-link cart-link">Cart <span id="cartCount">0</span></a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Zaga Tech Credit</h2>
            <h3>Financing Digital Empowerment!</h3>
            <p>Buy Now & Pay Later</p>
            <a href="/Zaga/shop" class="hero-btn">Apply Now</a>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <h2>Own Now & Pay Later</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $cat): ?>
                <div class="category-card" onclick="filterByCategory('<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>')">
                    <div class="category-icon"><?php echo $cat['icon']; ?></div>
                    <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-section">
        <div class="container">
            <h2>Featured Products</h2>
            <div id="featuredProducts" class="products-grid">
                <!-- Products will be loaded here by JavaScript -->
            </div>
        </div>
    </section>

    <!-- Offline Courses for Students Section -->
    <section class="courses-section">
        <div class="container">
            <h2>Offline Courses for Students</h2>

            <!-- Digital Skilling Courses Subsection -->
            <h3 style="margin-top: 40px; margin-bottom: 20px; color: #1f2937; font-size: 24px;">Digital Skilling Courses</h3>
            <div class="products-grid">
                <?php foreach ($digitalCourses as $course): ?>
                <div class="product-card">
                    <div class="product-image" aria-hidden="true" style="font-size:32px;">
                        <span><?php echo $course['icon']; ?></span>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p class="description"><?php echo htmlspecialchars(mb_substr($course['description'], 0, 100)); ?></p>
                        <div class="price-section">
                            <span class="price">UGX <?php echo number_format($course['price'], 0, '.', ','); ?></span>
                        </div>
                        <div class="actions">
                            <?php
                            // Link to dedicated course page if exists, otherwise about.html
                            $courseLinks = [
                                'Basic Computer Literacy' => 'basic-computer-literacy.html',
                                'Microsoft Office Essentials' => 'microsoft-office-essentials.html',
                            ];
                            $linkUrl = 'about.html';
                            $linkText = 'Enroll Now';
                            foreach ($courseLinks as $key => $url) {
                                if (stripos($course['title'], $key) !== false) {
                                    $linkUrl = $url;
                                    $linkText = 'Learn More';
                                    break;
                                }
                            }
                            ?>
                            <a href="<?php echo $linkUrl; ?>" class="btn-primary"><?php echo $linkText; ?></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Entrepreneurship Courses Subsection -->
            <h3 style="margin-top: 60px; margin-bottom: 20px; color: #1f2937; font-size: 24px;">Entrepreneurship Courses</h3>
            <div class="products-grid">
                <?php foreach ($entreprenCourses as $course): ?>
                <div class="product-card">
                    <div class="product-image" aria-hidden="true" style="font-size:32px;">
                        <span><?php echo $course['icon']; ?></span>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p class="description"><?php echo htmlspecialchars(mb_substr($course['description'], 0, 100)); ?></p>
                        <div class="price-section">
                            <span class="price">UGX <?php echo number_format($course['price'], 0, '.', ','); ?></span>
                        </div>
                        <div class="actions">
                            <a href="/Zaga/about" class="btn-primary">Enroll Now</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p><a href="#">How it works</a></p>
                    <p><a href="#">Our Partners</a></p>
                    <p><a href="/Zaga/about#testimonials-heading">Financed success stories</a></p>
                    <p><a href="#">FAQs</a></p>
                </div>

                <div class="footer-section">
                    <h3>Payment Terms</h3>
                    <p><a href="https://wa.me/256700706809" target="_blank">Apply Now</a></p>
                    <p><a href="#">Terms & Conditions</a></p>
                    <p><a href="https://wa.me/256700706809" target="_blank">Delivery Tracking</a></p>
                    <p><a href="#">Privacy Policy</a></p>
                </div>

                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p>Address: Kabaka Kintu House Level 1 Shop no C-03 Kampala Road-Kampaka-Uganda</p>
                    <p>Email: sales2.zagatechnologiesltd@gmail.com</p>
                    <p>Phone: +256 700 706809</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Zaga Technologies Ltd. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        // Load featured products on home page — wait for API data
        function displayFeaturedProducts() {
            const featured = products.slice(0, 8);
            const container = document.getElementById('featuredProducts');
            if (!container) return;
            container.innerHTML = featured.map(product => `
                <div class="product-card">
                    <div class="product-image">
                        <img src="${product.image}" alt="${product.title}" onerror="this.src='images/product-1.svg'">
                        ${product.discount ? `<span class="discount-badge">${product.discount}% OFF</span>` : ''}
                    </div>
                    <div class="product-info">
                        <h3>${product.title}</h3>
                        <div class="rating">
                            <span class="stars">${getStars(product.rating)}</span>
                            <span class="rating-value">${product.rating} (${product.reviews})</span>
                        </div>
                        <p class="description">${product.description.substring(0, 80)}...</p>
                        <div class="price-section">
                            <span class="price">${formatPrice(product.price)}</span>
                        </div>
                        <div class="actions">
                            <button onclick="viewProduct(${product.id})" class="btn-primary">View Details</button>
                            <button onclick="addToCart(${product.id})" class="btn-secondary">Add to Cart</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Listen for products loaded from API
        window.addEventListener('products-loaded', displayFeaturedProducts);
        // Also try on DOMContentLoaded in case products loaded first
        window.addEventListener('DOMContentLoaded', function () {
            if (window._productsLoaded) displayFeaturedProducts();
        });

        function filterByCategory(category) {
            sessionStorage.setItem('selectedCategory', category);
            window.location.href = '/Zaga/shop';
        }

        function viewProduct(id) {
            sessionStorage.setItem('productId', id);
            window.location.href = '/Zaga/product-detail';
        }
    </script>
</body>

</html>
