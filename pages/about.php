<?php
// ============================================================
// Zaga Technologies - About Us Page
// ============================================================

require_once __DIR__ . '/../includes/config.php';

$page_title = 'About Us';
$current_page = 'about';

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    /* ── shared ── */
    .about-img { width:100%; height:auto; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); display:block; }
    .about-heading { margin-bottom:16px; color:var(--dark-text); font-size:28px; }
    .about-text { color:var(--dark-text); line-height:1.6; margin-bottom:16px; }
    .solutions-list { list-style:none; padding:0; color:var(--dark-text); line-height:1.8; font-size:16px; }
    .solutions-list li { margin-bottom:12px; }
    .contact-heading { color:#010e50; font-size:32px; margin-bottom:16px; }
    .contact-desc { color:#010e50; font-size:18px; margin-bottom:28px; }
    .testimonials-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:18px; }

    /* ── two-column rows (desktop only) ── */
    .two-col-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 56px;
        align-items: center;
    }

    /* ── mobile: single column, keep existing stacked order ── */
    @media (max-width: 768px) {
        .two-col-row { display: block; }
        .two-col-row .col-img  { margin-bottom: 20px; }
        .two-col-row .col-text { margin-top: 0; }
        .about-heading { font-size:22px; }
        .about-text { font-size:14px; }
        .solutions-list { font-size:14px; }
        .contact-heading { font-size:24px; }
        .contact-desc { font-size:15px; }
        .testimonials-grid { grid-template-columns:1fr; }
    }
</style>

<!-- About Section — image LEFT, text RIGHT -->
<section class="about-section" style="padding:56px 20px;background:#ffffff;">
    <div class="container" style="max-width:1200px;margin:0 auto;">
        <div class="two-col-row">
            <!-- col 1: image -->
            <div class="col-img">
                <img src="<?php echo SITE_URL; ?>/images/2.jpg" alt="Zaga Technologies" class="about-img">
            </div>
            <!-- col 2: text -->
            <div class="col-text">
                <h2 class="about-heading">About</h2>
                <p class="about-text">At Zaga Technologies Ltd, we
                    believe that access to cutting-edge technology should be within everyone's reach. Our credit
                    solutions are designed to empower individuals and businesses to acquire the latest tech products
                    without the immediate financial burden. With flexible payment plans and competitive interest
                    rates, we make it easier than ever to stay ahead in the digital age.</p>
                <p class="about-text">We also bring you affordable
                    offline courses that
                    can be accessed immediately after purchasing our products.</p>
                <p style="color:var(--dark-text);line-height:1.6;">Join us on a journey to unlock the potential of
                    technology through smart financing options tailored to your needs.</p>
            </div>
        </div>
    </div>
</section>

<!-- Our Solutions Section — text LEFT, image RIGHT -->
<section class="solutions-section" style="padding:56px 20px;background:#f8fafc;">
    <div class="container" style="max-width:1200px;margin:0 auto;">
        <div class="two-col-row">
            <!-- col 1: text -->
            <div class="col-text">
                <h2 class="about-heading">Our Solutions</h2>
                <ul class="solutions-list">
                    <li>&#10003; Buy Now &amp; Pay later solution</li>
                    <li>&#10003; 3 - 6 Months Financing period</li>
                    <li>&#10003; Flexible Weekly &amp; monthly payments via mobile money</li>
                    <li>&#10003; Full ownership of the device after full payment</li>
                    <li>&#10003; Affordable high-quality computing devices.</li>
                    <li>&#10003; Affordable Offline Learning Courses.</li>
                </ul>
            </div>
            <!-- col 2: image -->
            <div class="col-img">
                <img src="<?php echo SITE_URL; ?>/images/1.jpg" alt="Our Solutions" class="about-img">
            </div>
        </div>
    </div>
</section>

