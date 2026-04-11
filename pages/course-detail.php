<?php
// ============================================================
// Zaga Technologies - Course Detail Page (Dynamic)
// ============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../config/database.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . SITE_URL . '/courses');
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM courses WHERE slug = ?");
$stmt->bind_param('s', $slug);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$course) {
    header('HTTP/1.0 404 Not Found');
    $page_title = 'Course Not Found';
    $current_page = 'courses';
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="container" style="padding:60px 20px;text-align:center;"><h1>Course Not Found</h1><p>The course you are looking for does not exist.</p><a href="' . SITE_URL . '/courses" class="btn btn-primary" style="margin-top:20px;">Browse Courses</a></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$page_title = safe_output($course['title']);
$current_page = 'courses';

$typeLabel = $course['course_type'] === 'digital_skilling' ? 'Digital Skilling' : 'Entrepreneurship';
$priceFormatted = number_format($course['price'], 0);

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .course-hero { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; padding: 60px 0; margin-bottom: 0; }
    .course-hero h1,
    .course-hero p,
    .course-hero span,
    .course-hero strong,
    .course-hero a,
    .course-hero div { color: white !important; }
    .course-hero a:hover { text-decoration: underline; }
    .course-hero h1 { font-size: 36px; margin-bottom: 15px; }
    .course-hero .subtitle { font-size: 18px; opacity: 0.95; margin-bottom: 20px; }
    .course-hero .breadcrumb-nav { opacity: 0.8; }
    .course-meta { display: flex; gap: 30px; flex-wrap: wrap; margin-top: 20px; }
    .meta-item { display: flex; align-items: center; gap: 8px; }
    .course-content { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; margin-bottom: 60px; padding-top: 40px; }
    .course-main { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .course-sidebar { position: sticky; top: 100px; height: fit-content; }
    .price-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: center; }
    .price-card .price { font-size: 42px; font-weight: bold; color: var(--color-primary, #2563eb); margin: 15px 0; }
    .price-card .price-label { font-size: 14px; color: var(--color-text-muted, #64748b); text-transform: uppercase; }
    .section-heading { font-size: 24px; margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 2px solid var(--color-border, #e2e8f0); }
    .enroll-benefits { background: #f0f9ff; padding: 20px; border-radius: 8px; margin-top: 20px; }
    .enroll-benefits ul { list-style: none; padding-left: 0; }
    .enroll-benefits li { padding: 8px 0 8px 28px; position: relative; }
    .enroll-benefits li:before { content: "✓"; position: absolute; left: 0; color: #16a34a; font-weight: bold; }
    @media (max-width: 768px) {
        .course-content { grid-template-columns: 1fr; }
        .course-sidebar { position: static; }
        .course-hero h1 { font-size: 24px; }
        .course-hero .subtitle { font-size: 15px; }
        .price-card .price { font-size: 32px; }
        .course-meta { gap: 15px; }
    }
</style>

<!-- Course Hero Section -->
<section class="course-hero">
    <div class="container" style="padding: 0 20px;">
        <div style="color: rgba(255,255,255,0.8); margin-bottom: 20px;">
            <a href="<?php echo SITE_URL; ?>/" style="color: white; text-decoration: none;">Home</a> /
            <a href="<?php echo SITE_URL; ?>/courses" style="color: white; text-decoration: none;">Courses</a> /
            <span><?php echo safe_output($course['title']); ?></span>
        </div>
        <h1><?php echo safe_output($course['title']); ?></h1>
        <p class="subtitle"><?php echo safe_output($course['description'] ?? ''); ?></p>
        <div class="course-meta">
            <?php if ($course['modules']): ?>
            <div class="meta-item">
                <span style="font-size: 20px;">📚</span>
                <span><strong><?php echo intval($course['modules']); ?> Modules</strong><?php echo $course['lessons'] ? ' • ' . intval($course['lessons']) . ' Lessons' : ''; ?></span>
            </div>
            <?php endif; ?>
            <?php if ($course['duration']): ?>
            <div class="meta-item">
                <span style="font-size: 20px;">⏱️</span>
                <span><strong>Duration:</strong> <?php echo safe_output($course['duration']); ?></span>
            </div>
            <?php endif; ?>
            <div class="meta-item">
                <span style="font-size: 20px;">🎯</span>
                <span><strong>Level:</strong> <?php echo safe_output($course['level'] ?? 'Beginner'); ?></span>
            </div>
            <div class="meta-item">
                <span style="font-size: 20px;">⭐</span>
                <span><strong><?php echo number_format($course['rating'], 1); ?></strong> (<?php echo intval($course['reviews']); ?> reviews)</span>
            </div>
        </div>
    </div>
</section>

<!-- Course Content -->
<div class="container" style="padding: 0 20px;">
    <div class="course-content">
        <!-- Main Content -->
        <div class="course-main">
            <h2 class="section-heading">Course Overview</h2>
            <p><?php echo nl2br(safe_output($course['description'] ?? '')); ?></p>

            <?php if ($course['instructor']): ?>
            <h2 class="section-heading">Instructor</h2>
            <p><strong><?php echo safe_output($course['instructor']); ?></strong></p>
            <?php endif; ?>

            <h2 class="section-heading">Course Details</h2>
            <table style="width:100%;border-collapse:collapse;">
                <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:12px 0;font-weight:600;">Category</td><td style="padding:12px 0;"><?php echo safe_output($typeLabel); ?></td></tr>
                <?php if ($course['duration']): ?><tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:12px 0;font-weight:600;">Duration</td><td style="padding:12px 0;"><?php echo safe_output($course['duration']); ?></td></tr><?php endif; ?>
                <?php if ($course['modules']): ?><tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:12px 0;font-weight:600;">Modules</td><td style="padding:12px 0;"><?php echo intval($course['modules']); ?></td></tr><?php endif; ?>
                <?php if ($course['lessons']): ?><tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:12px 0;font-weight:600;">Lessons</td><td style="padding:12px 0;"><?php echo intval($course['lessons']); ?></td></tr><?php endif; ?>
                <tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:12px 0;font-weight:600;">Level</td><td style="padding:12px 0;"><?php echo safe_output($course['level'] ?? 'Beginner'); ?></td></tr>
                <?php if ($course['sku']): ?><tr style="border-bottom:1px solid #e2e8f0;"><td style="padding:12px 0;font-weight:600;">SKU</td><td style="padding:12px 0;"><?php echo safe_output($course['sku']); ?></td></tr><?php endif; ?>
                <?php if ($course['credit_available'] && floatval($course['default_apr']) > 0): ?><tr><td style="padding:12px 0;font-weight:600;">Credit</td><td style="padding:12px 0;">Available on <?php echo number_format($course['default_apr'], 0); ?>%</td></tr><?php endif; ?>
            </table>
        </div>

        <!-- Sidebar -->
        <div class="course-sidebar">
            <div class="price-card">
                <p class="price-label">Course Fee</p>
                <p class="price">UGX <?php echo $priceFormatted; ?></p>
                <p style="font-size: 13px; color: var(--color-text-muted, #64748b); margin-bottom: 20px;">One-time payment<?php echo $course['credit_available'] ? ' or flexible financing' : ''; ?></p>
                <button id="enrollBtn" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 16px; margin-bottom: 10px;">Enroll Now</button>
                <button id="addToCartBtn" class="btn btn-secondary" style="width: 100%; padding: 12px; font-size: 14px;">Add to Cart</button>

                <div class="enroll-benefits">
                    <h4 style="margin-bottom: 10px; font-size: 15px;">This course includes:</h4>
                    <ul>
                        <?php if ($course['duration']): ?><li><?php echo safe_output($course['duration']); ?> of instruction</li><?php endif; ?>
                        <li>Hands-on practice sessions</li>
                        <li>Course materials included</li>
                        <li>Certificate of completion</li>
                        <?php if ($course['instructor']): ?><li>Instructor: <?php echo safe_output($course['instructor']); ?></li><?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
    var courseData = {
        id: 'course-<?php echo intval($course['id']); ?>',
        type: 'course',
        title: <?php echo json_encode($course['title']); ?>,
        price: <?php echo floatval($course['price']); ?>,
        description: <?php echo json_encode($course['description'] ?? ''); ?>,
        duration: <?php echo json_encode($course['duration'] ?? ''); ?>,
        modules: <?php echo intval($course['modules']); ?>,
        lessons: <?php echo intval($course['lessons']); ?>,
        level: <?php echo json_encode($course['level'] ?? 'Beginner'); ?>,
        image: <?php echo json_encode($course['image'] ?? ''); ?>,
        icon: <?php echo json_encode($course['icon'] ?? ''); ?>,
        sku: <?php echo json_encode($course['sku'] ?? ''); ?>,
        rating: <?php echo floatval($course['rating']); ?>,
        reviews: <?php echo intval($course['reviews']); ?>,
        creditAvailable: <?php echo $course['credit_available'] ? 'true' : 'false'; ?>,
        defaultAPR: <?php echo floatval($course['default_apr'] ?? 0); ?>
    };

    document.getElementById('enrollBtn').addEventListener('click', function () {
        showPaymentChoice({
            item: { price: courseData.price, title: courseData.title, creditAvailable: courseData.creditAvailable, defaultAPR: courseData.defaultAPR, creditTermsMonths: '<?php echo safe_output($course['credit_terms_months'] ?? '3,6'); ?>' },
            quantity: 1,
            onConfirm: function (paymentPlan) {
                addCourseToCart(courseData.id, 1, paymentPlan, courseData);
                window.location.href = '<?php echo SITE_URL; ?>/checkout';
            }
        });
    });

    document.getElementById('addToCartBtn').addEventListener('click', function () {
        showPaymentChoice({
            item: { price: courseData.price, title: courseData.title, creditAvailable: courseData.creditAvailable, defaultAPR: courseData.defaultAPR, creditTermsMonths: '<?php echo safe_output($course['credit_terms_months'] ?? '3,6'); ?>' },
            quantity: 1,
            onConfirm: function (paymentPlan) {
                addCourseToCart(courseData.id, 1, paymentPlan, courseData);
                showToast('Course added to cart!');
            }
        });
    });
</script>
