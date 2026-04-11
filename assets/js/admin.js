// ============================================================
// ADMIN PANEL - Zaga Technologies
// Centralized JavaScript for all admin CRUD operations
// ============================================================

if (typeof window.SITE_URL === 'undefined') window.SITE_URL = '/Zaga';

// Ensure all fetch calls include session cookies
(function() {
    const _origFetch = window.fetch.bind(window);
    window.fetch = function(url, opts) {
        opts = Object.assign({}, opts || {});
        if (!opts.credentials) opts.credentials = 'same-origin';
        return _origFetch(url, opts);
    };
})();

const API = {
    auth: SITE_URL + '/api/auth.php',
    products: SITE_URL + '/api/products.php',
    categories: SITE_URL + '/api/categories.php',
    courses: SITE_URL + '/api/courses.php',
    dashboard: SITE_URL + '/api/dashboard.php',
    orders: SITE_URL + '/api/orders.php',
    customers: SITE_URL + '/api/customers.php',
    reviews: SITE_URL + '/api/reviews.php',
    testimonials: SITE_URL + '/api/testimonials.php'
};

// ============================================================
// DATA STORES
// ============================================================
let allProducts = [], allCategories = [], allDigitalCourses = [], allEntreprenCourses = [];
let allOrders = [], allCustomers = [], allReviews = [], allTestimonials = [];


// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Escape HTML to prevent XSS attacks.
 */
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}

/**
 * Format a number as price with commas, no decimals.
 */
function formatPrice(num) {
    if (num === null || num === undefined || isNaN(num)) return '0';
    return Math.round(Number(num)).toLocaleString('en-US');
}

/**
 * Round to nearest 500 (ceiling).
 */
function roundTo500(n) {
    return Math.ceil(n / 500) * 500;
}

/**
 * Compute credit payment schedule.
 * @param {number} amount - Total item price
 * @param {number} months - Number of installments
 * @param {number} apr - Annual percentage rate (default 0)
 * @param {number|null} depositAmount - Custom deposit (null = use 20% default)
 */
function computeCredit(amount, months, apr, depositAmount) {
    months = Number(months) || 3;
    amount = Number(amount);
    apr = Number(apr) || 0;

    var deposit = (depositAmount !== null && depositAmount !== undefined)
        ? Number(depositAmount)
        : roundTo500(amount * 0.2);
    var principal = Math.max(0, amount - deposit);

    var schedule = [];
    var monthlyPayment = 0;
    var totalInterest = 0;

    if (apr > 0 && principal > 0) {
        var monthlyRate = apr / 100 / 12;
        var factor = Math.pow(1 + monthlyRate, months);
        var rawMonthly = principal * (monthlyRate * factor) / (factor - 1);
        monthlyPayment = roundTo500(rawMonthly);

        var balance = principal;
        for (var m = 0; m < months; m++) {
            var interest = Math.round(balance * monthlyRate);
            var payment = (m === months - 1) ? balance + interest : monthlyPayment;
            var princ = payment - interest;
            balance = Math.max(0, balance - princ);
            totalInterest += interest;
            schedule.push({ month: m + 1, payment: payment, principal: princ, interest: interest, balance: balance });
        }
    } else if (principal > 0) {
        var rawMonthly2 = principal / months;
        monthlyPayment = roundTo500(rawMonthly2);
        var remaining = principal;
        for (var n = 0; n < months; n++) {
            var pmt = (n === months - 1) ? remaining : Math.min(monthlyPayment, remaining);
            remaining = Math.max(0, remaining - pmt);
            schedule.push({ month: n + 1, payment: pmt, principal: pmt, interest: 0, balance: remaining });
        }
    }

    return {
        amount: amount, deposit: deposit, remaining: principal, months: months,
        monthly: monthlyPayment, totalInterest: totalInterest, schedule: schedule,
        annualInterestRate: apr
    };
}

/**
 * Show a toast notification that auto-dismisses after 3 seconds.
 */
function showToast(msg, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'admin-toast admin-toast-' + type;
    toast.textContent = msg;

    // Styling
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '-400px',
        minWidth: '280px',
        maxWidth: '420px',
        padding: '14px 22px',
        borderRadius: '8px',
        color: '#fff',
        fontSize: '14px',
        fontWeight: '500',
        zIndex: '100000',
        boxShadow: '0 4px 20px rgba(0,0,0,0.25)',
        transition: 'right 0.35s ease',
        lineHeight: '1.4'
    });

    if (type === 'success') {
        toast.style.background = '#16a34a';
    } else if (type === 'error') {
        toast.style.background = '#dc2626';
    } else if (type === 'warning') {
        toast.style.background = '#d97706';
    } else {
        toast.style.background = '#2563eb';
    }

    document.body.appendChild(toast);

    // Slide in
    requestAnimationFrame(() => {
        toast.style.right = '20px';
    });

    // Auto dismiss
    setTimeout(() => {
        toast.style.right = '-400px';
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

/**
 * Remove a modal by its id.
 */
function removeModal(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

/**
 * Create a reusable modal with overlay, header, and body.
 * Returns the modal element.
 */
function createModal(id, title, bodyHtml, maxWidth = '800px') {
    // Remove existing modal with same id
    removeModal(id);

    const overlay = document.createElement('div');
    overlay.id = id;
    Object.assign(overlay.style, {
        position: 'fixed',
        top: '0',
        left: '0',
        width: '100%',
        height: '100%',
        background: 'rgba(0,0,0,0.5)',
        zIndex: '50000',
        display: 'flex',
        alignItems: 'flex-start',
        justifyContent: 'center',
        paddingTop: '40px',
        overflowY: 'auto'
    });

    const modal = document.createElement('div');
    Object.assign(modal.style, {
        background: '#fff',
        borderRadius: '12px',
        width: '90%',
        maxWidth: maxWidth,
        maxHeight: '85vh',
        overflowY: 'auto',
        boxShadow: '0 20px 60px rgba(0,0,0,0.3)',
        marginBottom: '40px'
    });

    // Header
    const header = document.createElement('div');
    Object.assign(header.style, {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: '18px 24px',
        borderBottom: '1px solid #e5e7eb',
        position: 'sticky',
        top: '0',
        background: '#fff',
        borderRadius: '12px 12px 0 0',
        zIndex: '1'
    });

    const titleEl = document.createElement('h3');
    titleEl.textContent = title;
    Object.assign(titleEl.style, { margin: '0', fontSize: '18px', fontWeight: '600', color: '#111827' });

    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    Object.assign(closeBtn.style, {
        background: 'none',
        border: 'none',
        fontSize: '28px',
        cursor: 'pointer',
        color: '#6b7280',
        lineHeight: '1',
        padding: '0 4px'
    });
    closeBtn.onclick = () => removeModal(id);

    header.appendChild(titleEl);
    header.appendChild(closeBtn);

    // Body
    const body = document.createElement('div');
    body.style.padding = '24px';
    body.innerHTML = bodyHtml;

    modal.appendChild(header);
    modal.appendChild(body);
    overlay.appendChild(modal);

    // Close on overlay click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) removeModal(id);
    });

    document.body.appendChild(overlay);
    return modal;
}

/**
 * Generic form field builder for modals.
 */
function formField(label, inputHtml, id) {
    return `
        <div style="margin-bottom:14px;">
            <label for="${id || ''}" style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;color:#374151;">${escapeHtml(label)}</label>
            ${inputHtml}
        </div>`;
}

/**
 * Standard input style string.
 */
const inputStyle = 'width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;box-sizing:border-box;';

/**
 * Image path field with "Browse Gallery" button.
 */
function imagePathField(label, inputName, value, modalId) {
    var val = value ? ' value="' + escapeHtml(value) + '"' : '';
    return `
        <div style="margin-bottom:14px;">
            <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;color:#374151;">${escapeHtml(label)}</label>
            <div style="display:flex;gap:6px;">
                <input type="text" name="${inputName}" placeholder="images/photo.jpg" style="${inputStyle}flex:1;"${val} />
                <button type="button" onclick="openGalleryPicker('${inputName}','${modalId || ''}')" style="padding:8px 12px;background:#f1f5f9;border:1px solid #d1d5db;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;color:#374151;white-space:nowrap;display:flex;align-items:center;gap:4px;">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    Gallery
                </button>
            </div>
        </div>`;
}

/**
 * Standard button builder.
 */
// Icon SVGs for action buttons
const icons = {
    edit: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
    delete: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>',
    view: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
    enroll: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>',
    check: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
    status: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
};

// Map button labels to icons
const iconMap = {
    'Edit': icons.edit, 'Delete': icons.delete, 'View': icons.view,
    'Enroll': icons.enroll, 'Approve': icons.check, 'Reject': '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    'Status': icons.status,
};

function btnHtml(text, color, onclick) {
    const bg = { blue: '#2563eb', green: '#16a34a', red: '#dc2626', gray: '#6b7280', yellow: '#d97706' }[color] || color;
    const icon = iconMap[text] || '';
    return `<button onclick="${onclick}" title="${escapeHtml(text)}" style="padding:5px 8px;background:${bg};color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:12px;font-weight:500;display:inline-flex;align-items:center;gap:3px;line-height:1;">${icon}<span class="btn-label">${escapeHtml(text)}</span></button>`;
}

/** Wrap multiple btnHtml buttons in a flex container */
function actionCell(...buttons) {
    return `<div style="display:flex;gap:4px;flex-wrap:wrap;align-items:center;">${buttons.join('')}</div>`;
}

/**
 * Show a styled confirmation modal (replaces browser confirm).
 */
function showConfirmModal(title, message, confirmText, onConfirm, danger) {
    var iconColor = danger ? '#dc2626' : '#2563eb';
    var iconBg = danger ? '#fef2f2' : '#dbeafe';
    var btnBg = danger ? '#dc2626' : '#2563eb';
    var iconSvg = danger
        ? '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="' + iconColor + '" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
        : '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="' + iconColor + '" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>';
    var body =
        '<div style="text-align:center;padding:10px 0;">' +
            '<div style="width:56px;height:56px;border-radius:50%;background:' + iconBg + ';display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">' + iconSvg + '</div>' +
            '<p style="color:#374151;font-size:15px;margin:0 0 20px;line-height:1.5;">' + message + '</p>' +
            '<div style="display:flex;gap:10px;justify-content:center;">' +
                '<button onclick="removeModal(\'confirmModal\')" style="padding:10px 24px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;font-size:14px;">Cancel</button>' +
                '<button id="confirmActionBtn" style="padding:10px 24px;background:' + btnBg + ';color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:14px;">' + (confirmText || 'Confirm') + '</button>' +
            '</div>' +
        '</div>';
    createModal('confirmModal', title, body, '420px');
    document.getElementById('confirmActionBtn').addEventListener('click', function() {
        removeModal('confirmModal');
        onConfirm();
    });
}


// ============================================================
// GALLERY PICKER - pick image from gallery for forms
// ============================================================

function openGalleryPicker(targetInputName, modalId) {
    var body =
        '<div style="margin-bottom:12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">' +
            '<input type="text" id="gpSearch" placeholder="Search images..." style="' + inputStyle + 'flex:1;min-width:150px;" oninput="filterGalleryPicker()">' +
            '<select id="gpDir" style="' + inputStyle + 'width:auto;" onchange="filterGalleryPicker()">' +
                '<option value="all">All</option><option value="uploads">Uploads</option><option value="images">Images</option>' +
            '</select>' +
        '</div>' +
        '<div id="gpGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;max-height:400px;overflow-y:auto;padding:4px;">' +
            '<div style="grid-column:1/-1;text-align:center;padding:30px;color:#9ca3af;">Loading...</div>' +
        '</div>';

    createModal('galleryPickerModal', 'Choose Image', body, '700px');

    // Load images
    fetch(SITE_URL + '/api/gallery.php?action=list')
        .then(function(r) { return r.json(); })
        .then(function(json) {
            if (json.success) {
                window._gpFiles = json.data;
                window._gpTarget = targetInputName;
                window._gpParentModal = modalId;
                filterGalleryPicker();
            }
        });
}

function filterGalleryPicker() {
    var search = (document.getElementById('gpSearch') || {}).value || '';
    search = search.toLowerCase();
    var dir = (document.getElementById('gpDir') || {}).value || 'all';
    var files = (window._gpFiles || []).filter(function(f) {
        return (f.name.toLowerCase().indexOf(search) !== -1) && (dir === 'all' || f.dir === dir);
    });
    var grid = document.getElementById('gpGrid');
    if (!grid) return;
    if (files.length === 0) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:#9ca3af;">No images found</div>';
        return;
    }
    grid.innerHTML = files.map(function(f) {
        return '<div onclick="selectGalleryImage(\'' + escapeHtml(f.path) + '\')" style="cursor:pointer;border:2px solid transparent;border-radius:8px;overflow:hidden;transition:border-color 0.15s;" onmouseover="this.style.borderColor=\'#2563eb\'" onmouseout="this.style.borderColor=\'transparent\'">' +
            '<img src="' + SITE_URL + '/' + f.path + '" style="width:100%;height:80px;object-fit:cover;display:block;background:#f8fafc;" loading="lazy" onerror="this.style.display=\'none\'">' +
            '<div style="padding:4px 6px;font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#64748b;">' + escapeHtml(f.name) + '</div>' +
        '</div>';
    }).join('');
}

