// ============================================================
// Zaga Technologies - Main Application JavaScript
// ============================================================

// Global toast notification
window.showToast = function(message, type) {
    type = type || 'success';
    var container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;max-width:360px;';
        document.body.appendChild(container);
    }
    var colors = { success: '#16a34a', error: '#dc2626', warning: '#d97706', info: '#2563eb' };
    var toastIcons = {
        success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
        error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>'
    };
    var toast = document.createElement('div');
    toast.style.cssText = 'display:flex;align-items:center;gap:10px;padding:12px 16px;background:' + (colors[type] || colors.info) + ';color:white;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);font-size:14px;font-weight:500;animation:toastSlideIn 0.3s ease;cursor:pointer;';
    toast.innerHTML = (toastIcons[type] || toastIcons.info) + '<span style="flex:1;">' + message + '</span>';
    toast.onclick = function() { toast.remove(); };
    container.appendChild(toast);
    setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(function() { toast.remove(); }, 300); }, 4000);
};
var _toastStyle = document.createElement('style');
_toastStyle.textContent = '@keyframes toastSlideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}' +
    '.custom-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:10000;display:flex;align-items:center;justify-content:center;animation:toastSlideIn 0.15s ease}' +
    '.custom-modal{background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.2);max-width:420px;width:90%;padding:28px;text-align:center}' +
    '.custom-modal h3{font-size:18px;font-weight:700;margin-bottom:8px;color:#1e293b}' +
    '.custom-modal p{font-size:14px;color:#64748b;margin-bottom:24px;line-height:1.5}' +
    '.custom-modal-actions{display:flex;gap:10px;justify-content:center}' +
    '.custom-modal-btn{padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;border:1px solid #d1d5db;background:#fff;color:#374151;transition:background 0.15s}' +
    '.custom-modal-btn:hover{background:#f9fafb}' +
    '.custom-modal-btn.primary{background:#2563eb;color:#fff;border-color:#2563eb}' +
    '.custom-modal-btn.primary:hover{background:#1d4ed8}' +
    '.custom-modal-btn.danger{background:#dc2626;color:#fff;border-color:#dc2626}' +
    '.custom-modal-btn.danger:hover{background:#b91c1c}' +
    '.custom-modal input[type=text]{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;margin-bottom:20px;box-sizing:border-box}';
document.head.appendChild(_toastStyle);

// Custom confirm modal (replaces browser confirm())
window.showAppConfirm = function(title, message, confirmText, onConfirm, danger) {
    var overlay = document.createElement('div');
    overlay.className = 'custom-modal-overlay';
    var btnClass = danger ? 'danger' : 'primary';
    overlay.innerHTML = '<div class="custom-modal">' +
        '<h3>' + title + '</h3>' +
        '<p>' + message + '</p>' +
        '<div class="custom-modal-actions">' +
            '<button class="custom-modal-btn" id="cmCancel">Cancel</button>' +
            '<button class="custom-modal-btn ' + btnClass + '" id="cmConfirm">' + (confirmText || 'Confirm') + '</button>' +
        '</div></div>';
    document.body.appendChild(overlay);
    overlay.querySelector('#cmCancel').onclick = function() { overlay.remove(); };
    overlay.querySelector('#cmConfirm').onclick = function() { overlay.remove(); onConfirm(); };
    overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
};

// Custom prompt modal (replaces browser prompt())
window.showAppPrompt = function(title, message, defaultVal, onSubmit) {
    var overlay = document.createElement('div');
    overlay.className = 'custom-modal-overlay';
    overlay.innerHTML = '<div class="custom-modal">' +
        '<h3>' + title + '</h3>' +
        '<p>' + message + '</p>' +
        '<input type="text" id="cmInput" value="' + (defaultVal || '') + '">' +
        '<div class="custom-modal-actions">' +
            '<button class="custom-modal-btn" id="cmCancel">Cancel</button>' +
            '<button class="custom-modal-btn primary" id="cmConfirm">Save</button>' +
        '</div></div>';
    document.body.appendChild(overlay);
    var input = overlay.querySelector('#cmInput');
    input.focus();
    input.select();
    overlay.querySelector('#cmCancel').onclick = function() { overlay.remove(); };
    overlay.querySelector('#cmConfirm').onclick = function() { var v = input.value.trim(); overlay.remove(); if (v) onSubmit(v); };
    input.addEventListener('keydown', function(e) { if (e.key === 'Enter') { var v = input.value.trim(); overlay.remove(); if (v) onSubmit(v); } });
    overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
};

