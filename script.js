// script.js
// Core product data, cart logic, and shared utilities for the Zaga Tech Credit demo

(function () {
    // --- Initialization: load products from PHP/MySQL API ---
    window.products = [];
    window.courses = [];
    window._productsLoaded = false;
    window._coursesLoaded = false;

    // Load products from database via API
    window.loadProductsFromDB = function () {
        return fetch('api/products.php?action=list')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success && data.data) {
                    window.products = data.data.map(function (p) {
                        // Normalize field names for compatibility with existing front-end code
                        p.inStock = p.in_stock == 1;
                        p.additionalImages = p.additional_images || [];
                        return p;
                    });
                    window._productsLoaded = true;
                    // Cache in localStorage for offline/fallback
                    try { localStorage.setItem('products', JSON.stringify(window.products)); } catch (e) {}
                    // Dispatch event so pages can react
                    window.dispatchEvent(new CustomEvent('products-loaded'));
                }
            })
            .catch(function (err) {
                console.warn('API fetch failed, falling back to localStorage/generated products', err);
                var stored = localStorage.getItem('products');
                if (stored) {
                    try { window.products = JSON.parse(stored); } catch (e) { window.products = generateProducts(120); }
                } else {
                    window.products = generateProducts(120);
                    localStorage.setItem('products', JSON.stringify(window.products));
                }
                window._productsLoaded = true;
                window.dispatchEvent(new CustomEvent('products-loaded'));
            });
    };

    // Load courses from database via API
    window.loadCoursesFromDB = function () {
        return fetch('api/courses.php?action=list')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success && data.data) {
                    window.courses = data.data.map(function (c) {
                        // Normalize for front-end compatibility
                        c.type = 'course';
                        c.inStock = c.in_stock == 1;
                        c.creditAvailable = c.credit_available == 1;
                        c.defaultAPR = c.default_apr || 0;
                        c.category = c.course_type === 'digital_skilling' ? 'Digital Skills' : 'Entrepreneurship';
                        return c;
                    });
                    window._coursesLoaded = true;
                    window.dispatchEvent(new CustomEvent('courses-loaded'));
                }
            })
            .catch(function (err) {
                console.warn('Courses API fetch failed', err);
                window._coursesLoaded = true;
                window.dispatchEvent(new CustomEvent('courses-loaded'));
            });
    };

    // Start loading from API immediately
    loadProductsFromDB();
    loadCoursesFromDB();

    // ===== Utility functions used across pages =====

    // HTML escaping to prevent XSS when injecting user data into DOM
    window.escapeHtml = function (str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    };

    window.getStars = function (rating) {
        const full = Math.floor(rating);
        const half = rating - full >= 0.5;
        let stars = '';
        for (let i = 0; i < full; i++) stars += '★';
        if (half) stars += '☆';
        while (stars.length < 5) stars += '☆';
        return stars;
    };

    window.formatPrice = function (price) {
        return 'UGX ' + Number(price).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };

    // ===== Cart functions =====
    const CART_KEY = 'cart';

    window.getCart = function () {
        try {
            const raw = localStorage.getItem(CART_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            console.error('Failed to read cart', e);
            return [];
        }
    };

    window.saveCart = function (cart) {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
        updateCartCount();
    };

    // paymentPlan: null or { type: 'credit', months: 3|6 }
    // replace: if true, set quantity instead of incrementing (used by Buy Now)
    window.addToCart = function (productId, quantity = 1, paymentPlan = null, replace = false) {
        const prodId = Number(productId);
        let cart = getCart();
        let item = cart.find((c) => c.productId === prodId);
        const product = products.find((p) => p.id === prodId);
        if (!product) {
            if (typeof showToast === 'function') showToast('Product not found', 'error'); else console.warn('Product not found');
            return;
        }
        if (!item) {
            cart.push({ productId: prodId, quantity: Number(quantity || 1), paymentPlan: paymentPlan });
        } else {
            item.quantity = replace ? Number(quantity || 1) : item.quantity + Number(quantity || 1);
            if (paymentPlan) item.paymentPlan = paymentPlan;
        }
        saveCart(cart);
    };

    // Credit computation helper with optional interest support
    // interest rate is annual percentage, defaults to 0% (no interest)
    window.computeCredit = function (amount, months, annualInterestRate = 0) {
        months = Number(months) || 3;
        amount = Number(amount);
        annualInterestRate = Number(annualInterestRate) || 0;

        const deposit = Number((amount * 0.2).toFixed(2));
        const principal = Number((amount - deposit).toFixed(2));

        let schedule = [];
        let monthlyPayment = 0;
        let totalInterest = 0;

        if (annualInterestRate > 0) {
            // Calculate amortized payment using standard formula
            const monthlyRate = annualInterestRate / 100 / 12;
            if (monthlyRate > 0) {
                const factor = Math.pow(1 + monthlyRate, months);
                monthlyPayment = Number((principal * (monthlyRate * factor) / (factor - 1)).toFixed(2));
            } else {
                monthlyPayment = Number((principal / months).toFixed(2));
            }

            // Generate amortization schedule
            let balance = principal;
            for (let m = 0; m < months; m++) {
                const interestPayment = Number((balance * monthlyRate).toFixed(2));
                const principalPayment = Number((monthlyPayment - interestPayment).toFixed(2));
                balance = Number((balance - principalPayment).toFixed(2));
                totalInterest += interestPayment;

                schedule.push({
                    month: m + 1,
                    payment: monthlyPayment,
                    principal: principalPayment,
                    interest: interestPayment,
                    balance: Math.max(0, balance)
                });
            }
        } else {
            // No interest: simple equal monthly payments
            monthlyPayment = Number((principal / months).toFixed(2));
            for (let m = 0; m < months; m++) {
                schedule.push({
                    month: m + 1,
                    payment: monthlyPayment,
                    principal: monthlyPayment,
                    interest: 0,
                    balance: Number((principal - (monthlyPayment * (m + 1))).toFixed(2))
                });
            }
        }

        return {
            amount: Number(amount.toFixed(2)),
            deposit,
            remaining: principal,
            months,
            monthly: monthlyPayment,
            totalInterest: Number(totalInterest.toFixed(2)),
            schedule: schedule,
            annualInterestRate: annualInterestRate
        };
    };

    window.removeFromCart = function (itemId, itemType = 'product') {
        let cart = getCart();
        if (itemType === 'course') {
            cart = cart.filter((c) => !(c.itemId === itemId && c.type === 'course'));
        } else {
            const prodId = Number(itemId);
            cart = cart.filter((c) => c.productId !== prodId);
        }
        saveCart(cart);
        if (typeof displayCart === 'function') displayCart();
        if (typeof displayCheckoutSummary === 'function') displayCheckoutSummary();
    };

    window.updateQuantity = function (itemId, newQuantity, itemType = 'product') {
        const qty = Number(newQuantity);
        let cart = getCart();
        let item;
        if (itemType === 'course') {
            item = cart.find((c) => c.itemId === itemId && c.type === 'course');
        } else {
            const prodId = Number(itemId);
            item = cart.find((c) => c.productId === prodId);
        }
        if (item) {
            if (qty <= 0) {
                if (itemType === 'course') {
                    cart = cart.filter((c) => !(c.itemId === itemId && c.type === 'course'));
                } else {
                    const prodId = Number(itemId);
                    cart = cart.filter((c) => c.productId !== prodId);
                }
            } else {
                item.quantity = qty;
            }
            saveCart(cart);
            if (typeof displayCart === 'function') displayCart();
            if (typeof displayCheckoutSummary === 'function') displayCheckoutSummary();
        }
    };

    window.updateCartCount = function () {
        const cart = getCart();
        const count = cart.reduce((s, i) => s + (i.quantity || 0), 0);
        const elements = document.querySelectorAll('#cartCount');
        elements.forEach(function (el) {
            el.textContent = count;
            if (count > 0) {
                el.classList.add('has-items');
            } else {
                el.classList.remove('has-items');
            }
        });
    };

    // Validate cart: remove items whose product no longer exists
    window.validateCart = function () {
        if (!window.products || window.products.length === 0) return;
        const cart = getCart();
        const validCart = cart.filter(function (item) {
            if (item.type === 'course') return true;
            return window.products.some(function (p) { return p.id === item.productId; });
        });
        if (validCart.length !== cart.length) {
            saveCart(validCart);
        } else {
            updateCartCount();
        }
    };

    // Run validation after products load
    window.addEventListener('products-loaded', function () {
        validateCart();
    });

    // Keep cart count updated when other tabs modify localStorage
    window.addEventListener('storage', function (e) {
        if (e.key === CART_KEY) updateCartCount();
        if (e.key === 'products') {
            try {
                window.products = JSON.parse(e.newValue || '[]');
            } catch (err) {
                console.error('Failed to parse updated products from storage', err);
            }
        }
    });

    // Initialize cart count on load
    document.addEventListener('DOMContentLoaded', function () {
        updateCartCount();
        renderUserArea();
        // Hide admin link if not authenticated
        try {
            const authed = localStorage.getItem('admin_auth') === '1';
            if (!authed) {
                document.querySelectorAll('.admin-link').forEach(el => {
                    el.style.display = 'none';
                });
            }
        } catch (e) {
            console.warn('Admin auth check failed', e);
        }
    });

    // ===== Navigation helpers =====
    window.viewProduct = function (id) {
        sessionStorage.setItem('productId', id);
        window.location.href = '/Zaga/product-detail';
    };

    window.viewProductDetail = function (id) {
        sessionStorage.setItem('productId', id);
        window.location.href = '/Zaga/product-detail';
    };

    // ===== Pagination helpers =====
    window.paginate = function (array, page = 1, perPage = 12) {
        const total = array.length;
        const totalPages = Math.max(1, Math.ceil(total / perPage));
        const current = Math.min(Math.max(1, page), totalPages);
        const start = (current - 1) * perPage;
        const end = start + perPage;
        return {
            pageItems: array.slice(start, end),
            totalPages: totalPages,
            currentPage: current,
            perPage: perPage,
            totalItems: total,
        };
    };

    window.renderPaginationControls = function (containerId, currentPage, totalPages) {
        const container = document.getElementById(containerId);
        if (!container) return;
        let html = '';
        html += `<button ${currentPage === 1 ? 'disabled' : ''} class="pag-btn" data-page="${currentPage - 1}">Prev</button>`;
        // show up to 7 page buttons
        const start = Math.max(1, currentPage - 3);
        const end = Math.min(totalPages, currentPage + 3);
        for (let p = start; p <= end; p++) {
            html += `<button class="pag-btn ${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>`;
        }
        html += `<button ${currentPage === totalPages ? 'disabled' : ''} class="pag-btn" data-page="${currentPage + 1}">Next</button>`;
        container.innerHTML = html;
        container.querySelectorAll('.pag-btn').forEach((btn) => {
            btn.addEventListener('click', function () {
                const page = Number(this.getAttribute('data-page'));
                container.dispatchEvent(new CustomEvent('page-change', { detail: { page } }));
            });
        });
    };

    // ===== User auth (simple simulation) =====
    const USER_KEY = 'zagatech_current_user';

    window.getCurrentUser = function () {
        try {
            return JSON.parse(localStorage.getItem(USER_KEY));
        } catch (e) {
            return null;
        }
    };

    window.signInUser = function (email, name) {
        const user = { email: email.toLowerCase(), name: name || email.split('@')[0] };
        localStorage.setItem(USER_KEY, JSON.stringify(user));
        renderUserArea();
        return user;
    };

    window.signOutUser = function () {
        localStorage.removeItem(USER_KEY);
        renderUserArea();
    };

    function renderUserArea() {
        const navLinks = document.querySelectorAll('.nav-links');
        navLinks.forEach((nav) => {
            // Avoid duplicating
            if (nav.querySelector('.user-area')) return;
            const userArea = document.createElement('div');
            userArea.className = 'user-area';
            userArea.style.display = 'flex';
            userArea.style.gap = '10px';
            userArea.style.alignItems = 'center';

            const user = getCurrentUser();
            if (user) {
                const ordersLink = document.createElement('a');
                ordersLink.href = '/Zaga/order-history';
                ordersLink.textContent = 'My Orders';
                ordersLink.className = 'nav-link';

                const signOut = document.createElement('button');
                signOut.className = 'btn-secondary';
                signOut.textContent = 'Sign out';
                signOut.onclick = function () {
                    signOutUser();
                };

                const nameEl = document.createElement('span');
                nameEl.textContent = user.name;
                nameEl.style.fontWeight = '600';

                userArea.appendChild(nameEl);
                userArea.appendChild(ordersLink);
                userArea.appendChild(signOut);
            } else {
                const loginBtn = document.createElement('button');
                loginBtn.className = 'btn-secondary';
                loginBtn.textContent = 'Sign in';
                loginBtn.onclick = showSignInModal;
                userArea.appendChild(loginBtn);
            }

            nav.appendChild(userArea);
        });
    }

    function showSignInModal() {
        if (document.getElementById('signin-modal')) return;
        const modal = document.createElement('div');
        modal.id = 'signin-modal';
        modal.innerHTML = `
            <div class="modal-backdrop" style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:2000;">
                <div style="background:white;padding:20px;border-radius:8px;max-width:400px;width:100%;">
                    <h3>Sign in</h3>
                    <p>Enter your email to view orders and checkout faster.</p>
                    <input id="signinEmail" type="email" placeholder="you@example.com" style="width:100%;padding:8px;margin:8px 0;" />
                    <input id="signinName" type="text" placeholder="Display name (optional)" style="width:100%;padding:8px;margin:8px 0;" />
                    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px;">
                        <button id="signinCancel" class="btn-secondary">Cancel</button>
                        <button id="signinConfirm" class="btn-primary">Sign in</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        document.getElementById('signinCancel').onclick = () => modal.remove();
        document.getElementById('signinConfirm').onclick = () => {
            const email = document.getElementById('signinEmail').value.trim();
            const name = document.getElementById('signinName').value.trim();
            if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
                if (typeof showToast === 'function') showToast('Please enter a valid email', 'error'); else console.warn('Please enter a valid email');
                return;
            }
            signInUser(email, name || null);
            modal.remove();
        };
    }

    // ===== Order placement helper (used by checkout) =====
    // Enhanced to generate and store full payment schedules
    window.placeOrder = function (orderMeta) {
        // orderMeta expected to include: customer (name), email, total, totalNow, items
        const orders = JSON.parse(localStorage.getItem('orders') || '[]');
        const currentUser = getCurrentUser();
        const orderNumber = 'ORD-' + Date.now();

        // Generate full amortization schedule for credit items
        let fullSchedule = [];
        let orderDate = new Date();

        if (orderMeta.items) {
            orderMeta.items.forEach(item => {
                if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                    const product = products.find(p => p.id === item.productId);
                    if (product) {
                        const itemTotal = product.price * item.quantity;
                        const interestRate = item.paymentPlan.interestRate || 0;
                        const creditInfo = computeCredit(itemTotal, item.paymentPlan.months, interestRate);

                        // Add product title to schedule for reference
                        creditInfo.schedule.forEach((payment, idx) => {
                            const dueDate = new Date(orderDate);
                            dueDate.setMonth(dueDate.getMonth() + payment.month);
                            fullSchedule.push({
                                productId: item.productId,
                                productTitle: product.title,
                                dueDate: dueDate.toLocaleDateString(),
                                dueDateObj: dueDate,
                                amount: payment.payment,
                                principal: payment.principal,
                                interest: payment.interest,
                                remainingBalance: payment.balance,
                                paid: false,
                                paidDate: null,
                                paidAmount: 0
                            });
                        });
                    }
                }
            });
        }

        const orderData = Object.assign({}, orderMeta, {
            orderNumber: orderNumber,
            date: new Date().toLocaleString(),
            user: currentUser ? currentUser.email : null,
            schedule: fullSchedule,
            paymentsMade: []
        });

        orders.push(orderData);
        localStorage.setItem('orders', JSON.stringify(orders));
        // clear cart after order
        localStorage.removeItem(CART_KEY);
        updateCartCount();
        return orderData;
    };

    // ===== Product generation helper =====
    function generateProducts(count) {
        const categories = ['Laptops', 'Desktops', 'Tablets', 'Accessories', 'Peripherals', 'Storage'];
        const brands = ['Astra', 'Orion', 'Zephyr', 'Nimbus', 'Vertex', 'Photon'];
        const laptopModels = ['Air', 'Pro', 'Slim', 'Max', 'Studio'];
        const desktopModels = ['Ranger', 'Titan', 'Core', 'Quantum'];
        const tabletModels = ['Tab', 'Note', 'Pad', 'Slate'];
        const accessoryNames = ['Headset', 'Charger', 'Dock', 'Case', 'Adapter', 'Power Bank'];
        const peripheralNames = ['Keyboard', 'Mouse', 'Monitor', 'Webcam', 'Microphone'];
        const storageNames = ['SSD', 'HDD', 'NVMe', 'Portable SSD'];

        const products = [];
        let idCounter = 1;

        function localPic(id, w = 600, h = 400) {
            // Use local images if available (images/product-1.svg ... product-12.svg)
            const idx = ((id - 1) % 12) + 1;
            return `./images/product-${idx}.svg`;
        }

        for (let i = 0; i < count; i++) {
            const category = categories[i % categories.length];
            let title = '';
            let basePrice = 99 + Math.round(Math.random() * 2900);
            let rating = Number((3 + Math.random() * 2).toFixed(1));
            let reviews = Math.floor(Math.random() * 1200);
            let description = '';
            let features = [];
            let sku = `TS-${String(idCounter).padStart(5, '0')}`;
            let warranty = `${1 + (idCounter % 3)} Year${(idCounter % 3) === 1 ? '' : 's'}`;
            let inStock = Math.random() > 0.05;
            let stock = inStock ? Math.floor(5 + Math.random() * 195) : 0;

            if (category === 'Laptops') {
                const brand = brands[idCounter % brands.length];
                const model = laptopModels[idCounter % laptopModels.length];
                title = `${brand} ${model} ${2020 + (idCounter % 6)}`;
                basePrice = 499 + Math.round(Math.random() * 2500);
                description = `The ${title} is a powerful, portable laptop featuring modern processors, vivid display, and long battery life. Perfect for productivity and light content creation.`;
                features = ['Intel/AMD latest-gen CPU', '8-32GB RAM options', 'Fast NVMe storage', 'Backlit keyboard', 'Wi-Fi 6'];
            } else if (category === 'Desktops') {
                const brand = brands[idCounter % brands.length];
                const model = desktopModels[idCounter % desktopModels.length];
                title = `${brand} ${model} Desktop ${String(idCounter).slice(-3)}`;
                basePrice = 399 + Math.round(Math.random() * 3000);
                description = `A high-performance desktop built for gaming, creativity and business. Expandable, serviceable and ready for heavy workloads.`;
                features = ['High-performance CPU', 'Discrete GPU options', 'Large cooling system', 'Multiple storage bays', 'Upgradeable RAM'];
            } else if (category === 'Tablets') {
                const brand = brands[idCounter % brands.length];
                const model = tabletModels[idCounter % tabletModels.length];
                title = `${brand} ${model} ${idCounter % 4 === 0 ? 'Plus' : 'Mini'}`;
                basePrice = 199 + Math.round(Math.random() * 1200);
                description = `A sleek tablet for media, reading, and light productivity. Crisp display and long battery life make it a great companion on the go.`;
                features = ['High-resolution touch display', 'Stylus support', 'Wi-Fi & LTE options', 'Long battery life'];
            } else if (category === 'Accessories') {
                const name = accessoryNames[idCounter % accessoryNames.length];
                title = `Universal ${name} by ${brands[idCounter % brands.length]}`;
                basePrice = 9 + Math.round(Math.random() * 190);
                description = `Premium ${name.toLowerCase()} compatible with most devices. Built for durability and performance.`;
                features = ['Durable build', 'Warranty included', 'Universal compatibility'];
            } else if (category === 'Peripherals') {
                const name = peripheralNames[idCounter % peripheralNames.length];
                title = `${brands[idCounter % brands.length]} ${name} ${idCounter % 3 === 0 ? 'X' : ''}`;
                basePrice = 19 + Math.round(Math.random() * 500);
                description = `Reliable ${name.toLowerCase()} for daily use. Comfortable design and precise performance.`;
                features = ['Comfort-focused design', 'Plug & play', 'Multi-device pairing'];
            } else if (category === 'Storage') {
                const name = storageNames[idCounter % storageNames.length];
                title = `${brands[idCounter % brands.length]} ${name} ${(idCounter % 5) * 128}GB`;
                basePrice = 29 + Math.round(Math.random() * 600);
                description = `Fast and reliable storage for your important files, media, and backups.`;
                features = ['High endurance', 'Fast read/write speeds', 'Compact design'];
            }

            // Some products have a discount
            let discount = null;
            let originalPrice = null;
            if (Math.random() > 0.8) {
                const pct = [10, 15, 20, 25][Math.floor(Math.random() * 4)];
                discount = pct;
                originalPrice = basePrice;
                basePrice = Math.round(basePrice * (1 - pct / 100));
            }

            // Compose product object
            const product = {
                id: idCounter,
                title: title,
                category: category,
                price: basePrice,
                originalPrice: originalPrice,
                discount: discount,
                rating: rating,
                reviews: reviews,
                description: description,
                features: features,
                sku: sku,
                warranty: warranty,
                inStock: inStock,
                stock: stock,
                image: localPic(idCounter),
                additionalImages: [localPic(idCounter, 400, 300), localPic(idCounter + 1, 400, 300)],
            };

            products.push(product);
            idCounter++;
        }

        return products;
    }

    // Expose a small helper so devs can regenerate default products during development
    window.__regenerateDefaultProducts = function (count = 120) {
        window.products = generateProducts(count);
        localStorage.setItem('products', JSON.stringify(window.products));
        updateCartCount();
        return window.products;
    };

    // Make sure changes to products are saved when code updates them directly
    // (for example admin panel will push into `products` and call localStorage.setItem itself)
})();

// Mobile Menu Toggle
function initMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            menuToggle.classList.toggle('active');
            navLinks.classList.toggle('active');
            document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (navLinks.classList.contains('active') &&
                !navLinks.contains(e.target) &&
                !menuToggle.contains(e.target)) {
                menuToggle.classList.remove('active');
                navLinks.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Close menu when clicking a link
        const links = navLinks.querySelectorAll('.nav-link');
        links.forEach(link => {
            link.addEventListener('click', () => {
                menuToggle.classList.remove('active');
                navLinks.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    }
}

// Initialize mobile menu on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initMobileMenu();
        initHowItWorksModal();
        initFAQsModal();
        initTermsModal();
        initPrivacyModal();
    });
} else {
    initMobileMenu();
    initHowItWorksModal();
    initFAQsModal();
    initTermsModal();
    initPrivacyModal();
}

// Footer: "How it works" modal
function initHowItWorksModal() {
    // Find all footer links that match "How it works"
    const links = Array.from(document.querySelectorAll('footer a, .footer a, .site-footer a, p a'))
        .filter(a => /how it works/i.test(a.textContent.trim()));

    if (!links.length) return;

    // Create modal once and reuse
    let modal = document.getElementById('howItWorksModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'howItWorksModal';
        modal.style.position = 'fixed';
        modal.style.inset = '0';
        modal.style.background = 'rgba(0,0,0,0.5)';
        modal.style.display = 'none';
        modal.style.zIndex = '2000';

        const panel = document.createElement('div');
        panel.style.position = 'absolute';
        panel.style.right = '50%';
        panel.style.top = '50%';
        panel.style.transform = 'translate(50%, -50%)';
        panel.style.width = 'min(680px, 92vw)';
        panel.style.maxHeight = '85vh';
        panel.style.overflowY = 'auto';
        panel.style.background = '#ffffff';
        panel.style.borderRadius = '12px';
        panel.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15)';
        panel.style.padding = '24px';

        panel.innerHTML = `
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
                <h2 style="margin:0; font-size:22px; color:#0f172a;">How Zaga Tech Credit Works</h2>
                <button id="howItWorksClose" aria-label="Close" style="border:none; background:#e5e7eb; color:#0f172a; border-radius:8px; padding:8px 12px; cursor:pointer;">Close</button>
            </div>
            <p style="color:#64748b; margin:0 0 12px;">Transparent, flexible financing to help you get the tech you need.</p>

            <div style="display:grid; grid-template-columns:1fr; gap:16px;">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px;">
                    <h3 style="margin-top:0; color:#0f172a;">1) Deposit & Approval</h3>
                    <ul style="margin:8px 0 0 20px; color:#334155;">
                        <li>Pay an upfront deposit of <strong>20%</strong> of the item price.</li>
                        <li>Instant, simulated approval for demonstration purposes.</li>
                        <li>VAT is applied at <strong>18%</strong> to the total order.</li>
                    </ul>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px;">
                    <h3 style="margin-top:0; color:#0f172a;">2) Flexible Repayment</h3>
                    <ul style="margin:8px 0 0 20px; color:#334155;">
                        <li>Choose a plan between <strong>3</strong> and <strong>6 months</strong>.</li>
                        <li>Remaining balance is split into equal monthly installments.</li>
                        <li>All schedules are shown clearly at checkout and in your order history.</li>
                    </ul>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px;">
                    <h3 style="margin-top:0; color:#0f172a;">3) What You’ll See</h3>
                    <ul style="margin:8px 0 0 20px; color:#334155;">
                        <li>Deposit amount and monthly payment breakdown.</li>
                        <li>VAT (18%) shown separately for transparency.</li>
                        <li>Full payment schedule available in <em>Order History</em>.</li>
                    </ul>
                </div>
                <div style="background:#fff4e6; border:1px solid #ffc266; border-radius:10px; padding:16px;">
                    <h3 style="margin-top:0; color:#0f172a;">4) Offline Courses</h3>
                    <ul style="margin:8px 0 0 20px; color:#334155;">
                        <li>We offer <strong>Digital Skilling</strong> and <strong>Entrepreneurship</strong> courses for students.</li>
                        <li>These courses can be <strong>pre-installed</strong> on any device you purchase from us.</li>
                        <li>Access course materials offline anytime, anywhere on your new device.</li>
                        <li>Contact us during checkout to request pre-installation of your preferred courses.</li>
                    </ul>
                </div>            </div>
        `;

        modal.appendChild(panel);
        document.body.appendChild(modal);

        const close = () => { modal.style.display = 'none'; document.body.style.overflow = ''; };
        modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
        panel.querySelector('#howItWorksClose').addEventListener('click', close);
    }

    const open = (e) => {
        if (e) e.preventDefault();
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    links.forEach(link => {
        link.addEventListener('click', open);
        link.setAttribute('href', '#how-it-works');
        link.setAttribute('aria-controls', 'howItWorksModal');
        link.setAttribute('role', 'button');
    });
}

// Footer: "FAQs" modal
function initFAQsModal() {
    const links = Array.from(document.querySelectorAll('footer a, .footer a, .site-footer a, p a'))
        .filter(a => /faqs?/i.test(a.textContent.trim()));

    if (!links.length) return;

    let modal = document.getElementById('faqsModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'faqsModal';
        modal.style.position = 'fixed';
        modal.style.inset = '0';
        modal.style.background = 'rgba(0,0,0,0.5)';
        modal.style.display = 'none';
        modal.style.zIndex = '2000';

        const panel = document.createElement('div');
        panel.style.position = 'absolute';
        panel.style.right = '50%';
        panel.style.top = '50%';
        panel.style.transform = 'translate(50%, -50%)';
        panel.style.width = 'min(720px, 92vw)';
        panel.style.maxHeight = '85vh';
        panel.style.overflowY = 'auto';
        panel.style.background = '#ffffff';
        panel.style.borderRadius = '12px';
        panel.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15)';
        panel.style.padding = '24px';

        panel.innerHTML = `
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
                <h2 style="margin:0; font-size:22px; color:#0f172a;">FAQs – Zaga Tech Credit</h2>
                <button id="faqsClose" aria-label="Close" style="border:none; background:#e5e7eb; color:#0f172a; border-radius:8px; padding:8px 12px; cursor:pointer;">Close</button>
            </div>
            <p style="color:#64748b; margin:0 0 12px;">Answers to common questions about deposits, VAT, plans, and payments.</p>

            <div style="display:grid; grid-template-columns:1fr; gap:14px;">
                <details style="border:1px solid #e2e8f0; border-radius:10px; padding:12px; background:#f8fafc;">
                    <summary style="cursor:pointer; color:#0f172a; font-weight:600;">What deposit is required?</summary>
                    <p style="color:#334155; margin:8px 0 0;">A 20% deposit of the total item price is required to start your plan.</p>
                </details>
                <details style="border:1px solid #e2e8f0; border-radius:10px; padding:12px; background:#f8fafc;">
                    <summary style="cursor:pointer; color:#0f172a; font-weight:600;">How is VAT handled?</summary>
                    <p style="color:#334155; margin:8px 0 0;">VAT is charged at 18% and displayed clearly on the cart and checkout pages.</p>
                </details>
                <details style="border:1px solid #e2e8f0; border-radius:10px; padding:12px; background:#f8fafc;">
                    <summary style="cursor:pointer; color:#0f172a; font-weight:600;">What repayment plans are available?</summary>
                    <p style="color:#334155; margin:8px 0 0;">You can choose between 3 to 6 monthly installments after the deposit.</p>
                </details>
                <details style="border:1px solid #e2e8f0; border-radius:10px; padding:12px; background:#f8fafc;">
                    <summary style="cursor:pointer; color:#0f172a; font-weight:600;">Where can I see my payment schedule?</summary>
                    <p style="color:#334155; margin:8px 0 0;">Your full schedule is shown at checkout and stored in Order History for future reference.</p>
                </details>
                <details style="border:1px solid #e2e8f0; border-radius:10px; padding:12px; background:#f8fafc;">
                    <summary style="cursor:pointer; color:#0f172a; font-weight:600;">Is this a real credit approval?</summary>
                    <p style="color:#334155; margin:8px 0 0;">This demo simulates approvals client-side. In production, approvals would be handled securely on the server.</p>
                </details>
                <details style="border:1px solid #e2e8f0; border-radius:10px; padding:12px; background:#f8fafc;">
                    <summary style="cursor:pointer; color:#0f172a; font-weight:600;">What payment methods are accepted?</summary>
                    <p style="color:#334155; margin:8px 0 0;">We accept <strong>Mobile Money</strong> payments. Payment is required <strong>after delivery</strong> of your device. Our agent will confirm delivery and collect payment via mobile money transfer.</p>
                </details>
                <details style="border:1px solid #e2e8f0; border-radius:10px; padding:12px; background:#f8fafc;">
                    <summary style="cursor:pointer; color:#0f172a; font-weight:600;">When do I pay for my order?</summary>
                    <p style="color:#334155; margin:8px 0 0;">Payment is made <strong>after delivery</strong>. Once you receive and verify your device, you'll complete the mobile money payment with our delivery agent.</p>
                </details>
                <details style="border:1px solid #e2e8f0; border-radius:10px; padding:12px; background:#f8fafc;">
                    <summary style="cursor:pointer; color:#0f172a; font-weight:600;">Can courses be pre-installed on my device?</summary>
                    <p style="color:#334155; margin:8px 0 0;">Yes! Our <strong>Digital Skilling</strong> and <strong>Entrepreneurship</strong> courses can be pre-installed on any device you purchase. This allows you to access course materials offline. Simply request this during checkout or contact our WhatsApp agent.</p>
                </details>
            </div>
        `;

        modal.appendChild(panel);
        document.body.appendChild(modal);

        const close = () => { modal.style.display = 'none'; document.body.style.overflow = ''; };
        modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
        panel.querySelector('#faqsClose').addEventListener('click', close);
    }

    const open = (e) => {
        if (e) e.preventDefault();
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    links.forEach(link => {
        link.addEventListener('click', open);
        link.setAttribute('href', '#faqs');
        link.setAttribute('aria-controls', 'faqsModal');
        link.setAttribute('role', 'button');
    });
}

// Footer: "Terms & Conditions" modal
function initTermsModal() {
    const links = Array.from(document.querySelectorAll('footer a, .footer a, .site-footer a, p a'))
        .filter(a => /terms\s*&\s*conditions/i.test(a.textContent.trim()));

    if (!links.length) return;

    let modal = document.getElementById('termsModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'termsModal';
        modal.style.position = 'fixed';
        modal.style.inset = '0';
        modal.style.background = 'rgba(0,0,0,0.5)';
        modal.style.display = 'none';
        modal.style.zIndex = '2000';

        const panel = document.createElement('div');
        panel.style.position = 'absolute';
        panel.style.right = '50%';
        panel.style.top = '50%';
        panel.style.transform = 'translate(50%, -50%)';
        panel.style.width = 'min(720px, 92vw)';
        panel.style.maxHeight = '85vh';
        panel.style.overflowY = 'auto';
        panel.style.background = '#ffffff';
        panel.style.borderRadius = '12px';
        panel.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15)';
        panel.style.padding = '24px';

        panel.innerHTML = `
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
                <h2 style="margin:0; font-size:22px; color:#0f172a;">Terms & Conditions</h2>
                <button id="termsClose" aria-label="Close" style="border:none; background:#e5e7eb; color:#0f172a; border-radius:8px; padding:8px 12px; cursor:pointer;">Close</button>
            </div>
            <p style="color:#64748b; margin:0 0 12px;">Please read these terms carefully before using our services.</p>

            <div style="display:grid; grid-template-columns:1fr; gap:14px;">
                <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px; background:#f8fafc;">
                    <h3 style="margin-top:0; color:#0f172a; font-size:16px;">1. Credit Terms</h3>
                    <p style="color:#334155; margin:8px 0 0; font-size:14px;">A 20% deposit is required upfront. The remaining balance is payable over 3-6 months with monthly installments. VAT at 18% applies to all purchases.</p>
                </div>
                <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px; background:#f8fafc;">
                    <h3 style="margin-top:0; color:#0f172a; font-size:16px;">2. Payment Method</h3>
                    <p style="color:#334155; margin:8px 0 0; font-size:14px;">Payment is accepted via Mobile Money after delivery. Our agent will confirm receipt and collect payment upon delivery.</p>
                </div>
                <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px; background:#f8fafc;">
                    <h3 style="margin-top:0; color:#0f172a; font-size:16px;">3. Delivery & Returns</h3>
                    <p style="color:#334155; margin:8px 0 0; font-size:14px;">Delivery timelines vary by location. Products can be returned within 7 days if unused and in original packaging. Contact us via WhatsApp for any delivery or return inquiries.</p>
                </div>
                <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px; background:#f8fafc;">
                    <h3 style="margin-top:0; color:#0f172a; font-size:16px;">4. Device Pre-installation</h3>
                    <p style="color:#334155; margin:8px 0 0; font-size:14px;">Offline courses can be pre-installed on purchased devices at no extra charge. Request this service during checkout or contact our agent.</p>
                </div>
            </div>
        `;

        modal.appendChild(panel);
        document.body.appendChild(modal);

        const close = () => { modal.style.display = 'none'; document.body.style.overflow = ''; };
        modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
        panel.querySelector('#termsClose').addEventListener('click', close);
    }

    const open = (e) => {
        if (e) e.preventDefault();
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    links.forEach(link => {
        link.addEventListener('click', open);
        link.setAttribute('href', '#terms');
        link.setAttribute('aria-controls', 'termsModal');
        link.setAttribute('role', 'button');
    });
}

// Footer: "Privacy Policy" modal
function initPrivacyModal() {
    const links = Array.from(document.querySelectorAll('footer a, .footer a, .site-footer a, p a'))
        .filter(a => /privacy\s*policy/i.test(a.textContent.trim()));

    if (!links.length) return;

    let modal = document.getElementById('privacyModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'privacyModal';
        modal.style.position = 'fixed';
        modal.style.inset = '0';
        modal.style.background = 'rgba(0,0,0,0.5)';
        modal.style.display = 'none';
        modal.style.zIndex = '2000';

        const panel = document.createElement('div');
        panel.style.position = 'absolute';
        panel.style.right = '50%';
        panel.style.top = '50%';
        panel.style.transform = 'translate(50%, -50%)';
        panel.style.width = 'min(720px, 92vw)';
        panel.style.maxHeight = '85vh';
        panel.style.overflowY = 'auto';
        panel.style.background = '#ffffff';
        panel.style.borderRadius = '12px';
        panel.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15)';
        panel.style.padding = '24px';

        panel.innerHTML = `
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
                <h2 style="margin:0; font-size:22px; color:#0f172a;">Privacy Policy</h2>
                <button id="privacyClose" aria-label="Close" style="border:none; background:#e5e7eb; color:#0f172a; border-radius:8px; padding:8px 12px; cursor:pointer;">Close</button>
            </div>
            <p style="color:#64748b; margin:0 0 12px;">Your privacy and data security are important to us.</p>

            <div style="display:grid; grid-template-columns:1fr; gap:14px;">
                <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px; background:#f8fafc;">
                    <h3 style="margin-top:0; color:#0f172a; font-size:16px;">1. Information Collection</h3>
                    <p style="color:#334155; margin:8px 0 0; font-size:14px;">We collect your name, contact information, and payment details only for processing orders and delivering products. Your information is stored securely and never shared with third parties without consent.</p>
                </div>
                <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px; background:#f8fafc;">
                    <h3 style="margin-top:0; color:#0f172a; font-size:16px;">2. Data Usage</h3>
                    <p style="color:#334155; margin:8px 0 0; font-size:14px;">Your data is used exclusively for order fulfillment, customer support, and sending updates about your purchases. We do not use your information for marketing purposes without explicit permission.</p>
                </div>
                <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px; background:#f8fafc;">
                    <h3 style="margin-top:0; color:#0f172a; font-size:16px;">3. Data Security</h3>
                    <p style="color:#334155; margin:8px 0 0; font-size:14px;">We implement industry-standard security measures to protect your personal and financial information. All transactions are processed securely through trusted payment channels.</p>
                </div>
                <div style="border:1px solid #e2e8f0; border-radius:10px; padding:16px; background:#f8fafc;">
                    <h3 style="margin-top:0; color:#0f172a; font-size:16px;">4. Your Rights</h3>
                    <p style="color:#334155; margin:8px 0 0; font-size:14px;">You have the right to access, update, or delete your personal information at any time. Contact us via WhatsApp at +256 700 706809 to exercise these rights.</p>
                </div>
            </div>
        `;

        modal.appendChild(panel);
        document.body.appendChild(modal);

        const close = () => { modal.style.display = 'none'; document.body.style.overflow = ''; };
        modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
        panel.querySelector('#privacyClose').addEventListener('click', close);
    }

    const open = (e) => {
        if (e) e.preventDefault();
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    links.forEach(link => {
        link.addEventListener('click', open);
        link.setAttribute('href', '#privacy');
        link.setAttribute('aria-controls', 'privacyModal');
        link.setAttribute('role', 'button');
    });
}

// ============================================================
// GLOBAL: Password Visibility Toggle
// ============================================================
window.togglePasswordVisibility = function(btn) {
    var input = btn.parentElement.querySelector('input');
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '\u{1F648}';
        btn.setAttribute('aria-label', 'Hide password');
    } else {
        input.type = 'password';
        btn.textContent = '\u{1F441}';
        btn.setAttribute('aria-label', 'Show password');
    }
};

// ============================================================
// GLOBAL: showPaymentChoice - Universal Credit System
// Works for both products and courses
// ============================================================
window.showPaymentChoice = function(options) {
    // options: { productId, quantity, onConfirm(paymentPlan|null), defaultToCredit, defaultAPR }
    //   OR:    { item: {price, title, ...}, quantity, onConfirm(paymentPlan|null) }
    if (document.getElementById('payment-choice-modal')) return;

    // Determine price and credit availability - either from productId lookup or from options.item
    var itemPrice = 0;
    var itemTitle = '';
    var creditAvailable = true;
    if (options.item) {
        itemPrice = options.item.price;
        itemTitle = options.item.title || '';
        creditAvailable = options.item.creditAvailable !== false;
    } else if (options.productId) {
        var product = products.find(function(p) { return p.id === options.productId; });
        if (product) {
            itemPrice = product.price;
            itemTitle = product.title || '';
            creditAvailable = product.creditAvailable !== false;
        }
    }

    var totalAmount = itemPrice * (options.quantity || 1);

    var creditLabel = creditAvailable
        ? '<label style="display:block;padding:8px 0;cursor:pointer;"><input type="radio" name="payMethod" value="credit"> Buy on credit (20% deposit, remainder over months)</label>'
        : '';

    var modal = document.createElement('div');
    modal.id = 'payment-choice-modal';
    modal.innerHTML =
        '<div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:2000;padding:20px;">' +
        '<div style="background:white;padding:24px;border-radius:8px;max-width:500px;width:100%;max-height:80vh;overflow-y:auto;">' +
            '<h3 style="margin:0 0 5px;">Choose Payment Method</h3>' +
            (itemTitle ? '<p style="color:#64748b;font-size:13px;margin:0 0 15px;">' + itemTitle + '</p>' : '') +
            '<div style="margin:10px 0;">' +
                '<label style="display:block;padding:8px 0;cursor:pointer;"><input type="radio" name="payMethod" value="full" checked> Pay in full (UGX ' + totalAmount.toLocaleString() + ')</label>' +
                creditLabel +
            '</div>' +
            '<div id="creditOptions" style="display:none;margin-top:12px;padding:14px;background:#f3f4f6;border-radius:5px;">' +
                '<div style="margin-bottom:12px;">' +
                    '<label style="font-weight:600;font-size:13px;">Payment Period: <select id="creditMonths" style="margin-left:8px;padding:6px;border:1px solid #cbd5e1;border-radius:4px;"><option value="3">3 Months</option><option value="6">6 Months</option></select></label>' +
                '</div>' +
                '<div style="margin-bottom:12px;">' +
                    '<label style="font-weight:600;font-size:13px;">Annual Interest Rate:</label>' +
                    '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:6px;">' +
                        '<label style="cursor:pointer;"><input type="radio" name="interestRate" value="0" checked> 0% (No Interest)</label>' +
                        '<label style="cursor:pointer;"><input type="radio" name="interestRate" value="5"> 5% APR</label>' +
                        '<label style="cursor:pointer;"><input type="radio" name="interestRate" value="9.99"> 9.99% APR</label>' +
                        '<label style="cursor:pointer;"><input type="radio" name="interestRate" value="14.99"> 14.99% APR</label>' +
                    '</div>' +
                '</div>' +
                '<div id="creditSummary" style="margin-top:12px;color:#374151;font-size:13px;background:white;padding:10px;border-radius:3px;border-left:3px solid #2563eb;"></div>' +
            '</div>' +
            '<div style="display:flex;justify-content:flex-end;gap:8px;margin-top:15px;">' +
                '<button id="payCancel" class="btn-secondary" style="padding:8px 16px;">Cancel</button>' +
                '<button id="payConfirm" class="btn-primary" style="padding:8px 16px;">Confirm</button>' +
            '</div>' +
        '</div>' +
        '</div>';
    document.body.appendChild(modal);

    var radios = modal.querySelectorAll("input[name='payMethod']");
    var creditOpts = modal.querySelector('#creditOptions');
    var monthsSelect = modal.querySelector('#creditMonths');
    var interestRadios = modal.querySelectorAll("input[name='interestRate']");
    var creditSummary = modal.querySelector('#creditSummary');

    function updateSummary() {
        var months = Number(monthsSelect.value);
        var interest = Number(modal.querySelector("input[name='interestRate']:checked").value);
        var c = computeCredit(totalAmount, months, interest);

        var interestText = '';
        if (c.totalInterest > 0) {
            interestText = '<br><strong>Total Interest:</strong> UGX ' + c.totalInterest.toFixed(2);
        }

        creditSummary.innerHTML =
            '<strong>Deposit (Due Now):</strong> UGX ' + c.deposit.toFixed(2) + '<br>' +
            '<strong>Monthly Payment:</strong> UGX ' + c.monthly.toFixed(2) + ' x ' + c.months + ' months<br>' +
            '<strong>Total to Finance:</strong> UGX ' + c.remaining.toFixed(2) + interestText;
    }

    radios.forEach(function(r) {
        r.addEventListener('change', function() {
            if (this.value === 'credit') { creditOpts.style.display = 'block'; updateSummary(); }
            else creditOpts.style.display = 'none';
        });
    });

    monthsSelect.addEventListener('change', updateSummary);
    interestRadios.forEach(function(r) { r.addEventListener('change', updateSummary); });

    // If caller requested default to credit
    if (options && options.defaultToCredit) {
        var creditRadio = modal.querySelector("input[name='payMethod'][value='credit']");
        if (creditRadio) {
            creditRadio.checked = true;
            creditOpts.style.display = 'block';
            if (typeof options.defaultAPR !== 'undefined' && options.defaultAPR !== null) {
                var aprRadio = modal.querySelector("input[name='interestRate'][value='" + options.defaultAPR + "']");
                if (aprRadio) aprRadio.checked = true;
            }
            updateSummary();
        }
    }

    modal.querySelector('#payCancel').onclick = function() { modal.remove(); };
    modal.querySelector('#payConfirm').onclick = function() {
        var selected = modal.querySelector("input[name='payMethod']:checked").value;
        var plan = null;
        if (selected === 'credit') {
            plan = {
                type: 'credit',
                months: Number(monthsSelect.value),
                interestRate: Number(modal.querySelector("input[name='interestRate']:checked").value)
            };
        }
        options.onConfirm(plan);
        modal.remove();
    };
};

// ============================================================
// GLOBAL: addCourseToCart - Universal course cart function
// ============================================================
window.addCourseToCart = function(courseId, quantity, paymentPlan, courseDataObj) {
    var cart = getCart();
    var item = cart.find(function(c) { return c.itemId === courseId && c.type === 'course'; });
    if (!item) {
        cart.push({
            itemId: courseId,
            type: 'course',
            quantity: quantity || 1,
            paymentPlan: paymentPlan,
            courseData: courseDataObj
        });
    } else {
        item.quantity = quantity || 1;
        if (paymentPlan) item.paymentPlan = paymentPlan;
    }
    saveCart(cart);
    updateCartCount();
};