function selectGalleryImage(path) {
    var targetName = window._gpTarget;
    var parentModal = window._gpParentModal;
    // Close picker
    removeModal('galleryPickerModal');
    // Set the path in the target input
    // Find input by name within the parent modal
    var parentEl = parentModal ? document.getElementById(parentModal) : document;
    if (parentEl) {
        var input = parentEl.querySelector('input[name="' + targetName + '"]');
        if (input) input.value = path;
    }
    // Also try globally
    var inputs = document.querySelectorAll('input[name="' + targetName + '"]');
    inputs.forEach(function(inp) { inp.value = path; });
}

// ============================================================
// DASHBOARD
// ============================================================

async function loadDashboard() {
    try {
        const res = await fetch(API.dashboard, { cache: 'no-store' });
        const json = await res.json();
        if (!json.success) {
            console.warn('Dashboard API error:', json.message);
            return;
        }
        const d = json.data;

        const map = {
            statProducts: d.total_products,
            statCategories: d.total_categories,
            statDigitalCourses: d.total_digital_courses,
            statEntreprenCourses: d.total_entrepreneurship_courses,
            statOrders: d.total_orders,
            statRevenue: 'UGX ' + formatPrice(d.total_revenue),
            statCustomers: d.total_customers,
            statPendingReviews: d.pending_reviews,
            statTestimonials: d.total_testimonials
        };

        for (const [id, val] of Object.entries(map)) {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        }

        // Load recent orders for dashboard
        if (document.getElementById('recentOrdersBody')) {
            await loadRecentOrders();
        }
    } catch (err) {
        console.error('Dashboard load error:', err);
    }
}

async function loadRecentOrders() {
    try {
        const res = await fetch(API.orders + '?action=list', { cache: 'no-store' });
        const json = await res.json();
        if (!json.success) return;

        const tbody = document.getElementById('recentOrdersBody');
        if (!tbody) return;

        const recent = json.data.slice(0, 10);
        if (recent.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:#9ca3af;">No orders yet</td></tr>';
            return;
        }

        tbody.innerHTML = recent.map(o => `
            <tr>
                <td style="font-weight:600;">${escapeHtml(o.order_number)}</td>
                <td>${escapeHtml(o.customer_name)}</td>
                <td>${new Date(o.order_date).toLocaleDateString()}</td>
                <td>UGX ${formatPrice(o.total_now)}</td>
                <td>${getStatusBadge(o.status)}</td>
                <td>${btnHtml('View', 'blue', 'openViewOrderModal(' + o.id + ')')}</td>
            </tr>
        `).join('');
    } catch (err) {
        console.error('Recent orders load error:', err);
    }
}


// ============================================================
// PRODUCTS CRUD
// ============================================================

async function loadProducts() {
    try {
        const res = await fetch(API.products + '?action=list', { cache: 'no-store' });
        const json = await res.json();
        if (!json.success) return;
        allProducts = json.data;
        if (document.getElementById('productsTableBody')) {
            renderProductsTable(allProducts);
        }
    } catch (err) {
        console.error('Products load error:', err);
    }
}

function renderProductsTable(products) {
    const tbody = document.getElementById('productsTableBody');
    if (!tbody) return;

    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">No products found</td></tr>';
        return;
    }

    tbody.innerHTML = products.map(p => {
        const imgSrc = p.image ? SITE_URL + '/' + p.image : SITE_URL + '/images/product-1.svg';
        return `
        <tr>
            <td>${p.id}</td>
            <td><img src="${escapeHtml(imgSrc)}" class="admin-img-preview" style="width:48px;height:48px;object-fit:cover;border-radius:6px;" onerror="this.src='${SITE_URL}/images/product-1.svg'" /></td>
            <td style="font-weight:600;">${escapeHtml(p.title)}</td>
            <td>${escapeHtml(p.category || 'Uncategorized')}</td>
            <td>UGX ${formatPrice(p.price)}</td>
            <td>${p.stock}</td>
            <td>${Number(p.rating).toFixed(1)} &#11088;</td>
            <td>
                ${actionCell(btnHtml('Edit', 'blue', 'openEditProductModal(' + p.id + ')'), btnHtml('Delete', 'red', 'deleteProduct(' + p.id + ')'))}
            </td>
        </tr>`;
    }).join('');
}

function filterProductsTable() {
    const searchEl = document.getElementById('productSearch');
    if (!searchEl) return;
    const term = searchEl.value.toLowerCase().trim();
    if (!term) {
        renderProductsTable(allProducts);
        return;
    }
    const filtered = allProducts.filter(p =>
        (p.title || '').toLowerCase().includes(term) ||
        (p.category || '').toLowerCase().includes(term) ||
        (p.sku || '').toLowerCase().includes(term)
    );
    renderProductsTable(filtered);
}

function openAddProductModal() {
    const catOptions = allCategories.map(c =>
        `<option value="${c.id}">${escapeHtml(c.name)}</option>`
    ).join('');

    const body = `
        <form id="addProductForm" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                ${formField('Title *', `<input type="text" name="title" required style="${inputStyle}" />`, 'pTitle')}
                ${formField('Category *', `<select name="category_id" required style="${inputStyle}"><option value="">Select</option>${catOptions}</select>`, 'pCat')}
                ${formField('Price (UGX) *', `<input type="number" name="price" required style="${inputStyle}" />`, 'pPrice')}
                ${formField('Stock', `<input type="number" name="stock" value="0" style="${inputStyle}" />`, 'pStock')}
                ${formField('Original Price', `<input type="number" name="original_price" style="${inputStyle}" />`, 'pOrigPrice')}
                ${formField('Discount %', `<input type="number" name="discount" min="0" max="100" style="${inputStyle}" />`, 'pDiscount')}
                ${formField('Rating', `<input type="number" name="rating" step="0.5" min="0" max="5" value="0" style="${inputStyle}" />`, 'pRating')}
                ${formField('Reviews Count', `<input type="number" name="reviews" value="0" style="${inputStyle}" />`, 'pReviews')}
                ${formField('SKU', `<input type="text" name="sku" style="${inputStyle}" />`, 'pSku')}
                ${formField('Warranty', `<input type="text" name="warranty" style="${inputStyle}" />`, 'pWarranty')}
                ${imagePathField('Image Path', 'image', '', 'productModal')}
                ${formField('Upload Image', `<input type="file" name="image_file" accept="image/*" style="${inputStyle}" />`, 'pImageFile')}
            </div>
            ${formField('Description', `<textarea name="description" rows="3" style="${inputStyle}"></textarea>`, 'pDesc')}
            ${formField('Features (comma-separated)', `<input type="text" name="features_text" placeholder="Feature 1, Feature 2, Feature 3" style="${inputStyle}" />`, 'pFeatures')}
            <fieldset style="border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-top:12px;">
                <legend style="font-weight:600;color:#1e40af;padding:0 8px;">Credit Settings</legend>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                    ${formField('Credit Available', `<select name="credit_available" style="${inputStyle}"><option value="1">Yes</option><option value="0">No</option></select>`)}
                    ${formField('Default APR %', `<input type="number" name="default_apr" step="0.01" min="0" max="100" value="0" style="${inputStyle}" />`)}
                    ${formField('Terms (months)', `<input type="text" name="credit_terms_months" value="3,6" placeholder="e.g. 2,3,4,6" style="${inputStyle}" />`)}
                </div>
            </fieldset>
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="removeModal('productModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Add Product</button>
            </div>
        </form>`;

    createModal('productModal', 'Add New Product', body);

    document.getElementById('addProductForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const form = e.target;
            const fd = new FormData(form);
            fd.append('action', 'add');

            // Convert comma-separated features to JSON array
            const featuresText = fd.get('features_text') || '';
            const featuresArr = featuresText.split(',').map(f => f.trim()).filter(f => f);
            fd.set('features', JSON.stringify(featuresArr));
            fd.delete('features_text');

            const res = await fetch(API.products, { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Product added successfully');
                removeModal('productModal');
                await loadProducts();
            } else {
                showToast(json.message || 'Failed to add product', 'error');
            }
        } catch (err) {
            showToast('Error adding product: ' + err.message, 'error');
        }
    });
}