<!-- Contact Us CTA Section -->
<section class="contact-cta-section" style="padding:24px 10px;background:#ffffff;text-align:center;">
    <div class="container" style="max-width:600px;margin:0 auto;padding:0 15px;">
        <h2 class="contact-heading">Contact Us Today</h2>
        <p class="contact-desc">Start your digital financing
            journey with Zaga Technologies. We're here to help you acquire the tech you need on flexible payment
            terms.</p>

        <div style="background:#f0f6ff;padding:24px;border-radius:8px;">
            <div style="margin-bottom:20px;">
                <h3 style="color:#010e50;font-size:16px;margin-bottom:8px;">Email</h3>
                <a href="mailto:sales2.zagatechnologiesltd@gmail.com"
                    style="color:#010e50;text-decoration:none;font-size:14px;word-break:break-all;">sales2.zagatechnologiesltd@gmail.com</a>
            </div>
            <div>
                <h3 style="color:#010e50;font-size:16px;margin-bottom:8px;">Phone</h3>
                <a href="tel:+256700706809" style="color:#010e50;text-decoration:none;font-size:15px;">+256 700
                    706809</a>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section" aria-labelledby="testimonials-heading"
    style="padding:40px 0;background:#f8fafc;">
    <div class="container" style="padding:0 15px;">
        <h2 id="testimonials-heading" style="text-align:center;margin-bottom:20px;color:var(--dark-text);">What
            our customers say</h2>
        <p style="text-align:center;color:var(--light-text);margin-bottom:28px;">Real stories from customers who
            financed their purchases with us.</p>

        <div class="testimonials-grid" id="testimonialsGrid">
            <!-- Testimonials loaded dynamically from database -->
            <p style="text-align:center;color:#94a3b8;">Loading testimonials...</p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
    // Load testimonials dynamically from database
    fetch('<?php echo SITE_URL; ?>/api/testimonials.php?action=list')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var grid = document.getElementById('testimonialsGrid');
            if (data.success && data.data.length > 0) {
                grid.innerHTML = data.data.map(function(t) {
                    return '<article class="testimonial-card" style="background:white;padding:18px;border-radius:8px;box-shadow:0 6px 18px rgba(15,23,42,0.06);">' +
                        '<div style="display:flex;gap:12px;align-items:center;margin-bottom:10px;">' +
                            '<img src="' + escapeHtml(t.image || '<?php echo SITE_URL; ?>/images/user1.jpg') + '" alt="Photo of ' + escapeHtml(t.name) + '" style="width:56px;height:56px;border-radius:50%;object-fit:cover;" onerror="this.src=\'<?php echo SITE_URL; ?>/images/user1.jpg\'">' +
                            '<div>' +
                                '<strong>' + escapeHtml(t.name) + '</strong>' +
                                '<div style="color:var(--light-text);font-size:13px;">' + escapeHtml(t.role || '') + '</div>' +
                            '</div>' +
                        '</div>' +
                        '<p style="color:var(--dark-text);line-height:1.4;">"' + escapeHtml(t.content) + '"</p>' +
                    '</article>';
                }).join('');
            } else {
                grid.innerHTML = '<p style="text-align:center;color:#94a3b8;">No testimonials yet.</p>';
            }
        })
        .catch(function() {
            // Fallback: show hardcoded testimonials if API fails
            var grid = document.getElementById('testimonialsGrid');
            var fallback = [
                {name:'Amina N.',role:'Small business owner',image:'<?php echo SITE_URL; ?>/images/user1.jpg',content:'The credit option made it possible to upgrade my laptop without draining savings. The deposit was easy and payments were clear and they had excellent service.'},
                {name:'David M.',role:'Freelancer',image:'<?php echo SITE_URL; ?>/images/user2.jpg',content:'I bought a monitor on a 3-month plan. The monthly payments fit my budget and customer support was very helpful with schedule questions.'},
                {name:'Grace K.',role:'Student',image:'<?php echo SITE_URL; ?>/images/user3.jpg',content:'As a student, the deposit and installments meant I could get a tablet for remote classes straight away. The schedule was transparent and easy to follow.'},
                {name:'Samuel L.',role:'IT Consultant',image:'<?php echo SITE_URL; ?>/images/user4.jpg',content:'Clear terms, straightforward checkout and helpful reminders paying over six months made upgrading our office PCs achievable.'}
            ];
            grid.innerHTML = fallback.map(function(t) {
                return '<article class="testimonial-card" style="background:white;padding:18px;border-radius:8px;box-shadow:0 6px 18px rgba(15,23,42,0.06);">' +
                    '<div style="display:flex;gap:12px;align-items:center;margin-bottom:10px;">' +
                        '<img src="' + t.image + '" alt="Photo of ' + t.name + '" style="width:56px;height:56px;border-radius:50%;object-fit:cover;">' +
                        '<div><strong>' + t.name + '</strong><div style="color:var(--light-text);font-size:13px;">' + t.role + '</div></div>' +
                    '</div>' +
                    '<p style="color:var(--dark-text);line-height:1.4;">"' + t.content + '"</p>' +
                '</article>';
            }).join('');
        });

    if (typeof updateCartCount === 'function') updateCartCount();
</script>