(function () {
    'use strict';

    var SITE_URL = window.SITE_URL || '';
    var CART_KEY = 'cart';
    var USER_KEY = 'zagatech_current_user';

    // ============================================================
    // Data Loading: Products & Courses from API
    // ============================================================

    window.products = [];
    window.courses = [];
    window._productsLoaded = false;
    window._coursesLoaded = false;

    var PRODUCTS_CACHE_KEY = 'zaga_products_v2';
    var PRODUCTS_CACHE_TTL = 3 * 60 * 1000; // 3 minutes

    window.clearProductsCache = function () {
        try { localStorage.removeItem(PRODUCTS_CACHE_KEY); } catch (e) {}
    };

    window.loadProductsFromDB = function () {
        return fetch(SITE_URL + '/api/products.php?action=list', { cache: 'no-store' })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                if (!data.success || !data.data) throw new Error('Bad response');
                window.products = data.data.map(function (p) {
                    p.inStock = p.in_stock == 1;
                    p.additionalImages = (p.additional_images || []).map(function (img) {
                        return (img && img.indexOf('http') !== 0 && img.indexOf('/') !== 0) ? SITE_URL + '/' + img : img;
                    });
                    if (p.image && p.image.indexOf('http') !== 0 && p.image.indexOf('/') !== 0) {
                        p.image = SITE_URL + '/' + p.image;
                    }
                    return p;
                });
                try {
                    localStorage.setItem(PRODUCTS_CACHE_KEY, JSON.stringify({ ts: Date.now(), data: window.products }));
                } catch (e) {}
                window._productsLoaded = true;
                window.dispatchEvent(new CustomEvent('products-loaded'));
            })
            .catch(function (err) {
                console.warn('Products API unavailable, checking cache:', err);
                try {
                    var raw = localStorage.getItem(PRODUCTS_CACHE_KEY);
                    if (raw) {
                        var cached = JSON.parse(raw);
                        if (cached && cached.data && (Date.now() - cached.ts) < PRODUCTS_CACHE_TTL) {
                            window.products = cached.data;
                            console.info('Serving products from unexpired cache (' + cached.data.length + ' items).');
                        } else {
                            localStorage.removeItem(PRODUCTS_CACHE_KEY);
                            window.products = [];
                            console.warn('Cache expired — products could not be loaded. Is the server running?');
                        }
                    } else {
                        window.products = [];
                    }
                } catch (e) {
                    window.products = [];
                }
                window._productsLoaded = true;
                window.dispatchEvent(new CustomEvent('products-loaded'));
            });
    };

    /**
     * Load courses from the PHP/MySQL API and cache in window.courses.
     */
    window.loadCoursesFromDB = function () {
        return fetch(SITE_URL + '/api/courses.php?action=list')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success && data.data) {
                    window.courses = data.data.map(function (c) {
                        c.type = 'course';
                        c.inStock = c.in_stock == 1;
                        c.creditAvailable = c.credit_available == 1;
                        c.defaultAPR = c.default_apr || 0;
                        if (c.image && c.image.indexOf('http') !== 0 && c.image.indexOf('/') !== 0) {
                            c.image = SITE_URL + '/' + c.image;
                        }
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

    // Start loading data immediately
    loadProductsFromDB();
    loadCoursesFromDB();

    // ============================================================
    // Utility Functions
    // ============================================================

    /**
     * Escape HTML special characters to prevent XSS.
     */
    window.escapeHtml = function (str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    /**
     * Format a price with UGX prefix and comma separators.
     */
    window.formatPrice = function (price) {
        return 'UGX ' + Number(price).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };

    /**
     * Return star characters for a given rating (0-5).
     */
    window.getStars = function (rating) {
        var full = Math.floor(rating);
        var half = rating - full >= 0.5;
        var stars = '';
        for (var i = 0; i < full; i++) stars += '\u2605';
        if (half) stars += '\u2606';
        while (stars.length < 5) stars += '\u2606';
        return stars;
    };

    // ============================================================
    // Cart Functions
    // ============================================================

    /**
     * Get the current cart array from localStorage.
     */
    window.getCart = function () {
        try {
            var raw = localStorage.getItem(CART_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            console.error('Failed to read cart', e);
            return [];
        }
    };

    /**
     * Save the cart array to localStorage, update badge, and sync to DB for logged-in users.
     */
    window.saveCart = function (cart) {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
        updateCartCount();
        // Sync to DB for logged-in users
        syncCartToDB(cart);
    };

    var _cartSyncTimer = null;
    function syncCartToDB(cart) {
        // Debounce to avoid excessive API calls
        clearTimeout(_cartSyncTimer);
        _cartSyncTimer = setTimeout(function () {
            var fd = new FormData();
            fd.append('action', 'save_cart');
            fd.append('cart_json', JSON.stringify(cart));
            fetch(SITE_URL + '/api/customers.php', { method: 'POST', body: fd }).catch(function () {});
        }, 500);
    }

    /**
     * Load cart from DB on page load (merges with localStorage).
     */
    function loadCartFromDB() {
        fetch(SITE_URL + '/api/customers.php?action=load_cart')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success && data.cart && Array.isArray(data.cart) && data.cart.length > 0) {
                    var localCart = getCart();
                    // If local cart is empty, use DB cart. If both have items, merge.
                    if (localCart.length === 0) {
                        localStorage.setItem(CART_KEY, JSON.stringify(data.cart));
                        updateCartCount();
                    }
                }
            })
            .catch(function () {});
    }

    /**
     * Add a product to the cart.
     * @param {number} productId
     * @param {number} qty
     * @param {object|null} paymentPlan  e.g. { type: 'credit', months: 3, interestRate: 0 }
     */
    window.addToCart = function (productId, qty, paymentPlan, replace) {
        qty = Number(qty) || 1;
        var prodId = Number(productId);
        var cart = getCart();
        var item = cart.find(function (c) { return c.productId === prodId; });
        var product = products.find(function (p) { return p.id === prodId; });

        if (!product) {
            showToast('Product not found', 'error');
            return;
        }

        if (!item) {
            cart.push({ productId: prodId, quantity: qty, paymentPlan: paymentPlan || null });
        } else {
            item.quantity = replace ? qty : item.quantity + qty;
            if (paymentPlan) item.paymentPlan = paymentPlan;
        }
        saveCart(cart);
    };

    /**
     * Add a course to the cart.
     */
    window.addCourseToCart = function (courseId, quantity, paymentPlan, courseDataObj) {
        var cart = getCart();
        var item = cart.find(function (c) { return c.itemId === courseId && c.type === 'course'; });
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
    };

    /**
     * Remove an item from the cart by ID and type.
     */
    window.removeFromCart = function (itemId, itemType) {
        itemType = itemType || 'product';
        var cart = getCart();
        if (itemType === 'course') {
            cart = cart.filter(function (c) { return !(c.itemId === itemId && c.type === 'course'); });
        } else {
            var prodId = Number(itemId);
            cart = cart.filter(function (c) { return c.productId !== prodId; });
        }
        saveCart(cart);
        if (typeof displayCart === 'function') displayCart();
        if (typeof displayCheckoutSummary === 'function') displayCheckoutSummary();
    };

    /**
     * Update quantity of a cart item. Removes if qty <= 0.
     */
    window.updateQuantity = function (itemId, newQuantity, itemType) {
        itemType = itemType || 'product';
        var qty = Number(newQuantity);
        var cart = getCart();
        var item;

        if (itemType === 'course') {
            item = cart.find(function (c) { return c.itemId === itemId && c.type === 'course'; });
        } else {
            var prodId = Number(itemId);
            item = cart.find(function (c) { return c.productId === prodId; });
        }

        if (item) {
            if (qty <= 0) {
                if (itemType === 'course') {
                    cart = cart.filter(function (c) { return !(c.itemId === itemId && c.type === 'course'); });
                } else {
                    var pid = Number(itemId);
                    cart = cart.filter(function (c) { return c.productId !== pid; });
                }
            } else {
                item.quantity = qty;
            }
            saveCart(cart);
            if (typeof displayCart === 'function') displayCart();
            if (typeof displayCheckoutSummary === 'function') displayCheckoutSummary();
        }
    };

    /**
     * Update the cart count badge in the navbar.
     */
    window.updateCartCount = function () {
        var cart = getCart();
        var count = cart.reduce(function (s, i) { return s + (i.quantity || 0); }, 0);
        var elements = document.querySelectorAll('#cartCount');
        elements.forEach(function (el) {
            el.textContent = count;
            if (count > 0) {
                el.classList.add('has-items');
            } else {
                el.classList.remove('has-items');
            }
        });
    };

    /**
     * Validate cart: remove items whose product no longer exists in loaded data.
     */
    window.validateCart = function () {
        if (!window.products || window.products.length === 0) return;
        var cart = getCart();
        var validCart = cart.filter(function (item) {
            if (item.type === 'course') return true;
            return window.products.some(function (p) { return p.id === item.productId; });
        });
        if (validCart.length !== cart.length) {
            saveCart(validCart);
        } else {
            updateCartCount();
        }
    };

    window.addEventListener('products-loaded', function () {
        validateCart();
    });

    // Sync cart count across tabs
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

    // ============================================================
    // Credit / BNPL Computation
    // ============================================================

    /**
     * Compute credit payment schedule.
     * @param {number} amount           Total item price
     * @param {number} months           Number of installments (3 or 6)
     * @param {number} annualInterestRate  Annual percentage rate (default 0)
     * @returns {object} { amount, deposit, remaining, months, monthly, totalInterest, schedule, annualInterestRate }
     */
    // Round to nearest 500 (up)
    function roundTo500(n) {
        return Math.ceil(n / 500) * 500;
    }

    window.computeCredit = function (amount, months, annualInterestRate) {
        months = Number(months) || 3;
        amount = Number(amount);
        annualInterestRate = Number(annualInterestRate) || 0;

        var deposit = roundTo500(amount * 0.2);
        var principal = amount - deposit;

        var schedule = [];
        var monthlyPayment = 0;
        var totalInterest = 0;

        if (annualInterestRate > 0) {
            var monthlyRate = annualInterestRate / 100 / 12;
            var factor = Math.pow(1 + monthlyRate, months);
            var rawMonthly = principal * (monthlyRate * factor) / (factor - 1);
            monthlyPayment = roundTo500(rawMonthly);

            // Build schedule — last payment absorbs rounding difference
            var balance = principal;
            var totalPrincipalPaid = 0;
            for (var m = 0; m < months; m++) {
                var interestPayment = Math.round(balance * monthlyRate);
                var payment = (m === months - 1) ? balance + interestPayment : monthlyPayment;
                var principalPayment = payment - interestPayment;
                balance = Math.max(0, balance - principalPayment);
                totalInterest += interestPayment;

                schedule.push({
                    month: m + 1,
                    payment: payment,
                    principal: principalPayment,
                    interest: interestPayment,
                    balance: balance
                });
            }
        } else {
            // No interest — round monthly up to nearest 500, last payment takes remainder
            var rawMonthly2 = principal / months;
            monthlyPayment = roundTo500(rawMonthly2);
            var remaining = principal;

            for (var n = 0; n < months; n++) {
                var payment2 = (n === months - 1) ? remaining : Math.min(monthlyPayment, remaining);
                remaining = Math.max(0, remaining - payment2);

                schedule.push({
                    month: n + 1,
                    payment: payment2,
                    principal: payment2,
                    interest: 0,
                    balance: remaining
                });
            }
        }

        return {
            amount: amount,
            deposit: deposit,
            remaining: principal,
            months: months,
            monthly: monthlyPayment,
            totalInterest: totalInterest,
            schedule: schedule,
            annualInterestRate: annualInterestRate
        };
    };

    // ============================================================
    // Navigation Helpers
    // ============================================================

    window.viewProduct = function (id) {
        sessionStorage.setItem('productId', id);
        window.location.href = SITE_URL + '/product-detail';
    };

    window.viewProductDetail = function (id) {
        sessionStorage.setItem('productId', id);
        window.location.href = SITE_URL + '/product-detail';
    };

    // ============================================================
    // Pagination Helpers
    // ============================================================

    /**
     * Paginate an array.
     */
    window.paginate = function (array, page, perPage) {
        page = page || 1;
        perPage = perPage || 12;
        var total = array.length;
        var totalPages = Math.max(1, Math.ceil(total / perPage));
        var current = Math.min(Math.max(1, page), totalPages);
        var start = (current - 1) * perPage;
        var end = start + perPage;
        return {
            pageItems: array.slice(start, end),
            totalPages: totalPages,
            currentPage: current,
            perPage: perPage,
            totalItems: total
        };
    };

    /**
     * Render pagination buttons into a container.
     */
    window.renderPaginationControls = function (containerId, currentPage, totalPages) {
        var container = document.getElementById(containerId);
        if (!container) return;
        var html = '';
        html += '<button ' + (currentPage === 1 ? 'disabled' : '') + ' class="pag-btn" data-page="' + (currentPage - 1) + '">Prev</button>';
        var start = Math.max(1, currentPage - 3);
        var end = Math.min(totalPages, currentPage + 3);
        for (var p = start; p <= end; p++) {
            html += '<button class="pag-btn ' + (p === currentPage ? 'active' : '') + '" data-page="' + p + '">' + p + '</button>';
        }
        html += '<button ' + (currentPage === totalPages ? 'disabled' : '') + ' class="pag-btn" data-page="' + (currentPage + 1) + '">Next</button>';
        container.innerHTML = html;
        container.querySelectorAll('.pag-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var pg = Number(this.getAttribute('data-page'));
                container.dispatchEvent(new CustomEvent('page-change', { detail: { page: pg } }));
            });
        });
    };

    // ============================================================
    // User Auth (Client-side simulation)
    // ============================================================

    window.getCurrentUser = function () {
        try {
            return JSON.parse(localStorage.getItem(USER_KEY));
        } catch (e) {
            return null;
        }
    };

    window.signInUser = function (email, name) {
        var user = { email: email.toLowerCase(), name: name || email.split('@')[0] };
        localStorage.setItem(USER_KEY, JSON.stringify(user));
        renderUserArea();
        return user;
    };

    window.signOutUser = function () {
        localStorage.removeItem(USER_KEY);
        renderUserArea();
    };

    function renderUserArea() {
        var navLinks = document.querySelectorAll('.nav-links');
        navLinks.forEach(function (nav) {
            if (nav.querySelector('.user-area')) return;
            var userArea = document.createElement('div');
            userArea.className = 'user-area';
            userArea.style.display = 'flex';
            userArea.style.gap = '10px';
            userArea.style.alignItems = 'center';

            var user = getCurrentUser();
            if (user) {
                var ordersLink = document.createElement('a');
                ordersLink.href = SITE_URL + '/order-history';
                ordersLink.textContent = 'My Orders';
                ordersLink.className = 'nav-link';

                var signOut = document.createElement('button');
                signOut.className = 'btn-secondary';
                signOut.textContent = 'Sign out';
                signOut.onclick = function () { signOutUser(); };

                var nameEl = document.createElement('span');
                nameEl.textContent = user.name;
                nameEl.style.fontWeight = '600';

                userArea.appendChild(nameEl);
                userArea.appendChild(ordersLink);
                userArea.appendChild(signOut);
            } else {
                var loginBtn = document.createElement('button');
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
        var modal = document.createElement('div');
        modal.id = 'signin-modal';
        modal.innerHTML =
            '<div class="modal-backdrop" style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:2000;">' +
                '<div style="background:white;padding:20px;border-radius:8px;max-width:400px;width:100%;">' +
                    '<h3>Sign in</h3>' +
                    '<p>Enter your email to view orders and checkout faster.</p>' +
                    '<input id="signinEmail" type="email" placeholder="you@example.com" style="width:100%;padding:8px;margin:8px 0;" />' +
                    '<input id="signinName" type="text" placeholder="Display name (optional)" style="width:100%;padding:8px;margin:8px 0;" />' +
                    '<div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px;">' +
                        '<button id="signinCancel" class="btn-secondary">Cancel</button>' +
                        '<button id="signinConfirm" class="btn-primary">Sign in</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        document.body.appendChild(modal);
        document.getElementById('signinCancel').onclick = function () { modal.remove(); };
        document.getElementById('signinConfirm').onclick = function () {
            var email = document.getElementById('signinEmail').value.trim();
            var name = document.getElementById('signinName').value.trim();
            if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
                showToast('Please enter a valid email', 'error');
                return;
            }
            signInUser(email, name || null);
            modal.remove();
        };
    }

    // ============================================================
    // Order Placement (used by checkout)
    // ============================================================

    window.placeOrder = function (orderMeta) {
        var orders = JSON.parse(localStorage.getItem('orders') || '[]');
        var currentUser = getCurrentUser();
        var orderNumber = 'ORD-' + Date.now();
        var fullSchedule = [];
        var orderDate = new Date();

        if (orderMeta.items) {
            orderMeta.items.forEach(function (item) {
                if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                    var product = products.find(function (p) { return p.id === item.productId; });
                    if (product) {
                        var itemTotal = product.price * item.quantity;
                        var interestRate = item.paymentPlan.interestRate || 0;
                        var creditInfo = computeCredit(itemTotal, item.paymentPlan.months, interestRate);

                        creditInfo.schedule.forEach(function (payment) {
                            var dueDate = new Date(orderDate);
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

        var orderData = Object.assign({}, orderMeta, {
            orderNumber: orderNumber,
            date: new Date().toLocaleString(),
            user: currentUser ? currentUser.email : null,
            schedule: fullSchedule,
            paymentsMade: []
        });

        orders.push(orderData);
        localStorage.setItem('orders', JSON.stringify(orders));
        localStorage.removeItem(CART_KEY);
        updateCartCount();
        syncCartToDB([]); // Clear cart in DB too
        return orderData;
    };

    // ============================================================
    // Modal Helpers
    // ============================================================

    /**
     * Open a modal by ID.
     */
    window.openModal = function (id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    };

    /**
     * Close a modal by ID.
     */
    window.closeModal = function (id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    // ============================================================
    // Payment Choice Modal (Universal for products & courses)
    // ============================================================

    window.showPaymentChoice = function (options) {
        if (document.getElementById('payment-choice-modal')) return;

        // Resolve item data and credit config
        var itemPrice = 0;
        var itemTitle = '';
        var creditAvailable = true;
        var defaultAPR = 0;
        var creditTerms = '3,6';

        if (options.item) {
            itemPrice = options.item.price;
            itemTitle = options.item.title || '';
            creditAvailable = options.item.creditAvailable !== false;
            defaultAPR = Number(options.item.defaultAPR || 0);
            creditTerms = options.item.creditTermsMonths || options.item.credit_terms_months || '3,6';
        } else if (options.productId) {
            var product = products.find(function (p) { return p.id === options.productId; });
            if (product) {
                itemPrice = product.price;
                itemTitle = product.title || '';
                creditAvailable = product.creditAvailable !== false;
                defaultAPR = Number(product.defaultAPR || product.default_apr || 0);
                creditTerms = product.creditTermsMonths || product.credit_terms_months || '3,6';
            }
        }

        var totalAmount = itemPrice * (options.quantity || 1);

        // Build month options from admin-configured terms
        var termMonths = creditTerms.split(',').map(function(t) { return parseInt(t.trim()); }).filter(function(t) { return t > 0; });
        if (termMonths.length === 0) termMonths = [3, 6];
        var monthOptionsHtml = termMonths.map(function(m) { return '<option value="' + m + '">' + m + ' Months</option>'; }).join('');

        // Credit label - only show if credit is available AND APR > 0
        var creditEnabled = creditAvailable && defaultAPR > 0;
        var creditLabel = creditEnabled
            ? '<label style="display:block;padding:8px 0;cursor:pointer;"><input type="radio" name="payMethod" value="credit"> Buy on credit — Available on ' + defaultAPR + '%</label>'
            : '';

        var modal = document.createElement('div');
        modal.id = 'payment-choice-modal';
        modal.innerHTML =
            '<div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:2000;padding:20px;">' +
            '<div style="background:white;padding:24px;border-radius:8px;max-width:500px;width:100%;max-height:80vh;overflow-y:auto;">' +
                '<h3 style="margin:0 0 5px;">Choose Payment Method</h3>' +
                (itemTitle ? '<p style="color:#64748b;font-size:13px;margin:0 0 15px;">' + escapeHtml(itemTitle) + '</p>' : '') +
                '<div style="margin:10px 0;">' +
                    '<label style="display:block;padding:8px 0;cursor:pointer;"><input type="radio" name="payMethod" value="full" checked> Pay in full (' + formatPrice(totalAmount) + ')</label>' +
                    creditLabel +
                '</div>' +
                '<div id="creditOptions" style="display:none;margin-top:12px;padding:14px;background:#f3f4f6;border-radius:5px;">' +
                    '<div style="margin-bottom:12px;">' +
                        '<label style="font-weight:600;font-size:13px;">Payment Period: <select id="creditMonths" style="margin-left:8px;padding:6px;border:1px solid #cbd5e1;border-radius:4px;">' + monthOptionsHtml + '</select></label>' +
                    '</div>' +
                    '<p style="font-size:13px;color:#64748b;margin-bottom:12px;">Interest Rate: <strong>Available on ' + defaultAPR + '%</strong></p>' +
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
        var creditSummary = modal.querySelector('#creditSummary');
        var fixedAPR = defaultAPR;

        function updateSummary() {
            var months = Number(monthsSelect.value);
            var c = computeCredit(totalAmount, months, fixedAPR);

            var interestText = '';
            if (c.totalInterest > 0) {
                interestText = '<br><strong>Total Interest:</strong> ' + formatPrice(c.totalInterest);
            }

            creditSummary.innerHTML =
                '<strong>Deposit (20% Due Now):</strong> ' + formatPrice(c.deposit) + '<br>' +
                '<strong>Monthly Payment:</strong> ' + formatPrice(c.monthly) + ' x ' + c.months + ' months<br>' +
                '<strong>Total to Finance:</strong> ' + formatPrice(c.remaining) + interestText;
        }

        radios.forEach(function (r) {
            r.addEventListener('change', function () {
                if (this.value === 'credit') { creditOpts.style.display = 'block'; updateSummary(); }
                else creditOpts.style.display = 'none';
            });
        });

        monthsSelect.addEventListener('change', updateSummary);

        // Default to credit view if requested
        if (options && options.defaultToCredit && creditAvailable) {
            var creditRadio = modal.querySelector("input[name='payMethod'][value='credit']");
            if (creditRadio) {
                creditRadio.checked = true;
                creditOpts.style.display = 'block';
                updateSummary();
            }
        }

        modal.querySelector('#payCancel').onclick = function () { modal.remove(); };
        modal.querySelector('#payConfirm').onclick = function () {
            var selected = modal.querySelector("input[name='payMethod']:checked").value;
            var plan = null;
            if (selected === 'credit') {
                plan = {
                    type: 'credit',
                    months: Number(monthsSelect.value),
                    interestRate: fixedAPR
                };
            }
            options.onConfirm(plan);
            modal.remove();
        };
    };

    // ============================================================
    // Mobile Menu
    // ============================================================

    /**
     * Initialize mobile hamburger menu toggle.
     */
    window.initMobileMenu = function () {
        var menuToggle = document.querySelector('.menu-toggle');
        var navLinks = document.querySelector('.nav-links');

        if (menuToggle && navLinks) {
            menuToggle.addEventListener('click', function () {
                menuToggle.classList.toggle('active');
                navLinks.classList.toggle('active');
                var isOpen = navLinks.classList.contains('active');
                menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                document.body.style.overflow = isOpen ? 'hidden' : '';
            });

            // Close menu when clicking outside
            document.addEventListener('click', function (e) {
                if (navLinks.classList.contains('active') &&
                    !navLinks.contains(e.target) &&
                    !menuToggle.contains(e.target)) {
                    menuToggle.classList.remove('active');
                    navLinks.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                }
            });

            // Close menu when clicking a link
            var links = navLinks.querySelectorAll('.nav-link');
            links.forEach(function (link) {
                link.addEventListener('click', function () {
                    menuToggle.classList.remove('active');
                    navLinks.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                });
            });
        }
    };

    // ============================================================
    // Mobile Search Popup
    // ============================================================

    window.initSearchPopup = function () {
        var toggleBtn = document.getElementById('searchToggle');
        var popup = document.getElementById('searchPopup');
        var backdrop = document.getElementById('searchPopupBackdrop');
        var closeBtn = document.getElementById('searchPopupClose');
        var input = document.getElementById('searchPopupInput');

        if (!toggleBtn || !popup) return;

        function openSearch() {
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            setTimeout(function () { if (input) input.focus(); }, 100);
        }

        function closeSearch() {
            popup.style.display = 'none';
            document.body.style.overflow = '';
        }

        toggleBtn.addEventListener('click', openSearch);
        if (closeBtn) closeBtn.addEventListener('click', closeSearch);
        if (backdrop) backdrop.addEventListener('click', closeSearch);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && popup.style.display === 'flex') {
                closeSearch();
            }
        });
    };

    // ============================================================
    // User Dropdown (for PHP session-based auth in header)
    // ============================================================

    function initUserDropdown() {
        var toggle = document.getElementById('userDropdownToggle');
        var menu = document.getElementById('userDropdownMenu');
        if (!toggle || !menu) return;

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.classList.toggle('open');
        });

        document.addEventListener('click', function (e) {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('open');
            }
        });
    }

    // ============================================================
    // Flash message auto-dismiss
    // ============================================================

    function initFlashMessages() {
        var container = document.getElementById('flashContainer');
        if (!container) return;
        setTimeout(function () {
            container.querySelectorAll('.flash-message').forEach(function (msg) {
                msg.style.opacity = '0';
                msg.style.transform = 'translateY(-10px)';
                setTimeout(function () { msg.remove(); }, 300);
            });
        }, 5000);
    }

    // ============================================================
    // Footer Modals: How It Works, FAQs, Terms, Privacy
    // ============================================================

    function initHowItWorksModal() {
        var links = Array.from(document.querySelectorAll('footer a, .footer a, .site-footer a, p a'))
            .filter(function (a) { return /how it works/i.test(a.textContent.trim()); });
        if (!links.length) return;

        var modal = document.getElementById('howItWorksModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'howItWorksModal';
            modal.className = 'footer-modal';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;z-index:2000;';

            var panel = document.createElement('div');
            panel.style.cssText = 'position:absolute;right:50%;top:50%;transform:translate(50%,-50%);width:min(680px,92vw);max-height:85vh;overflow-y:auto;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.15);padding:24px;';
            panel.innerHTML =
                '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;">' +
                    '<h2 style="margin:0;font-size:22px;color:#0f172a;">How Zaga Tech Credit Works</h2>' +
                    '<button id="howItWorksClose" aria-label="Close" style="border:none;background:#e5e7eb;color:#0f172a;border-radius:8px;padding:8px 12px;cursor:pointer;">Close</button>' +
                '</div>' +
                '<p style="color:#64748b;margin:0 0 12px;">Transparent, flexible financing to help you get the tech you need.</p>' +
                '<div style="display:grid;grid-template-columns:1fr;gap:16px;">' +
                    '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;"><h3 style="margin-top:0;color:#0f172a;">1) Deposit &amp; Approval</h3><ul style="margin:8px 0 0 20px;color:#334155;"><li>Pay an upfront deposit of <strong>20%</strong> of the item price.</li><li>Instant, simulated approval for demonstration purposes.</li><li>VAT is applied at <strong>18%</strong> to the total order.</li></ul></div>' +
                    '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;"><h3 style="margin-top:0;color:#0f172a;">2) Flexible Repayment</h3><ul style="margin:8px 0 0 20px;color:#334155;"><li>Choose a plan between <strong>3</strong> and <strong>6 months</strong>.</li><li>Remaining balance is split into equal monthly installments.</li><li>All schedules are shown clearly at checkout and in your order history.</li></ul></div>' +
                    '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;"><h3 style="margin-top:0;color:#0f172a;">3) What You\'ll See</h3><ul style="margin:8px 0 0 20px;color:#334155;"><li>Deposit amount and monthly payment breakdown.</li><li>VAT (18%) shown separately for transparency.</li><li>Full payment schedule available in <em>Order History</em>.</li></ul></div>' +
                    '<div style="background:#fff4e6;border:1px solid #ffc266;border-radius:10px;padding:16px;"><h3 style="margin-top:0;color:#0f172a;">4) Offline Courses</h3><ul style="margin:8px 0 0 20px;color:#334155;"><li>We offer <strong>Digital Skilling</strong> and <strong>Entrepreneurship</strong> courses for students.</li><li>These courses can be <strong>pre-installed</strong> on any device you purchase from us.</li><li>Access course materials offline anytime, anywhere on your new device.</li><li>Contact us during checkout to request pre-installation of your preferred courses.</li></ul></div>' +
                '</div>';
            modal.appendChild(panel);
            document.body.appendChild(modal);

            var closeFn = function () { modal.style.display = 'none'; document.body.style.overflow = ''; };
            modal.addEventListener('click', function (e) { if (e.target === modal) closeFn(); });
            panel.querySelector('#howItWorksClose').addEventListener('click', closeFn);
        }

        var openFn = function (e) { if (e) e.preventDefault(); modal.style.display = 'block'; document.body.style.overflow = 'hidden'; };
        links.forEach(function (link) { link.addEventListener('click', openFn); link.setAttribute('href', '#how-it-works'); link.setAttribute('role', 'button'); });
    }

    function initFAQsModal() {
        var links = Array.from(document.querySelectorAll('footer a, .footer a, .site-footer a, p a'))
            .filter(function (a) { return /faqs?/i.test(a.textContent.trim()); });
        if (!links.length) return;

        var modal = document.getElementById('faqsModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'faqsModal';
            modal.className = 'footer-modal';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;z-index:2000;';

            var panel = document.createElement('div');
            panel.style.cssText = 'position:absolute;right:50%;top:50%;transform:translate(50%,-50%);width:min(720px,92vw);max-height:85vh;overflow-y:auto;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.15);padding:24px;';
            panel.innerHTML =
                '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;">' +
                    '<h2 style="margin:0;font-size:22px;color:#0f172a;">FAQs - Zaga Tech Credit</h2>' +
                    '<button id="faqsClose" aria-label="Close" style="border:none;background:#e5e7eb;color:#0f172a;border-radius:8px;padding:8px 12px;cursor:pointer;">Close</button>' +
                '</div>' +
                '<p style="color:#64748b;margin:0 0 12px;">Answers to common questions about deposits, VAT, plans, and payments.</p>' +
                '<div style="display:grid;grid-template-columns:1fr;gap:14px;">' +
                    '<details style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;background:#f8fafc;"><summary style="cursor:pointer;color:#0f172a;font-weight:600;">What deposit is required?</summary><p style="color:#334155;margin:8px 0 0;">A 20% deposit of the total item price is required to start your plan.</p></details>' +
                    '<details style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;background:#f8fafc;"><summary style="cursor:pointer;color:#0f172a;font-weight:600;">How is VAT handled?</summary><p style="color:#334155;margin:8px 0 0;">VAT is charged at 18% and displayed clearly on the cart and checkout pages.</p></details>' +
                    '<details style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;background:#f8fafc;"><summary style="cursor:pointer;color:#0f172a;font-weight:600;">What repayment plans are available?</summary><p style="color:#334155;margin:8px 0 0;">You can choose between 3 to 6 monthly installments after the deposit.</p></details>' +
                    '<details style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;background:#f8fafc;"><summary style="cursor:pointer;color:#0f172a;font-weight:600;">Where can I see my payment schedule?</summary><p style="color:#334155;margin:8px 0 0;">Your full schedule is shown at checkout and stored in Order History for future reference.</p></details>' +
                    '<details style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;background:#f8fafc;"><summary style="cursor:pointer;color:#0f172a;font-weight:600;">Is this a real credit approval?</summary><p style="color:#334155;margin:8px 0 0;">This demo simulates approvals client-side. In production, approvals would be handled securely on the server.</p></details>' +
                    '<details style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;background:#f8fafc;"><summary style="cursor:pointer;color:#0f172a;font-weight:600;">What payment methods are accepted?</summary><p style="color:#334155;margin:8px 0 0;">We accept <strong>Mobile Money</strong> payments. Payment is required <strong>after delivery</strong> of your device.</p></details>' +
                    '<details style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;background:#f8fafc;"><summary style="cursor:pointer;color:#0f172a;font-weight:600;">When do I pay for my order?</summary><p style="color:#334155;margin:8px 0 0;">Payment is made <strong>after delivery</strong>. Once you receive and verify your device, you\'ll complete the mobile money payment with our delivery agent.</p></details>' +
                    '<details style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;background:#f8fafc;"><summary style="cursor:pointer;color:#0f172a;font-weight:600;">Can courses be pre-installed on my device?</summary><p style="color:#334155;margin:8px 0 0;">Yes! Our <strong>Digital Skilling</strong> and <strong>Entrepreneurship</strong> courses can be pre-installed on any device you purchase.</p></details>' +
                '</div>';
            modal.appendChild(panel);
            document.body.appendChild(modal);

            var closeFn = function () { modal.style.display = 'none'; document.body.style.overflow = ''; };
            modal.addEventListener('click', function (e) { if (e.target === modal) closeFn(); });
            panel.querySelector('#faqsClose').addEventListener('click', closeFn);
        }

        var openFn = function (e) { if (e) e.preventDefault(); modal.style.display = 'block'; document.body.style.overflow = 'hidden'; };
        links.forEach(function (link) { link.addEventListener('click', openFn); link.setAttribute('href', '#faqs'); link.setAttribute('role', 'button'); });
    }

    function initTermsModal() {
        var links = Array.from(document.querySelectorAll('footer a, .footer a, .site-footer a, p a'))
            .filter(function (a) { return /terms\s*&\s*conditions/i.test(a.textContent.trim()); });
        if (!links.length) return;

        var modal = document.getElementById('termsModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'termsModal';
            modal.className = 'footer-modal';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;z-index:2000;';

            var panel = document.createElement('div');
            panel.style.cssText = 'position:absolute;right:50%;top:50%;transform:translate(50%,-50%);width:min(720px,92vw);max-height:85vh;overflow-y:auto;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.15);padding:24px;';
            panel.innerHTML =
                '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;">' +
                    '<h2 style="margin:0;font-size:22px;color:#0f172a;">Terms &amp; Conditions</h2>' +
                    '<button id="termsClose" aria-label="Close" style="border:none;background:#e5e7eb;color:#0f172a;border-radius:8px;padding:8px 12px;cursor:pointer;">Close</button>' +
                '</div>' +
                '<p style="color:#64748b;margin:0 0 12px;">Please read these terms carefully before using our services.</p>' +
                '<div style="display:grid;grid-template-columns:1fr;gap:14px;">' +
                    '<div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px;background:#f8fafc;"><h3 style="margin-top:0;color:#0f172a;font-size:16px;">1. Credit Terms</h3><p style="color:#334155;margin:8px 0 0;font-size:14px;">A 20% deposit is required upfront. The remaining balance is payable over 3-6 months with monthly installments. VAT at 18% applies to all purchases.</p></div>' +
                    '<div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px;background:#f8fafc;"><h3 style="margin-top:0;color:#0f172a;font-size:16px;">2. Payment Method</h3><p style="color:#334155;margin:8px 0 0;font-size:14px;">Payment is accepted via Mobile Money after delivery. Our agent will confirm receipt and collect payment upon delivery.</p></div>' +
                    '<div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px;background:#f8fafc;"><h3 style="margin-top:0;color:#0f172a;font-size:16px;">3. Delivery &amp; Returns</h3><p style="color:#334155;margin:8px 0 0;font-size:14px;">Delivery timelines vary by location. Products can be returned within 7 days if unused and in original packaging.</p></div>' +
                    '<div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px;background:#f8fafc;"><h3 style="margin-top:0;color:#0f172a;font-size:16px;">4. Device Pre-installation</h3><p style="color:#334155;margin:8px 0 0;font-size:14px;">Offline courses can be pre-installed on purchased devices at no extra charge. Request this service during checkout or contact our agent.</p></div>' +
                '</div>';
            modal.appendChild(panel);
            document.body.appendChild(modal);

            var closeFn = function () { modal.style.display = 'none'; document.body.style.overflow = ''; };
            modal.addEventListener('click', function (e) { if (e.target === modal) closeFn(); });
            panel.querySelector('#termsClose').addEventListener('click', closeFn);
        }

        var openFn = function (e) { if (e) e.preventDefault(); modal.style.display = 'block'; document.body.style.overflow = 'hidden'; };
        links.forEach(function (link) { link.addEventListener('click', openFn); link.setAttribute('href', '#terms'); link.setAttribute('role', 'button'); });
    }

    function initPrivacyModal() {
        var links = Array.from(document.querySelectorAll('footer a, .footer a, .site-footer a, p a'))
            .filter(function (a) { return /privacy\s*policy/i.test(a.textContent.trim()); });
        if (!links.length) return;

        var modal = document.getElementById('privacyModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'privacyModal';
            modal.className = 'footer-modal';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;z-index:2000;';

            var panel = document.createElement('div');
            panel.style.cssText = 'position:absolute;right:50%;top:50%;transform:translate(50%,-50%);width:min(720px,92vw);max-height:85vh;overflow-y:auto;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.15);padding:24px;';
            panel.innerHTML =
                '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;">' +
                    '<h2 style="margin:0;font-size:22px;color:#0f172a;">Privacy Policy</h2>' +
                    '<button id="privacyClose" aria-label="Close" style="border:none;background:#e5e7eb;color:#0f172a;border-radius:8px;padding:8px 12px;cursor:pointer;">Close</button>' +
                '</div>' +
                '<p style="color:#64748b;margin:0 0 12px;">Your privacy and data security are important to us.</p>' +
                '<div style="display:grid;grid-template-columns:1fr;gap:14px;">' +
                    '<div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px;background:#f8fafc;"><h3 style="margin-top:0;color:#0f172a;font-size:16px;">1. Information Collection</h3><p style="color:#334155;margin:8px 0 0;font-size:14px;">We collect your name, contact information, and payment details only for processing orders and delivering products.</p></div>' +
                    '<div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px;background:#f8fafc;"><h3 style="margin-top:0;color:#0f172a;font-size:16px;">2. Data Usage</h3><p style="color:#334155;margin:8px 0 0;font-size:14px;">Your data is used exclusively for order fulfillment, customer support, and sending updates about your purchases.</p></div>' +
                    '<div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px;background:#f8fafc;"><h3 style="margin-top:0;color:#0f172a;font-size:16px;">3. Data Security</h3><p style="color:#334155;margin:8px 0 0;font-size:14px;">We implement industry-standard security measures to protect your personal and financial information.</p></div>' +
                    '<div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px;background:#f8fafc;"><h3 style="margin-top:0;color:#0f172a;font-size:16px;">4. Your Rights</h3><p style="color:#334155;margin:8px 0 0;font-size:14px;">You have the right to access, update, or delete your personal information at any time. Contact us via WhatsApp at +256 700 706809.</p></div>' +
                '</div>';
            modal.appendChild(panel);
            document.body.appendChild(modal);

            var closeFn = function () { modal.style.display = 'none'; document.body.style.overflow = ''; };
            modal.addEventListener('click', function (e) { if (e.target === modal) closeFn(); });
            panel.querySelector('#privacyClose').addEventListener('click', closeFn);
        }

        var openFn = function (e) { if (e) e.preventDefault(); modal.style.display = 'block'; document.body.style.overflow = 'hidden'; };
        links.forEach(function (link) { link.addEventListener('click', openFn); link.setAttribute('href', '#privacy'); link.setAttribute('role', 'button'); });
    }

    // ============================================================
    // Password Visibility Toggle
    // ============================================================

    window.togglePasswordVisibility = function (btn) {
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
    // Product Generation Fallback
    // ============================================================

    function generateProducts(count) {
        var categories = ['Laptops', 'Desktops', 'Tablets', 'Accessories', 'Peripherals', 'Storage'];
        var brands = ['Astra', 'Orion', 'Zephyr', 'Nimbus', 'Vertex', 'Photon'];
        var laptopModels = ['Air', 'Pro', 'Slim', 'Max', 'Studio'];
        var desktopModels = ['Ranger', 'Titan', 'Core', 'Quantum'];
        var tabletModels = ['Tab', 'Note', 'Pad', 'Slate'];
        var accessoryNames = ['Headset', 'Charger', 'Dock', 'Case', 'Adapter', 'Power Bank'];
        var peripheralNames = ['Keyboard', 'Mouse', 'Monitor', 'Webcam', 'Microphone'];
        var storageNames = ['SSD', 'HDD', 'NVMe', 'Portable SSD'];

        var result = [];
        var idCounter = 1;

        function localPic(id) {
            var idx = ((id - 1) % 12) + 1;
            return './images/product-' + idx + '.svg';
        }

        for (var i = 0; i < count; i++) {
            var category = categories[i % categories.length];
            var title = '';
            var basePrice = 99 + Math.round(Math.random() * 2900);
            var rating = Number((3 + Math.random() * 2).toFixed(1));
            var reviews = Math.floor(Math.random() * 1200);
            var description = '';
            var features = [];
            var sku = 'TS-' + String(idCounter).padStart(5, '0');
            var warranty = (1 + (idCounter % 3)) + ' Year' + ((idCounter % 3) === 1 ? '' : 's');
            var inStock = Math.random() > 0.05;
            var stock = inStock ? Math.floor(5 + Math.random() * 195) : 0;

            if (category === 'Laptops') {
                var brand = brands[idCounter % brands.length];
                var model = laptopModels[idCounter % laptopModels.length];
                title = brand + ' ' + model + ' ' + (2020 + (idCounter % 6));
                basePrice = 499 + Math.round(Math.random() * 2500);
                description = 'The ' + title + ' is a powerful, portable laptop featuring modern processors, vivid display, and long battery life.';
                features = ['Intel/AMD latest-gen CPU', '8-32GB RAM options', 'Fast NVMe storage', 'Backlit keyboard', 'Wi-Fi 6'];
            } else if (category === 'Desktops') {
                title = brands[idCounter % brands.length] + ' ' + desktopModels[idCounter % desktopModels.length] + ' Desktop ' + String(idCounter).slice(-3);
                basePrice = 399 + Math.round(Math.random() * 3000);
                description = 'A high-performance desktop built for gaming, creativity and business.';
                features = ['High-performance CPU', 'Discrete GPU options', 'Large cooling system', 'Multiple storage bays', 'Upgradeable RAM'];
            } else if (category === 'Tablets') {
                title = brands[idCounter % brands.length] + ' ' + tabletModels[idCounter % tabletModels.length] + ' ' + (idCounter % 4 === 0 ? 'Plus' : 'Mini');
                basePrice = 199 + Math.round(Math.random() * 1200);
                description = 'A sleek tablet for media, reading, and light productivity.';
                features = ['High-resolution touch display', 'Stylus support', 'Wi-Fi & LTE options', 'Long battery life'];
            } else if (category === 'Accessories') {
                var aName = accessoryNames[idCounter % accessoryNames.length];
                title = 'Universal ' + aName + ' by ' + brands[idCounter % brands.length];
                basePrice = 9 + Math.round(Math.random() * 190);
                description = 'Premium ' + aName.toLowerCase() + ' compatible with most devices.';
                features = ['Durable build', 'Warranty included', 'Universal compatibility'];
            } else if (category === 'Peripherals') {
                var pName = peripheralNames[idCounter % peripheralNames.length];
                title = brands[idCounter % brands.length] + ' ' + pName + (idCounter % 3 === 0 ? ' X' : '');
                basePrice = 19 + Math.round(Math.random() * 500);
                description = 'Reliable ' + pName.toLowerCase() + ' for daily use.';
                features = ['Comfort-focused design', 'Plug & play', 'Multi-device pairing'];
            } else if (category === 'Storage') {
                var sName = storageNames[idCounter % storageNames.length];
                title = brands[idCounter % brands.length] + ' ' + sName + ' ' + ((idCounter % 5) * 128) + 'GB';
                basePrice = 29 + Math.round(Math.random() * 600);
                description = 'Fast and reliable storage for your important files, media, and backups.';
                features = ['High endurance', 'Fast read/write speeds', 'Compact design'];
            }

            var discount = null;
            var originalPrice = null;
            if (Math.random() > 0.8) {
                var pct = [10, 15, 20, 25][Math.floor(Math.random() * 4)];
                discount = pct;
                originalPrice = basePrice;
                basePrice = Math.round(basePrice * (1 - pct / 100));
            }

            result.push({
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
                additionalImages: [localPic(idCounter), localPic(idCounter + 1)]
            });
            idCounter++;
        }
        return result;
    }

    window.__regenerateDefaultProducts = function (count) {
        count = count || 120;
        window.products = generateProducts(count);
        localStorage.setItem('products', JSON.stringify(window.products));
        updateCartCount();
        return window.products;
    };

    // ============================================================
    // Initialization on DOM Ready
    // ============================================================

    function initApp() {
        loadCartFromDB();
        updateCartCount();
        initMobileMenu();
        initSearchPopup();
        initUserDropdown();
        initFlashMessages();
        initHowItWorksModal();
        initFAQsModal();
        initTermsModal();
        initPrivacyModal();

        // Hide admin link if not authenticated (client-side check)
        try {
            var authed = localStorage.getItem('admin_auth') === '1';
            if (!authed) {
                document.querySelectorAll('.admin-link').forEach(function (el) {
                    el.style.display = 'none';
                });
            }
        } catch (e) {
            // Silently ignore
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
    } else {
        initApp();
    }

})();