async function openEditProductModal(id) {
    try {
        const res = await fetch(API.products + '?action=get&id=' + id);
        const json = await res.json();
        if (!json.success) {
            showToast(json.message || 'Product not found', 'error');
            return;
        }
        const p = json.data;
        const catOptions = allCategories.map(c =>
            `<option value="${c.id}" ${String(c.id) === String(p.category_id) ? 'selected' : ''}>${escapeHtml(c.name)}</option>`
        ).join('');

        const featuresStr = Array.isArray(p.features) ? p.features.join(', ') : '';

        const body = `
            <form id="editProductForm" enctype="multipart/form-data">
                <input type="hidden" name="id" value="${p.id}" />
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                    ${formField('Title *', `<input type="text" name="title" required value="${escapeHtml(p.title)}" style="${inputStyle}" />`)}
                    ${formField('Category *', `<select name="category_id" required style="${inputStyle}"><option value="">Select</option>${catOptions}</select>`)}
                    ${formField('Price (UGX) *', `<input type="number" name="price" required value="${p.price}" style="${inputStyle}" />`)}
                    ${formField('Stock', `<input type="number" name="stock" value="${p.stock || 0}" style="${inputStyle}" />`)}
                    ${formField('Original Price', `<input type="number" name="original_price" value="${p.original_price || ''}" style="${inputStyle}" />`)}
                    ${formField('Discount %', `<input type="number" name="discount" min="0" max="100" value="${p.discount || ''}" style="${inputStyle}" />`)}
                    ${formField('Rating', `<input type="number" name="rating" step="0.5" min="0" max="5" value="${p.rating || 0}" style="${inputStyle}" />`)}
                    ${formField('Reviews Count', `<input type="number" name="reviews" value="${p.reviews || 0}" style="${inputStyle}" />`)}
                    ${formField('SKU', `<input type="text" name="sku" value="${escapeHtml(p.sku || '')}" style="${inputStyle}" />`)}
                    ${formField('Warranty', `<input type="text" name="warranty" value="${escapeHtml(p.warranty || '')}" style="${inputStyle}" />`)}
                    ${imagePathField('Image Path', 'image', p.image || '', 'productModal')}
                    ${formField('Upload Image', `<input type="file" name="image_file" accept="image/*" style="${inputStyle}" />`)}
                </div>
                ${formField('Description', `<textarea name="description" rows="3" style="${inputStyle}">${escapeHtml(p.description || '')}</textarea>`)}
                ${formField('Features (comma-separated)', `<input type="text" name="features_text" value="${escapeHtml(featuresStr)}" style="${inputStyle}" />`)}
                <fieldset style="border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-top:12px;">
                    <legend style="font-weight:600;color:#1e40af;padding:0 8px;">Credit Settings</legend>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                        ${formField('Credit Available', `<select name="credit_available" style="${inputStyle}"><option value="1" ${(p.credit_available !== 0 && p.credit_available !== '0') ? 'selected' : ''}>Yes</option><option value="0" ${(p.credit_available === 0 || p.credit_available === '0') ? 'selected' : ''}>No</option></select>`)}
                        ${formField('Default APR %', `<input type="number" name="default_apr" step="0.01" min="0" max="100" value="${p.default_apr || 0}" style="${inputStyle}" />`)}
                        ${formField('Terms (months)', `<input type="text" name="credit_terms_months" value="${escapeHtml(p.credit_terms_months || '3,6')}" placeholder="e.g. 2,3,4,6" style="${inputStyle}" />`)}
                    </div>
                </fieldset>
                <div style="text-align:right;margin-top:18px;">
                    <button type="button" onclick="removeModal('productModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                    <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Update Product</button>
                </div>
            </form>`;

        createModal('productModal', 'Edit Product', body);

        document.getElementById('editProductForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const form = e.target;
                const fd = new FormData(form);
                fd.append('action', 'edit');

                const featuresText = fd.get('features_text') || '';
                const featuresArr = featuresText.split(',').map(f => f.trim()).filter(f => f);
                fd.set('features', JSON.stringify(featuresArr));
                fd.delete('features_text');

                const res2 = await fetch(API.products, { method: 'POST', body: fd });
                const json2 = await res2.json();
                if (json2.success) {
                    showToast(json2.message || 'Product updated successfully');
                    removeModal('productModal');
                    await loadProducts();
                } else {
                    showToast(json2.message || 'Failed to update product', 'error');
                }
            } catch (err) {
                showToast('Error updating product: ' + err.message, 'error');
            }
        });
    } catch (err) {
        showToast('Error loading product: ' + err.message, 'error');
    }
}

async function deleteProduct(id) {
    showConfirmModal('Delete Product', 'Are you sure you want to delete this product? This action cannot be undone.', 'Delete', async function() { await _doDeleteProduct(id); }, true); } async function _doDeleteProduct(id) {
    try {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        const res = await fetch(API.products, { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            showToast(json.message || 'Product deleted');
            await loadProducts();
        } else {
            showToast(json.message || 'Failed to delete product', 'error');
        }
    } catch (err) {
        showToast('Error deleting product: ' + err.message, 'error');
    }
}


// ============================================================
// CATEGORIES CRUD
// ============================================================

async function loadCategories() {
    try {
        const res = await fetch(API.categories + '?action=list', { cache: 'no-store' });
        const json = await res.json();
        if (!json.success) return;
        allCategories = json.data;
        if (document.getElementById('categoriesTableBody')) {
            renderCategoriesTable(allCategories);
        }
    } catch (err) {
        console.error('Categories load error:', err);
    }
}

function renderCategoriesTable(cats) {
    const tbody = document.getElementById('categoriesTableBody');
    if (!tbody) return;

    if (cats.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:#9ca3af;">No categories found</td></tr>';
        return;
    }

    tbody.innerHTML = cats.map(c => {
        const productCount = allProducts.filter(p => String(p.category_id) === String(c.id)).length;
        const statusBadge = Number(c.status) === 1
            ? '<span style="padding:3px 10px;border-radius:20px;background:#dcfce7;color:#166534;font-size:12px;font-weight:600;">Active</span>'
            : '<span style="padding:3px 10px;border-radius:20px;background:#fee2e2;color:#991b1b;font-size:12px;font-weight:600;">Inactive</span>';
        return `
        <tr>
            <td>${c.id}</td>
            <td style="font-size:24px;">${escapeHtml(c.icon || '')}</td>
            <td style="font-weight:600;">${escapeHtml(c.name)}</td>
            <td>${escapeHtml(c.description || '')}</td>
            <td>${productCount}</td>
            <td>${statusBadge}</td>
            <td>
                ${actionCell(btnHtml('Edit', 'blue', 'openEditCategoryModal(' + c.id + ')'), btnHtml('Delete', 'red', 'deleteCategory(' + c.id + ')'))}
            </td>
        </tr>`;
    }).join('');
}

function openAddCategoryModal() {
    const body = `
        <form id="addCategoryForm">
            ${formField('Name *', `<input type="text" name="name" required style="${inputStyle}" />`)}
            ${formField('Icon (emoji)', `<input type="text" name="icon" placeholder="e.g. &#128187;" style="${inputStyle}" />`)}
            ${formField('Description', `<textarea name="description" rows="3" style="${inputStyle}"></textarea>`)}
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="removeModal('categoryModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Add Category</button>
            </div>
        </form>`;

    createModal('categoryModal', 'Add New Category', body, '500px');

    document.getElementById('addCategoryForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const fd = new FormData(e.target);
            fd.append('action', 'add');
            const res = await fetch(API.categories, { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Category added');
                removeModal('categoryModal');
                await loadCategories();
                await loadProducts(); // refresh product counts
            } else {
                showToast(json.message || 'Failed to add category', 'error');
            }
        } catch (err) {
            showToast('Error adding category: ' + err.message, 'error');
        }
    });
}

function openEditCategoryModal(id) {
    const c = allCategories.find(cat => Number(cat.id) === Number(id));
    if (!c) {
        showToast('Category not found', 'error');
        return;
    }

    const body = `
        <form id="editCategoryForm">
            <input type="hidden" name="id" value="${c.id}" />
            ${formField('Name *', `<input type="text" name="name" required value="${escapeHtml(c.name)}" style="${inputStyle}" />`)}
            ${formField('Icon (emoji)', `<input type="text" name="icon" value="${escapeHtml(c.icon || '')}" style="${inputStyle}" />`)}
            ${formField('Description', `<textarea name="description" rows="3" style="${inputStyle}">${escapeHtml(c.description || '')}</textarea>`)}
            ${formField('Status', `<select name="status" style="${inputStyle}">
                <option value="1" ${Number(c.status) === 1 ? 'selected' : ''}>Active</option>
                <option value="0" ${Number(c.status) === 0 ? 'selected' : ''}>Inactive</option>
            </select>`)}
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="removeModal('categoryModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Update Category</button>
            </div>
        </form>`;

    createModal('categoryModal', 'Edit Category', body, '500px');

    document.getElementById('editCategoryForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const fd = new FormData(e.target);
            fd.append('action', 'edit');
            const res = await fetch(API.categories, { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Category updated');
                removeModal('categoryModal');
                await loadCategories();
            } else {
                showToast(json.message || 'Failed to update category', 'error');
            }
        } catch (err) {
            showToast('Error updating category: ' + err.message, 'error');
        }
    });
}

async function deleteCategory(id) {
    showConfirmModal('Delete Category', 'Are you sure you want to delete this category?', 'Delete', async function() { await _doDeleteCategory(id); }, true); } async function _doDeleteCategory(id) {
    try {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        const res = await fetch(API.categories, { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            showToast(json.message || 'Category deleted');
            await loadCategories();
        } else {
            showToast(json.message || 'Failed to delete category', 'error');
        }
    } catch (err) {
        showToast('Error deleting category: ' + err.message, 'error');
    }
}


// ============================================================
// COURSES CRUD
// ============================================================

async function loadDigitalCourses() {
    try {
        const res = await fetch(API.courses + '?action=list&type=digital_skilling', { cache: 'no-store' });
        const json = await res.json();
        if (!json.success) return;
        allDigitalCourses = json.data;
        renderCoursesTable(allDigitalCourses, 'digitalCoursesTableBody');
    } catch (err) {
        console.error('Digital courses load error:', err);
    }
}

async function loadEntreprenCourses() {
    try {
        const res = await fetch(API.courses + '?action=list&type=entrepreneurship', { cache: 'no-store' });
        const json = await res.json();
        if (!json.success) return;
        allEntreprenCourses = json.data;
        renderCoursesTable(allEntreprenCourses, 'entreprenCoursesTableBody');
    } catch (err) {
        console.error('Entrepreneurship courses load error:', err);
    }
}

function renderCoursesTable(courses, tbodyId) {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;

    if (courses.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:20px;color:#9ca3af;">No courses found</td></tr>';
        return;
    }

    tbody.innerHTML = courses.map(c => `
        <tr>
            <td>${c.id}</td>
            <td style="font-size:20px;">${escapeHtml(c.icon || '')}</td>
            <td style="font-weight:600;">${escapeHtml(c.title)}</td>
            <td>UGX ${formatPrice(c.price)}</td>
            <td>${escapeHtml(c.duration || '')}</td>
            <td>${c.modules || 0}</td>
            <td>${escapeHtml(c.level || '')}</td>
            <td>${Number(c.rating).toFixed(1)} &#11088;</td>
            <td>
                ${actionCell(btnHtml('Enroll', 'green', 'openEnrollCourseModal(' + c.id + ', "' + escapeHtml(c.title).replace(/"/g, '\\"') + '", ' + c.price + ')'), btnHtml('Edit', 'blue', 'openEditCourseModal(' + c.id + ')'), btnHtml('Delete', 'red', 'deleteCourse(' + c.id + ')'))}
            </td>
        </tr>
    `).join('');
}

function openAddCourseModal(courseType) {
    const isDigital = courseType === 'digital_skilling';
    const defaultPrice = isDigital ? 200000 : 250000;
    const typeLabel = isDigital ? 'Digital Skilling' : 'Entrepreneurship';

    const body = `
        <form id="addCourseForm" enctype="multipart/form-data">
            <input type="hidden" name="course_type" value="${courseType}" />
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                ${formField('Title *', `<input type="text" name="title" required style="${inputStyle}" />`)}
                ${formField('Price (UGX)', `<input type="number" name="price" value="${defaultPrice}" style="${inputStyle}" />`)}
                ${formField('Duration', `<input type="text" name="duration" placeholder="e.g. 8 weeks" style="${inputStyle}" />`)}
                ${formField('Level', `<select name="level" style="${inputStyle}">
                    <option value="Beginner">Beginner</option>
                    <option value="Beginner to Intermediate">Beginner to Intermediate</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                </select>`)}
                ${formField('Modules', `<input type="number" name="modules" value="0" style="${inputStyle}" />`)}
                ${formField('Lessons', `<input type="number" name="lessons" value="0" style="${inputStyle}" />`)}
                ${formField('Icon (emoji)', `<input type="text" name="icon" style="${inputStyle}" />`)}
                ${formField('Instructor', `<input type="text" name="instructor" style="${inputStyle}" />`)}
                ${imagePathField('Image Path', 'image', '', 'courseModal')}
                ${formField('Upload Image', `<input type="file" name="image_file" accept="image/*" style="${inputStyle}" />`)}
                ${formField('SKU', `<input type="text" name="sku" style="${inputStyle}" />`)}
                ${formField('Rating', `<input type="number" name="rating" step="0.5" min="0" max="5" value="0" style="${inputStyle}" />`)}
                ${formField('Reviews Count', `<input type="number" name="reviews" value="0" style="${inputStyle}" />`)}
            </div>
            ${formField('Description', `<textarea name="description" rows="3" style="${inputStyle}"></textarea>`)}
            <fieldset style="border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-top:12px;">
                <legend style="font-weight:600;color:#1e40af;padding:0 8px;">Credit Settings</legend>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                    ${formField('Credit Available', `<select name="credit_available" style="${inputStyle}"><option value="1">Yes</option><option value="0">No</option></select>`)}
                    ${formField('Default APR %', `<input type="number" name="default_apr" step="0.01" min="0" max="100" value="0" style="${inputStyle}" />`)}
                    ${formField('Terms (months)', `<input type="text" name="credit_terms_months" value="3,6" placeholder="e.g. 2,3,4,6" style="${inputStyle}" />`)}
                </div>
            </fieldset>
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="removeModal('courseModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Add Course</button>
            </div>
        </form>`;

    createModal('courseModal', 'Add ' + typeLabel + ' Course', body);

    document.getElementById('addCourseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const fd = new FormData(e.target);
            fd.append('action', 'add');
            const res = await fetch(API.courses, { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Course added');
                removeModal('courseModal');
                if (isDigital) await loadDigitalCourses();
                else await loadEntreprenCourses();
            } else {
                showToast(json.message || 'Failed to add course', 'error');
            }
        } catch (err) {
            showToast('Error adding course: ' + err.message, 'error');
        }
    });
}

async function openEditCourseModal(id) {
    try {
        const res = await fetch(API.courses + '?action=get&id=' + id);
        const json = await res.json();
        if (!json.success) {
            showToast(json.message || 'Course not found', 'error');
            return;
        }
        const c = json.data;
        const levels = ['Beginner', 'Beginner to Intermediate', 'Intermediate', 'Advanced'];
        const levelOptions = levels.map(l =>
            `<option value="${l}" ${c.level === l ? 'selected' : ''}>${l}</option>`
        ).join('');

        const body = `
            <form id="editCourseForm" enctype="multipart/form-data">
                <input type="hidden" name="id" value="${c.id}" />
                <input type="hidden" name="course_type" value="${escapeHtml(c.course_type)}" />
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                    ${formField('Title *', `<input type="text" name="title" required value="${escapeHtml(c.title)}" style="${inputStyle}" />`)}
                    ${formField('Price (UGX)', `<input type="number" name="price" value="${c.price}" style="${inputStyle}" />`)}
                    ${formField('Duration', `<input type="text" name="duration" value="${escapeHtml(c.duration || '')}" style="${inputStyle}" />`)}
                    ${formField('Level', `<select name="level" style="${inputStyle}">${levelOptions}</select>`)}
                    ${formField('Modules', `<input type="number" name="modules" value="${c.modules || 0}" style="${inputStyle}" />`)}
                    ${formField('Lessons', `<input type="number" name="lessons" value="${c.lessons || 0}" style="${inputStyle}" />`)}
                    ${formField('Icon (emoji)', `<input type="text" name="icon" value="${escapeHtml(c.icon || '')}" style="${inputStyle}" />`)}
                    ${formField('Instructor', `<input type="text" name="instructor" value="${escapeHtml(c.instructor || '')}" style="${inputStyle}" />`)}
                    ${imagePathField('Image Path', 'image', c.image || '', 'courseModal')}
                    ${formField('Upload Image', `<input type="file" name="image_file" accept="image/*" style="${inputStyle}" />`)}
                    ${formField('SKU', `<input type="text" name="sku" value="${escapeHtml(c.sku || '')}" style="${inputStyle}" />`)}
                    ${formField('Rating', `<input type="number" name="rating" step="0.5" min="0" max="5" value="${c.rating || 0}" style="${inputStyle}" />`)}
                    ${formField('Reviews Count', `<input type="number" name="reviews" value="${c.reviews || 0}" style="${inputStyle}" />`)}
                </div>
                ${formField('Description', `<textarea name="description" rows="3" style="${inputStyle}">${escapeHtml(c.description || '')}</textarea>`)}
                <fieldset style="border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-top:12px;">
                    <legend style="font-weight:600;color:#1e40af;padding:0 8px;">Credit Settings</legend>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                        ${formField('Credit Available', `<select name="credit_available" style="${inputStyle}"><option value="1" ${Number(c.credit_available) === 1 ? 'selected' : ''}>Yes</option><option value="0" ${Number(c.credit_available) === 0 ? 'selected' : ''}>No</option></select>`)}
                        ${formField('Default APR %', `<input type="number" name="default_apr" step="0.01" min="0" max="100" value="${c.default_apr || 0}" style="${inputStyle}" />`)}
                        ${formField('Terms (months)', `<input type="text" name="credit_terms_months" value="${escapeHtml(c.credit_terms_months || '3,6')}" placeholder="e.g. 2,3,4,6" style="${inputStyle}" />`)}
                    </div>
                </fieldset>
                <div style="text-align:right;margin-top:18px;">
                    <button type="button" onclick="removeModal('courseModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                    <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Update Course</button>
                </div>
            </form>`;

        createModal('courseModal', 'Edit Course', body);

        document.getElementById('editCourseForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const fd = new FormData(e.target);
                fd.append('action', 'edit');
                const res2 = await fetch(API.courses, { method: 'POST', body: fd });
                const json2 = await res2.json();
                if (json2.success) {
                    showToast(json2.message || 'Course updated');
                    removeModal('courseModal');
                    await loadDigitalCourses();
                    await loadEntreprenCourses();
                } else {
                    showToast(json2.message || 'Failed to update course', 'error');
                }
            } catch (err) {
                showToast('Error updating course: ' + err.message, 'error');
            }
        });
    } catch (err) {
        showToast('Error loading course: ' + err.message, 'error');
    }
}

async function openEnrollCourseModal(courseId, courseTitle, coursePrice) {
    // Fetch customers
    let customerOpts = '<option value="">-- Select Customer --</option>';
    try {
        const custRes = await fetch(API.customers + '?action=list', { cache: 'no-store' });
        const custJson = await custRes.json();
        if (custJson.success && custJson.data) {
            customerOpts += custJson.data.map(c =>
                `<option value="${c.id}" data-name="${escapeHtml(c.name)}" data-email="${escapeHtml(c.email)}" data-phone="${escapeHtml(c.phone || '')}">${escapeHtml(c.name)} (${escapeHtml(c.email)})</option>`
            ).join('');
        }
    } catch (e) {}

    const body = `
        <form id="enrollCourseForm">
            <p style="margin-bottom:16px;color:#64748b;">Enroll a customer in <strong>${escapeHtml(courseTitle)}</strong> (UGX ${formatPrice(coursePrice)})</p>
            ${formField('Select Customer *', `<select name="customer_id" required style="${inputStyle}" id="enrollCustomerSelect">${customerOpts}</select>`)}
            ${formField('Payment Method', `<select name="payment_method" style="${inputStyle}">
                <option value="admin_assigned">Admin Assigned</option>
                <option value="cash_on_delivery">Cash on Delivery</option>
                <option value="mobile_money">Mobile Money</option>
            </select>`)}
            ${formField('Status', `<select name="status" style="${inputStyle}">
                <option value="completed">Completed (Enrolled)</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
            </select>`)}
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="removeModal('enrollModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                <button type="submit" style="padding:8px 20px;background:#059669;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Enroll Student</button>
            </div>
        </form>`;

    createModal('enrollModal', 'Enroll Student in Course', body);

    document.getElementById('enrollCourseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const select = document.getElementById('enrollCustomerSelect');
            const opt = select.options[select.selectedIndex];
            const customerName = opt.getAttribute('data-name') || '';
            const customerEmail = opt.getAttribute('data-email') || '';
            const customerPhone = opt.getAttribute('data-phone') || '';
            const fd = new FormData(e.target);

            const orderFd = new FormData();
            orderFd.append('action', 'add');
            orderFd.append('customer_name', customerName);
            orderFd.append('customer_email', customerEmail);
            orderFd.append('customer_phone', customerPhone);
            orderFd.append('total_now', coursePrice);
            orderFd.append('total_full', coursePrice);
            orderFd.append('payment_method', fd.get('payment_method'));
            orderFd.append('status', fd.get('status'));
            orderFd.append('admin_notes', 'Enrolled by admin in: ' + courseTitle);
            orderFd.append('items_json', JSON.stringify([{
                type: 'course',
                itemId: courseId,
                name: courseTitle,
                title: courseTitle,
                price: coursePrice,
                quantity: 1
            }]));

            const res = await fetch(API.orders, { method: 'POST', body: orderFd });
            const json = await res.json();
            if (json.success) {
                showToast('Student enrolled successfully!');
                removeModal('enrollModal');
            } else {
                showToast(json.message || 'Failed to enroll student', 'error');
            }
        } catch (err) {
            showToast('Error enrolling student: ' + err.message, 'error');
        }
    });
}

async function deleteCourse(id) {
    showConfirmModal('Delete Course', 'Are you sure you want to delete this course?', 'Delete', async function() { await _doDeleteCourse(id); }, true); } async function _doDeleteCourse(id) {
    try {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        const res = await fetch(API.courses, { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            showToast(json.message || 'Course deleted');
            await loadDigitalCourses();
            await loadEntreprenCourses();
        } else {
            showToast(json.message || 'Failed to delete course', 'error');
        }
    } catch (err) {
        showToast('Error deleting course: ' + err.message, 'error');
    }
}


// ============================================================
// ORDERS CRUD
// ============================================================

async function loadOrders() {
    try {
        const res = await fetch(API.orders + '?action=list', { cache: 'no-store' });
        const json = await res.json();
        if (!json.success) return;
        allOrders = json.data;
        renderOrdersTable(allOrders);
    } catch (err) {
        console.error('Orders load error:', err);
    }
}

function getStatusBadge(status) {
    const map = {
        pending: { bg: '#fef3c7', color: '#92400e', label: 'Pending' },
        processing: { bg: '#dbeafe', color: '#1e40af', label: 'Processing' },
        completed: { bg: '#dcfce7', color: '#166534', label: 'Completed' },
        cancelled: { bg: '#fee2e2', color: '#991b1b', label: 'Cancelled' }
    };
    const s = map[status] || map.pending;
    return `<span style="padding:3px 10px;border-radius:20px;background:${s.bg};color:${s.color};font-size:12px;font-weight:600;">${s.label}</span>`;
}

function renderOrdersTable(orders) {
    const tbody = document.getElementById('ordersTableBody');
    if (!tbody) return;

    if (orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:20px;color:#9ca3af;">No orders found</td></tr>';
        return;
    }

    const statusOptions = ['pending', 'processing', 'completed', 'cancelled'];

    tbody.innerHTML = orders.map(o => {
        const selectOpts = statusOptions.map(s =>
            `<option value="${s}" ${o.status === s ? 'selected' : ''}>${s.charAt(0).toUpperCase() + s.slice(1)}</option>`
        ).join('');

        return `
        <tr>
            <td style="font-weight:600;">${escapeHtml(o.order_number)}</td>
            <td>${escapeHtml(o.customer_name)}</td>
            <td>${escapeHtml(o.customer_email || '')}</td>
            <td>${escapeHtml(o.customer_phone || '')}</td>
            <td>${new Date(o.order_date).toLocaleDateString()}</td>
            <td>UGX ${formatPrice(o.total_now)}</td>
            <td>
                <select onchange="updateOrderStatus(${o.id}, this.value)" style="padding:4px 8px;border-radius:4px;border:1px solid #d1d5db;font-size:12px;">
                    ${selectOpts}
                </select>
            </td>
            <td>
                ${actionCell(btnHtml('View', 'blue', 'openViewOrderModal(' + o.id + ')'), btnHtml('Edit', 'green', 'openEditOrderModal(' + o.id + ')'), btnHtml('Delete', 'red', 'deleteOrder(' + o.id + ')'))}
            </td>
        </tr>`;
    }).join('');
}

function filterOrdersTable() {
    const searchEl = document.getElementById('orderSearch');
    const statusEl = document.getElementById('orderStatusFilter');
    const term = searchEl ? searchEl.value.toLowerCase().trim() : '';
    const status = statusEl ? statusEl.value : '';

    let filtered = allOrders;
    if (term) {
        filtered = filtered.filter(o =>
            (o.order_number || '').toLowerCase().includes(term) ||
            (o.customer_name || '').toLowerCase().includes(term) ||
            (o.customer_email || '').toLowerCase().includes(term) ||
            (o.customer_phone || '').toLowerCase().includes(term)
        );
    }
    if (status) {
        filtered = filtered.filter(o => o.status === status);
    }
    renderOrdersTable(filtered);
}

async function updateOrderStatus(id, newStatus) {
    try {
        const fd = new FormData();
        fd.append('action', 'update_status');
        fd.append('id', id);
        fd.append('status', newStatus);
        const res = await fetch(API.orders, { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            showToast('Order status updated to ' + newStatus);
            // Update local data
            const order = allOrders.find(o => Number(o.id) === Number(id));
            if (order) order.status = newStatus;
        } else {
            showToast(json.message || 'Failed to update status', 'error');
            await loadOrders(); // reload to reset select
        }
    } catch (err) {
        showToast('Error updating status: ' + err.message, 'error');
    }
}

async function openViewOrderModal(id) {
    try {
        const res = await fetch(API.orders + '?action=get&id=' + id);
        const json = await res.json();
        if (!json.success) {
            showToast(json.message || 'Order not found', 'error');
            return;
        }
        const o = json.data;

        let items = [];
        try { items = JSON.parse(o.items_json || '[]'); } catch (e) { /* ignore */ }

        // Resolve item names/prices/slugs from loaded product/course data
        var allCourses = (allDigitalCourses || []).concat(allEntreprenCourses || []);
        items = items.map(function(i) {
            if (i.type === 'course') {
                var cid = i.itemId || i.productId;
                var course = allCourses.find(c => c.id === cid || ('course-' + c.id) === cid);
                if (course) { i.title = i.title || i.name || course.title; i.price = i.price || course.price; i.slug = course.slug; }
            } else {
                var product = (allProducts || []).find(p => p.id === i.productId);
                if (product) { i.title = i.title || i.name || product.title; i.price = i.price || product.price; i.slug = product.slug; }
            }
            return i;
        });

        let itemsHtml = '<p style="color:#9ca3af;">No items recorded</p>';
        if (items.length > 0) {
            itemsHtml = `
                <table style="width:100%;border-collapse:collapse;margin-top:8px;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            <th style="padding:8px;text-align:left;border-bottom:1px solid #e5e7eb;">Item</th>
                            <th style="padding:8px;text-align:left;border-bottom:1px solid #e5e7eb;">Type</th>
                            <th style="padding:8px;text-align:left;border-bottom:1px solid #e5e7eb;">Qty</th>
                            <th style="padding:8px;text-align:left;border-bottom:1px solid #e5e7eb;">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${items.map(i => {
                            var name = escapeHtml(i.title || i.name || 'Product #' + (i.productId || i.itemId || '?'));
                            var link = '';
                            if (i.type === 'course' && i.slug) {
                                link = SITE_URL + '/course/' + i.slug;
                            } else if (i.slug) {
                                link = SITE_URL + '/product/' + i.slug;
                            }
                            var nameHtml = link ? '<a href="' + link + '" target="_blank" style="color:#2563eb;text-decoration:none;font-weight:500;">' + name + '</a>' : name;
                            return '<tr>' +
                                '<td style="padding:8px;border-bottom:1px solid #f3f4f6;">' + nameHtml + '</td>' +
                                '<td style="padding:8px;border-bottom:1px solid #f3f4f6;">' + (i.type === 'course' ? 'Course' : 'Product') + '</td>' +
                                '<td style="padding:8px;border-bottom:1px solid #f3f4f6;">' + (i.quantity || i.qty || 1) + '</td>' +
                                '<td style="padding:8px;border-bottom:1px solid #f3f4f6;">UGX ' + formatPrice(i.price || 0) + '</td>' +
                            '</tr>';
                        }).join('')}
                    </tbody>
                </table>`;
        }

        const infoRow = (label, val) =>
            `<div style="display:flex;padding:8px 0;border-bottom:1px solid #f3f4f6;">
                <span style="font-weight:600;width:160px;color:#374151;">${label}</span>
                <span style="color:#6b7280;">${val}</span>
            </div>`;

        const body = `
            <div>
                ${infoRow('Order Number', escapeHtml(o.order_number))}
                ${infoRow('Customer', escapeHtml(o.customer_name))}
                ${infoRow('Email', escapeHtml(o.customer_email || 'N/A'))}
                ${infoRow('Phone', escapeHtml(o.customer_phone || 'N/A'))}
                ${infoRow('Date', new Date(o.order_date).toLocaleDateString())}
                ${infoRow('Status', getStatusBadge(o.status))}
                ${infoRow('Payment Method', escapeHtml(o.payment_method || 'N/A'))}
                ${infoRow('Total (Now)', 'UGX ' + formatPrice(o.total_now))}
                ${infoRow('Total (Full)', 'UGX ' + formatPrice(o.total_full))}
                ${infoRow('Shipping Address', escapeHtml(o.shipping_address || 'N/A'))}
                ${infoRow('Admin Notes', escapeHtml(o.admin_notes || 'None'))}
            </div>
            <h4 style="margin:20px 0 8px;font-size:15px;font-weight:600;">Order Items</h4>
            ${itemsHtml}
        `;

        createModal('orderViewModal', 'Order Details - ' + escapeHtml(o.order_number), body, '700px');
    } catch (err) {
        showToast('Error loading order: ' + err.message, 'error');
    }
}

function openAddOrderModal() {
    const body = `
        <form id="addOrderForm">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                ${formField('Customer Name *', `<input type="text" name="customer_name" required style="${inputStyle}" />`)}
                ${formField('Email', `<input type="email" name="customer_email" style="${inputStyle}" />`)}
                ${formField('Phone', `<input type="text" name="customer_phone" style="${inputStyle}" />`)}
                ${formField('Payment Method', `<select name="payment_method" style="${inputStyle}">
                    <option value="cash_on_delivery">Cash on Delivery</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="credit_card">Credit Card</option>
                </select>`)}
                ${formField('Total Now (UGX)', `<input type="number" name="total_now" value="0" style="${inputStyle}" />`)}
                ${formField('Total Full (UGX)', `<input type="number" name="total_full" value="0" style="${inputStyle}" />`)}
                ${formField('Status', `<select name="status" style="${inputStyle}">
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>`)}
            </div>
            ${formField('Shipping Address', `<textarea name="shipping_address" rows="2" style="${inputStyle}"></textarea>`)}
            ${formField('Admin Notes', `<textarea name="admin_notes" rows="2" style="${inputStyle}"></textarea>`)}
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="removeModal('orderModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Create Order</button>
            </div>
        </form>`;

    createModal('orderModal', 'Create New Order', body, '700px');

    document.getElementById('addOrderForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const fd = new FormData(e.target);
            fd.append('action', 'add');
            const res = await fetch(API.orders, { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Order created');
                removeModal('orderModal');
                await loadOrders();
            } else {
                showToast(json.message || 'Failed to create order', 'error');
            }
        } catch (err) {
            showToast('Error creating order: ' + err.message, 'error');
        }
    });
}

async function openEditOrderModal(id) {
    try {
        const res = await fetch(API.orders + '?action=get&id=' + id);
        const json = await res.json();
        if (!json.success) {
            showToast(json.message || 'Order not found', 'error');
            return;
        }
        const o = json.data;

        // Fetch customers for assignment dropdown
        let customerOpts = '<option value="">-- No customer assigned --</option>';
        try {
            const custRes = await fetch(API.customers + '?action=list', { cache: 'no-store' });
            const custJson = await custRes.json();
            if (custJson.success && custJson.data) {
                customerOpts += custJson.data.map(c =>
                    `<option value="${c.id}" ${o.customer_id == c.id ? 'selected' : ''}>${escapeHtml(c.name)} (${escapeHtml(c.email)})</option>`
                ).join('');
            }
        } catch (e) {}

        const paymentMethods = ['cash_on_delivery', 'mobile_money', 'bank_transfer', 'credit_card'];
        const paymentOpts = paymentMethods.map(pm =>
            `<option value="${pm}" ${o.payment_method === pm ? 'selected' : ''}>${pm.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</option>`
        ).join('');

        const statuses = ['pending', 'processing', 'completed', 'cancelled'];
        const statusOpts = statuses.map(s =>
            `<option value="${s}" ${o.status === s ? 'selected' : ''}>${s.charAt(0).toUpperCase() + s.slice(1)}</option>`
        ).join('');

        const body = `
            <form id="editOrderForm">
                <input type="hidden" name="id" value="${o.id}" />
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                    ${formField('Assign to Customer', `<select name="customer_id" style="${inputStyle}">${customerOpts}</select>`)}
                    ${formField('Customer Name *', `<input type="text" name="customer_name" required value="${escapeHtml(o.customer_name)}" style="${inputStyle}" />`)}
                    ${formField('Email', `<input type="email" name="customer_email" value="${escapeHtml(o.customer_email || '')}" style="${inputStyle}" />`)}
                    ${formField('Phone', `<input type="text" name="customer_phone" value="${escapeHtml(o.customer_phone || '')}" style="${inputStyle}" />`)}
                    ${formField('Payment Method', `<select name="payment_method" style="${inputStyle}">${paymentOpts}</select>`)}
                    ${formField('Total Now (UGX)', `<input type="number" name="total_now" value="${o.total_now || 0}" style="${inputStyle}" />`)}
                    ${formField('Total Full (UGX)', `<input type="number" name="total_full" value="${o.total_full || 0}" style="${inputStyle}" />`)}
                    ${formField('Status', `<select name="status" style="${inputStyle}">${statusOpts}</select>`)}
                </div>
                ${formField('Admin Notes', `<textarea name="admin_notes" rows="2" style="${inputStyle}">${escapeHtml(o.admin_notes || '')}</textarea>`)}
                <div style="text-align:right;margin-top:18px;">
                    <button type="button" onclick="removeModal('orderModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                    <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Update Order</button>
                </div>
            </form>`;

        createModal('orderModal', 'Edit Order - ' + escapeHtml(o.order_number), body, '700px');

        document.getElementById('editOrderForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const fd = new FormData(e.target);
                fd.append('action', 'edit');
                const res2 = await fetch(API.orders, { method: 'POST', body: fd });
                const json2 = await res2.json();
                if (json2.success) {
                    showToast(json2.message || 'Order updated');
                    removeModal('orderModal');
                    await loadOrders();
                } else {
                    showToast(json2.message || 'Failed to update order', 'error');
                }
            } catch (err) {
                showToast('Error updating order: ' + err.message, 'error');
            }
        });
    } catch (err) {
        showToast('Error loading order: ' + err.message, 'error');
    }
}

async function deleteOrder(id) {
    showConfirmModal('Delete Order', 'Are you sure you want to delete this order? This action cannot be undone.', 'Delete', async function() { await _doDeleteOrder(id); }, true); } async function _doDeleteOrder(id) {
    try {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        const res = await fetch(API.orders, { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            showToast(json.message || 'Order deleted');
            await loadOrders();
        } else {
            showToast(json.message || 'Failed to delete order', 'error');
        }
    } catch (err) {
        showToast('Error deleting order: ' + err.message, 'error');
    }
}


// ============================================================
// CUSTOMERS CRUD
// ============================================================

async function loadCustomers() {
    try {
        const res = await fetch(API.customers + '?action=list', { cache: 'no-store' });
        const json = await res.json();
        if (!json.success) return;
        allCustomers = json.data;
        renderCustomersTable(allCustomers);
    } catch (err) {
        console.error('Customers load error:', err);
    }
}

function renderCustomersTable(customers) {
    const tbody = document.getElementById('customersTableBody');
    if (!tbody) return;

    if (customers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:#9ca3af;">No customers found</td></tr>';
        return;
    }

    tbody.innerHTML = customers.map(c => `
        <tr>
            <td>${c.id}</td>
            <td style="font-weight:600;">${escapeHtml(c.name)}</td>
            <td>${escapeHtml(c.email || '')}</td>
            <td>${escapeHtml(c.phone || '')}</td>
            <td>${c.order_count || 0}</td>
            <td>${c.created_at ? new Date(c.created_at).toLocaleDateString() : 'N/A'}</td>
            <td>
                ${actionCell(btnHtml('Edit', 'blue', 'openEditCustomerModal(' + c.id + ')'), btnHtml('Delete', 'red', 'deleteCustomer(' + c.id + ')'))}
            </td>
        </tr>
    `).join('');
}

function filterCustomersTable() {
    const searchEl = document.getElementById('customerSearch');
    if (!searchEl) return;
    const term = searchEl.value.toLowerCase().trim();
    if (!term) {
        renderCustomersTable(allCustomers);
        return;
    }
    const filtered = allCustomers.filter(c =>
        (c.name || '').toLowerCase().includes(term) ||
        (c.email || '').toLowerCase().includes(term) ||
        (c.phone || '').toLowerCase().includes(term)
    );
    renderCustomersTable(filtered);
}

function openAddCustomerModal() {
    const body = `
        <form id="addCustomerForm">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                ${formField('Name *', `<input type="text" name="name" required style="${inputStyle}" />`)}
                ${formField('Email *', `<input type="email" name="email" required style="${inputStyle}" />`)}
                ${formField('Phone', `<input type="text" name="phone" style="${inputStyle}" />`)}
                ${formField('City', `<input type="text" name="city" style="${inputStyle}" />`)}
                ${formField('Country', `<input type="text" name="country" value="Uganda" style="${inputStyle}" />`)}
            </div>
            ${formField('Address', `<textarea name="address" rows="2" style="${inputStyle}"></textarea>`)}
            ${formField('Notes', `<textarea name="notes" rows="2" style="${inputStyle}"></textarea>`)}
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="removeModal('customerModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Add Customer</button>
            </div>
        </form>`;

    createModal('customerModal', 'Add New Customer', body, '600px');

    document.getElementById('addCustomerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const fd = new FormData(e.target);
            fd.append('action', 'add');
            const res = await fetch(API.customers, { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Customer added');
                removeModal('customerModal');
                await loadCustomers();
            } else {
                showToast(json.message || 'Failed to add customer', 'error');
            }
        } catch (err) {
            showToast('Error adding customer: ' + err.message, 'error');
        }
    });
}

function openEditCustomerModal(id) {
    const c = allCustomers.find(cust => Number(cust.id) === Number(id));
    if (!c) {
        showToast('Customer not found', 'error');
        return;
    }

    const body = `
        <form id="editCustomerForm">
            <input type="hidden" name="id" value="${c.id}" />
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                ${formField('Name *', `<input type="text" name="name" required value="${escapeHtml(c.name)}" style="${inputStyle}" />`)}
                ${formField('Email *', `<input type="email" name="email" required value="${escapeHtml(c.email || '')}" style="${inputStyle}" />`)}
                ${formField('Phone', `<input type="text" name="phone" value="${escapeHtml(c.phone || '')}" style="${inputStyle}" />`)}
                ${formField('City', `<input type="text" name="city" value="${escapeHtml(c.city || '')}" style="${inputStyle}" />`)}
                ${formField('Country', `<input type="text" name="country" value="${escapeHtml(c.country || 'Uganda')}" style="${inputStyle}" />`)}
            </div>
            ${formField('Address', `<textarea name="address" rows="2" style="${inputStyle}">${escapeHtml(c.address || '')}</textarea>`)}
            ${formField('Notes', `<textarea name="notes" rows="2" style="${inputStyle}">${escapeHtml(c.notes || '')}</textarea>`)}
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="removeModal('customerModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Update Customer</button>
            </div>
        </form>`;

    createModal('customerModal', 'Edit Customer', body, '600px');

    document.getElementById('editCustomerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const fd = new FormData(e.target);
            fd.append('action', 'edit');
            const res = await fetch(API.customers, { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Customer updated');
                removeModal('customerModal');
                await loadCustomers();
            } else {
                showToast(json.message || 'Failed to update customer', 'error');
            }
        } catch (err) {
            showToast('Error updating customer: ' + err.message, 'error');
        }
    });
}

async function deleteCustomer(id) {
    showConfirmModal('Delete Customer', 'Are you sure you want to delete this customer?', 'Delete', async function() { await _doDeleteCustomer(id); }, true); } async function _doDeleteCustomer(id) {
    try {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        const res = await fetch(API.customers, { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            showToast(json.message || 'Customer deleted');
            await loadCustomers();
        } else {
            showToast(json.message || 'Failed to delete customer', 'error');
        }
    } catch (err) {
        showToast('Error deleting customer: ' + err.message, 'error');
    }
}


// ============================================================
// REVIEWS CRUD
// ============================================================

async function loadReviews() {
    try {
        const res = await fetch(API.reviews + '?action=list_all', { cache: 'no-store' });
        const json = await res.json();
        if (!json.success) return;
        allReviews = json.data;
        renderReviewsTable(allReviews);
    } catch (err) {
        console.error('Reviews load error:', err);
    }
}

function getReviewStatusBadge(status) {
    const map = {
        pending: { bg: '#fef3c7', color: '#92400e', label: 'Pending' },
        approved: { bg: '#dcfce7', color: '#166534', label: 'Approved' },
        rejected: { bg: '#fee2e2', color: '#991b1b', label: 'Rejected' }
    };
    const s = map[status] || map.pending;
    return `<span style="padding:3px 10px;border-radius:20px;background:${s.bg};color:${s.color};font-size:12px;font-weight:600;">${s.label}</span>`;
}

function renderReviewsTable(reviews) {
    const tbody = document.getElementById('reviewsTableBody');
    if (!tbody) return;

    if (reviews.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">No reviews found</td></tr>';
        return;
    }

    tbody.innerHTML = reviews.map(r => {
        const itemInfo = r.item_title
            ? escapeHtml(r.item_title)
            : escapeHtml((r.item_type || 'item') + ' #' + (r.item_id || ''));
        const truncatedText = (r.review_text || '').length > 80
            ? escapeHtml(r.review_text.substring(0, 80)) + '...'
            : escapeHtml(r.review_text || '');
        const date = r.created_at ? new Date(r.created_at).toLocaleDateString() : 'N/A';

        let actionArr = [];
        if (r.status !== 'approved') {
            actionArr.push(btnHtml('Approve', 'green', `approveReview(${r.id}, 'approved')`));
        }
        if (r.status !== 'rejected') {
            actionArr.push(btnHtml('Reject', 'yellow', `approveReview(${r.id}, 'rejected')`));
        }
        actionArr.push(btnHtml('Edit', 'blue', 'openEditReviewModal(' + r.id + ')'));
        actionArr.push(btnHtml('Delete', 'red', 'deleteReview(' + r.id + ')'));
        let actionBtns = actionCell(...actionArr);

        return `
        <tr>
            <td>${r.id}</td>
            <td>${itemInfo}</td>
            <td>${escapeHtml(r.customer_name || '')}</td>
            <td>${Number(r.rating).toFixed(1)} &#11088;</td>
            <td title="${escapeHtml(r.review_text || '')}">${truncatedText}</td>
            <td>${getReviewStatusBadge(r.status)}</td>
            <td>${date}</td>
            <td style="white-space:nowrap;">${actionBtns}</td>
        </tr>`;
    }).join('');
}

function filterReviewsTable() {
    const searchEl = document.getElementById('reviewSearch');
    const statusEl = document.getElementById('reviewStatusFilter');
    const term = searchEl ? searchEl.value.toLowerCase().trim() : '';
    const status = statusEl ? statusEl.value : '';

    let filtered = allReviews;
    if (term) {
        filtered = filtered.filter(r =>
            (r.customer_name || '').toLowerCase().includes(term) ||
            (r.review_text || '').toLowerCase().includes(term) ||
            (r.item_title || '').toLowerCase().includes(term)
        );
    }
    if (status) {
        filtered = filtered.filter(r => r.status === status);
    }
    renderReviewsTable(filtered);
}

async function approveReview(id, newStatus) {
    try {
        const fd = new FormData();
        fd.append('action', 'approve');
        fd.append('id', id);
        fd.append('status', newStatus);
        const res = await fetch(API.reviews, { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            showToast('Review ' + newStatus);
            await loadReviews();
        } else {
            showToast(json.message || 'Failed to update review', 'error');
        }
    } catch (err) {
        showToast('Error updating review: ' + err.message, 'error');
    }
}

function openAddReviewModal() {
    // Build item options from products + courses
    let itemOptions = '<option value="">Select an item</option>';

    if (allProducts.length > 0) {
        itemOptions += '<optgroup label="Products">';
        allProducts.forEach(p => {
            itemOptions += `<option value="product-${p.id}">${escapeHtml(p.title)}</option>`;
        });
        itemOptions += '</optgroup>';
    }
    if (allDigitalCourses.length > 0) {
        itemOptions += '<optgroup label="Digital Skilling Courses">';
        allDigitalCourses.forEach(c => {
            itemOptions += `<option value="course-${c.id}">${escapeHtml(c.title)}</option>`;
        });
        itemOptions += '</optgroup>';
    }
    if (allEntreprenCourses.length > 0) {
        itemOptions += '<optgroup label="Entrepreneurship Courses">';
        allEntreprenCourses.forEach(c => {
            itemOptions += `<option value="course-${c.id}">${escapeHtml(c.title)}</option>`;
        });
        itemOptions += '</optgroup>';
    }

    const body = `
        <form id="addReviewForm">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                ${formField('Item *', `<select name="item_ref" required style="${inputStyle}">${itemOptions}</select>`)}
                ${formField('Customer Name *', `<input type="text" name="customer_name" required style="${inputStyle}" />`)}
                ${formField('Customer Email', `<input type="email" name="customer_email" style="${inputStyle}" />`)}
                ${formField('Rating', `<input type="number" name="rating" step="0.5" min="1" max="5" value="5" style="${inputStyle}" />`)}
                ${formField('Status', `<select name="status" style="${inputStyle}">
                    <option value="approved">Approved</option>
                    <option value="pending">Pending</option>
                    <option value="rejected">Rejected</option>
                </select>`)}
            </div>
            ${formField('Review Text *', `<textarea name="review_text" rows="4" required style="${inputStyle}"></textarea>`)}
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="removeModal('reviewModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Add Review</button>
            </div>
        </form>`;

    createModal('reviewModal', 'Add New Review', body, '600px');

    document.getElementById('addReviewForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const fd = new FormData(e.target);
            fd.append('action', 'add');

            // Parse item_ref (format: "type-id")
            const itemRef = fd.get('item_ref') || '';
            const parts = itemRef.split('-');
            if (parts.length === 2) {
                fd.set('item_type', parts[0]);
                fd.set('item_id', parts[1]);
            }
            fd.delete('item_ref');

            const res = await fetch(API.reviews, { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Review added');
                removeModal('reviewModal');
                await loadReviews();
            } else {
                showToast(json.message || 'Failed to add review', 'error');
            }
        } catch (err) {
            showToast('Error adding review: ' + err.message, 'error');
        }
    });
}

function openEditReviewModal(id) {
    const r = allReviews.find(rev => Number(rev.id) === Number(id));
    if (!r) {
        showToast('Review not found', 'error');
        return;
    }

    const body = `
        <form id="editReviewForm">
            <input type="hidden" name="id" value="${r.id}" />
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                ${formField('Customer Name *', `<input type="text" name="customer_name" required value="${escapeHtml(r.customer_name || '')}" style="${inputStyle}" />`)}
                ${formField('Rating', `<input type="number" name="rating" step="0.5" min="1" max="5" value="${r.rating || 5}" style="${inputStyle}" />`)}
                ${formField('Status', `<select name="status" style="${inputStyle}">
                    <option value="pending" ${r.status === 'pending' ? 'selected' : ''}>Pending</option>
                    <option value="approved" ${r.status === 'approved' ? 'selected' : ''}>Approved</option>
                    <option value="rejected" ${r.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                </select>`)}
            </div>
            ${formField('Review Text *', `<textarea name="review_text" rows="4" required style="${inputStyle}">${escapeHtml(r.review_text || '')}</textarea>`)}
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="removeModal('reviewModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Update Review</button>
            </div>
        </form>`;

    createModal('reviewModal', 'Edit Review', body, '600px');

    document.getElementById('editReviewForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const fd = new FormData(e.target);
            fd.append('action', 'edit');
            const res = await fetch(API.reviews, { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Review updated');
                removeModal('reviewModal');
                await loadReviews();
            } else {
                showToast(json.message || 'Failed to update review', 'error');
            }
        } catch (err) {
            showToast('Error updating review: ' + err.message, 'error');
        }
    });
}

async function deleteReview(id) {
    showConfirmModal('Delete Review', 'Are you sure you want to delete this review?', 'Delete', async function() { await _doDeleteReview(id); }, true); } async function _doDeleteReview(id) {
    try {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        const res = await fetch(API.reviews, { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            showToast(json.message || 'Review deleted');
            await loadReviews();
        } else {
            showToast(json.message || 'Failed to delete review', 'error');
        }
    } catch (err) {
        showToast('Error deleting review: ' + err.message, 'error');
    }
}


// ============================================================
// TESTIMONIALS CRUD
// ============================================================

async function loadTestimonials() {
    try {
        const res = await fetch(API.testimonials + '?action=list_all', { cache: 'no-store' });
        const json = await res.json();
        if (!json.success) return;
        allTestimonials = json.data;
        renderTestimonialsTable(allTestimonials);
    } catch (err) {
        console.error('Testimonials load error:', err);
    }
}

function renderTestimonialsTable(items) {
    const tbody = document.getElementById('testimonialsTableBody');
    if (!tbody) return;

    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:20px;color:#9ca3af;">No testimonials found</td></tr>';
        return;
    }

    tbody.innerHTML = items.map(t => {
        const imgSrc = t.image ? SITE_URL + '/' + t.image : '';
        const imgHtml = imgSrc
            ? `<img src="${escapeHtml(imgSrc)}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;" onerror="this.style.display='none'" />`
            : '<span style="display:inline-block;width:40px;height:40px;border-radius:50%;background:#e5e7eb;text-align:center;line-height:40px;font-weight:600;color:#6b7280;">' + escapeHtml((t.name || '?').charAt(0).toUpperCase()) + '</span>';

        const truncatedContent = (t.content || '').length > 80
            ? escapeHtml(t.content.substring(0, 80)) + '...'
            : escapeHtml(t.content || '');

        const statusBadge = Number(t.status) === 1
            ? '<span style="padding:3px 10px;border-radius:20px;background:#dcfce7;color:#166534;font-size:12px;font-weight:600;">Active</span>'
            : '<span style="padding:3px 10px;border-radius:20px;background:#fee2e2;color:#991b1b;font-size:12px;font-weight:600;">Inactive</span>';

        return `
        <tr>
            <td>${t.id}</td>
            <td>${imgHtml}</td>
            <td style="font-weight:600;">${escapeHtml(t.name)}</td>
            <td>${escapeHtml(t.role || '')}</td>
            <td title="${escapeHtml(t.content || '')}">${truncatedContent}</td>
            <td>${t.rating || 0} &#11088;</td>
            <td>${statusBadge}</td>
            <td>${t.display_order || 0}</td>
            <td>
                ${actionCell(btnHtml('Edit', 'blue', 'openEditTestimonialModal(' + t.id + ')'), btnHtml('Delete', 'red', 'deleteTestimonial(' + t.id + ')'))}
            </td>
        </tr>`;
    }).join('');
}

function openAddTestimonialModal() {
    const body = `
        <form id="addTestimonialForm" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                ${formField('Name *', `<input type="text" name="name" required style="${inputStyle}" />`)}
                ${formField('Role', `<input type="text" name="role" placeholder="e.g. CEO, Student" style="${inputStyle}" />`)}
                ${formField('Upload Image', `<input type="file" name="image_file" accept="image/*" style="${inputStyle}" />`)}
                ${imagePathField('Or Image Path', 'image', '', 'testimonialModal')}
                ${formField('Rating', `<select name="rating" style="${inputStyle}">
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="2">2 Stars</option>
                    <option value="1">1 Star</option>
                </select>`)}
                ${formField('Status', `<select name="status" style="${inputStyle}">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>`)}
                ${formField('Display Order', `<input type="number" name="display_order" value="0" style="${inputStyle}" />`)}
            </div>
            ${formField('Content *', `<textarea name="content" rows="4" required style="${inputStyle}"></textarea>`)}
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="removeModal('testimonialModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Add Testimonial</button>
            </div>
        </form>`;

    createModal('testimonialModal', 'Add New Testimonial', body, '600px');

    document.getElementById('addTestimonialForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const fd = new FormData(e.target);
            fd.append('action', 'add');
            const res = await fetch(API.testimonials, { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Testimonial added');
                removeModal('testimonialModal');
                await loadTestimonials();
            } else {
                showToast(json.message || 'Failed to add testimonial', 'error');
            }
        } catch (err) {
            showToast('Error adding testimonial: ' + err.message, 'error');
        }
    });
}

function openEditTestimonialModal(id) {
    const t = allTestimonials.find(item => Number(item.id) === Number(id));
    if (!t) {
        showToast('Testimonial not found', 'error');
        return;
    }

    const ratingOptions = [5, 4, 3, 2, 1].map(n =>
        `<option value="${n}" ${Number(t.rating) === n ? 'selected' : ''}>${n} Star${n > 1 ? 's' : ''}</option>`
    ).join('');

    const imgPreview = t.image ? `<div style="margin-bottom:8px;"><img src="${SITE_URL}/${escapeHtml(t.image)}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:1px solid #e2e8f0;" onerror="this.style.display='none'"></div>` : '';
    const body = `
        <form id="editTestimonialForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="${t.id}" />
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                ${formField('Name *', `<input type="text" name="name" required value="${escapeHtml(t.name)}" style="${inputStyle}" />`)}
                ${formField('Role', `<input type="text" name="role" value="${escapeHtml(t.role || '')}" style="${inputStyle}" />`)}
                ${formField('Upload New Image', `${imgPreview}<input type="file" name="image_file" accept="image/*" style="${inputStyle}" />`)}
                ${imagePathField('Or Image Path', 'image', t.image || '', 'testimonialModal')}
                ${formField('Rating', `<select name="rating" style="${inputStyle}">${ratingOptions}</select>`)}
                ${formField('Status', `<select name="status" style="${inputStyle}">
                    <option value="1" ${Number(t.status) === 1 ? 'selected' : ''}>Active</option>
                    <option value="0" ${Number(t.status) === 0 ? 'selected' : ''}>Inactive</option>
                </select>`)}
                ${formField('Display Order', `<input type="number" name="display_order" value="${t.display_order || 0}" style="${inputStyle}" />`)}
            </div>
            ${formField('Content *', `<textarea name="content" rows="4" required style="${inputStyle}">${escapeHtml(t.content || '')}</textarea>`)}
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="removeModal('testimonialModal')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>
                <button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Update Testimonial</button>
            </div>
        </form>`;

    createModal('testimonialModal', 'Edit Testimonial', body, '600px');

    document.getElementById('editTestimonialForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const fd = new FormData(e.target);
            fd.append('action', 'edit');
            const res = await fetch(API.testimonials, { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                showToast(json.message || 'Testimonial updated');
                removeModal('testimonialModal');
                await loadTestimonials();
            } else {
                showToast(json.message || 'Failed to update testimonial', 'error');
            }
        } catch (err) {
            showToast('Error updating testimonial: ' + err.message, 'error');
        }
    });
}

async function deleteTestimonial(id) {
    showConfirmModal('Delete Testimonial', 'Are you sure you want to delete this testimonial?', 'Delete', async function() { await _doDeleteTestimonial(id); }, true); } async function _doDeleteTestimonial(id) {
    try {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        const res = await fetch(API.testimonials, { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
            showToast(json.message || 'Testimonial deleted');
            await loadTestimonials();
        } else {
            showToast(json.message || 'Failed to delete testimonial', 'error');
        }
    } catch (err) {
        showToast('Error deleting testimonial: ' + err.message, 'error');
    }
}


// ============================================================
// PAGE INITIALIZATION
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    // Load data based on which page elements exist
    if (document.getElementById('statProducts')) { loadDashboard(); loadProducts(); loadDigitalCourses(); loadEntreprenCourses(); }
    if (document.getElementById('productsTableBody')) { loadProducts(); loadCategories(); }
    if (document.getElementById('categoriesTableBody')) { loadCategories(); loadProducts(); }
    if (document.getElementById('digitalCoursesTableBody')) { loadDigitalCourses(); loadEntreprenCourses(); }
    if (document.getElementById('ordersTableBody')) { loadOrders(); loadProducts(); loadDigitalCourses(); loadEntreprenCourses(); }
    if (document.getElementById('customersTableBody')) loadCustomers();
    if (document.getElementById('reviewsTableBody')) { loadReviews(); loadProducts(); loadDigitalCourses(); loadEntreprenCourses(); }
    if (document.getElementById('testimonialsTableBody')) loadTestimonials();

    // Search and filter event listeners
    const productSearch = document.getElementById('productSearch');
    if (productSearch) productSearch.addEventListener('input', filterProductsTable);

    const orderSearch = document.getElementById('orderSearch');
    if (orderSearch) orderSearch.addEventListener('input', filterOrdersTable);

    const orderStatusFilter = document.getElementById('orderStatusFilter');
    if (orderStatusFilter) orderStatusFilter.addEventListener('change', filterOrdersTable);

    const customerSearch = document.getElementById('customerSearch');
    if (customerSearch) customerSearch.addEventListener('input', filterCustomersTable);

    const reviewSearch = document.getElementById('reviewSearch');
    if (reviewSearch) reviewSearch.addEventListener('input', filterReviewsTable);

    const reviewStatusFilter = document.getElementById('reviewStatusFilter');
    if (reviewStatusFilter) reviewStatusFilter.addEventListener('change', filterReviewsTable);

    // Mobile sidebar toggle
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            if (overlay) overlay.classList.toggle('active');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // Admin logout
    const logoutBtn = document.getElementById('adminLogoutLink');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const res = await fetch(API.auth + '?action=logout', { cache: 'no-store' });
                await res.json();
            } catch (err) {
                // Even if fetch fails, still redirect to login
            }
            // Clear any cached state
            window.location.replace(SITE_URL + '/admin/login');
        });
    }
});
