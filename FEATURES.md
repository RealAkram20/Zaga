# Zaga Tech Credit E-Commerce Platform - Complete Feature Guide

## Overview
TechStore is a full-featured e-commerce website for computing devices with advanced buy-now-pay-later (BNPL) capabilities, including interest/amortization support and a complete payment management system.

---

## Core Features

### 1. **Product Catalog**
- **120+ Computing Products** across 6 categories:
  - Laptops
  - Desktops
  - Tablets
  - Accessories
  - Peripherals
  - Storage

- **Product Information Includes:**
  - High-resolution product images
  - Detailed specifications
  - Customer ratings and reviews
  - Stock availability
  - SKU and warranty information
  - Discount pricing

### 2. **Shopping Experience**
- **Search & Filter:**
  - Full-text product search
  - Filter by category, price range, and rating
  - Pagination for easy browsing

- **Product Details Page:**
  - Multiple product images with gallery view
  - Detailed specifications and features
  - Customer reviews
  - Related products

- **Shopping Cart:**
  - Add/remove items
  - Adjust quantities
  - Real-time cart updates
  - Persistent storage using localStorage

### 3. **Responsive Design**
- Mobile-first approach
- Breakpoints for tablet (768px) and desktop (1200px)
- Fully responsive navigation and layout
- Optimized touch interactions

---

## Advanced Credit Payment Features

### 1. **Payment Method Selection**
Users can choose between:
- **Pay in Full:** Complete payment at checkout
- **Buy on Credit:** Installment payment with deposit

### 2. **Flexible Credit Terms**
- **Payment Period Options:** 3 or 6 months
- **Deposit Requirement:** 20% of total purchase price (due immediately)
- **Remainder Finance:** 80% of purchase price split over selected months

### 3. **Interest Rate Options**
During checkout, customers can select from multiple APR options:
- **0% APR** (No Interest) - Default
- **5% APR**
- **9.99% APR**
- **14.99% APR**

### 4. **Amortization Schedule**
- **Automatic Calculation:** System generates full amortization schedules for credit purchases
- **Schedule Components:**
  - Payment number and due date
  - Payment amount (principal + interest)
  - Principal and interest breakdown
  - Remaining balance after payment

**Example Schedule (with interest):**
```
Payment 1: $335.45 (Principal: $334.12, Interest: $1.33, Balance: $1,665.88)
Payment 2: $335.45 (Principal: $334.37, Interest: $1.08, Balance: $1,331.51)
Payment 3: $335.45 (Principal: $334.62, Interest: $0.83, Balance: $996.89)
```

---

## Order Management

### 1. **Order History Page** (`order-history.html`)
- **Features:**
  - View all customer orders
  - Outstanding balance tracking
  - Credit payment indicators
  - Next payment due dates
  - View detailed payment schedules

- **For Each Order:**
  - Order number and date
  - Items purchased with quantities
  - Amount paid now vs. remaining balance
  - Payment status (Completed/Credit)
  - Payment schedule button
  - Payment portal link

### 2. **Payment Portal** (`payment-portal.html`)
- **Payment Schedule Display:**
  - Full amortization schedule with all payments
  - Due dates for each installment
  - Principal and interest breakdown

- **Payment Options:**
  - Pre-calculated quick payment options:
    - Next payment
    - Double payment
    - Full remaining balance
  - Custom payment amount input

- **Payment Processing:**
  - Credit card information entry
  - Card number validation
  - Expiry date (MM/YY) validation
  - CVV validation
  - Payment confirmation

- **Payment Tracking:**
  - Record of all payments made
  - Updated remaining balance
  - Automatic schedule refresh

---

## Admin Features

### 1. **Admin Panel** (`admin.html`)
- **Dashboard Statistics:**
  - Total products count
  - Total orders count
  - Total revenue

- **Product Management:**
  - View all products
  - Search and filter products
  - Edit product details
  - Add new products
  - Track inventory

- **Order Management:**
  - View recent orders
  - Order details and status
  - Customer information

- **Quick Access:**
  - Direct link to Order History & Payments page
  - View outstanding balances
  - Payment status overview

### 2. **Order History Admin View**
- Comprehensive order tracking
- Outstanding balance calculations
- Credit payment monitoring
- Payment schedule access

---

## User Authentication

### Simple Sign-In System
- Email-based authentication
- Optional display name
- User area in navigation
- Quick order access
- Sign-out capability

---

## Technical Implementation

### Technology Stack
- **Frontend:** HTML5, CSS3, Vanilla JavaScript (ES6+)
- **Storage:** Browser localStorage
- **Architecture:** Single-page application (SPA) with client-side routing

### Key Functions

#### `computeCredit(amount, months, annualInterestRate)`
Calculates credit terms and generates amortization schedule
```javascript
// Example: $1000 for 3 months at 9.99% APR
const credit = computeCredit(1000, 3, 9.99);
// Returns: {
//   amount: 1000,
//   deposit: 200,
//   remaining: 800,
//   months: 3,
//   monthly: 268.15,
//   totalInterest: 4.45,
//   schedule: [...]
// }
```

#### `placeOrder(orderMeta)`
Places order and generates full payment schedule
```javascript
const order = placeOrder({
  customer: "John Doe",
  email: "john@example.com",
  totalNow: 500,
  totalFull: 1000,
  items: [...]
});
// Returns: order with orderNumber, date, and full schedule
```

#### `addToCart(productId, quantity, paymentPlan)`
Adds items to cart with optional payment plan
```javascript
addToCart(5, 1, {
  type: 'credit',
  months: 3,
  interestRate: 9.99
});
```

---

## Data Storage

### localStorage Keys
- **`products`:** Product catalog (120 items)
- **`cart`:** Current shopping cart
- **`orders`:** All placed orders with schedules
- **`zagatech_current_user`:** Current logged-in user
- **`users`:** User authentication data

### Order Structure
```javascript
{
  orderNumber: "ORD-1234567890",
  date: "12/15/2025, 2:30:45 PM",
  user: "john@example.com",
  customer: "John Doe",
  email: "john@example.com",
  totalNow: 500,
  totalFull: 1000,
  items: [...],
  schedule: [
    {
      productId: 5,
      productTitle: "Astra Air 2025",
      dueDate: "1/15/2025",
      amount: 268.15,
      principal: 266.82,
      interest: 1.33,
      remainingBalance: 531.85,
      paid: false
    },
    ...
  ],
  paymentsMade: []
}
```

---

## File Structure

```
Zagatech.html/
├── index.html              # Home page
├── shop.html               # Product listing & search
├── product-detail.html     # Single product with payment options
├── cart.html               # Shopping cart
├── checkout.html           # Checkout & order placement
├── order-history.html      # Order history & payment schedules
├── payment-portal.html     # Customer payment management
├── admin.html              # Admin dashboard
├── styles.css              # All styling
└── images/
    └── product-1.svg       # Product images (placeholder SVGs)

../
└── script.js               # Core business logic & utilities
```

---

## Usage Guide

### For Customers

1. **Browse Products:**
   - Visit Home or Shop page
   - Use search or filters to find products
   - Click on product for details

2. **Purchase with Credit:**
   - Add item to cart
   - Go to checkout
   - Select "Buy on Credit"
   - Choose 3 or 6 month term
   - Select interest rate (0%, 5%, 9.99%, or 14.99%)
   - Review payment breakdown
   - Complete payment form
   - Deposit charged immediately

3. **Manage Payments:**
   - Sign in to access "My Orders"
   - View Order History page
   - Click "View Payment Schedule" for details
   - Click "Make Payment" when ready
   - Complete payment through portal
   - Track remaining balance

### For Admins

1. **Dashboard:**
   - Monitor sales statistics
   - Manage product inventory
   - Add/edit products

2. **Order Management:**
   - View all orders
   - Access "Order History & Payments" for detailed tracking
   - Monitor outstanding balances
   - Review payment schedules

---

## Key Advantages

✓ **No Backend Required** - Fully client-side using localStorage  
✓ **Flexible Payment Terms** - 3 or 6 month options  
✓ **Interest Support** - Multiple APR options with full amortization  
✓ **Transparent Pricing** - Clear breakdown of principal and interest  
✓ **User-Friendly** - Intuitive payment portal  
✓ **Admin Control** - Comprehensive order and payment tracking  
✓ **Mobile-Responsive** - Works on all devices  

---

## Browser Compatibility

- Chrome/Edge 80+
- Firefox 75+
- Safari 13+
- Mobile browsers (iOS Safari, Chrome Mobile)

---

## Future Enhancements

- Backend integration for persistent data
- Email notifications for payment reminders
- Multiple payment methods (PayPal, Stripe, etc.)
- Advanced reporting and analytics
- Subscription management
- Partial refunds and order modifications
- Multi-currency support

---

*Last Updated: 2025 | Zaga Tech Credit v2.0*

---

## Credit Policy, Eligibility & FAQs

### How the Credit Policy Works
- Deposit: 20% of the total purchase price is due at checkout. This reserves the order and reduces the financed principal.
- Finance Amount: The remaining 80% is financed over the selected term (3 or 6 months) and may carry interest depending on the APR chosen.
- Interest: Customers choose an APR option (0%, 5%, 9.99%, 14.99%) during checkout. The system calculates a full amortization schedule showing monthly payment, principal, interest and remaining balance.
- Schedules & Due Dates: Schedules are generated at order placement and stored with the order. Due dates are calculated monthly from the order date.

### Eligibility Criteria
To apply for credit at Zaga Tech Credit, customers should meet the following requirements (simulated for this demo):
- Age: 18 years or older.
- Residency: Must provide a valid billing address within supported regions (see site settings).
- Identification: A valid government-issued ID or email is used to validate identity during the checkout process.
- Purchase Minimum: Credit is offered for eligible purchases above a minimum threshold (e.g., $50).
- Credit Check: In a production deployment, a soft credit check would typically be performed. In this demo the check is simulated and approval is automatic when above requirements are met.

### How to Apply for Credit (Apply Now)
1. Add products to your cart and proceed to the product detail or cart page.
2. Click "Buy on Credit" on a product or select "Buy on Credit" in the checkout flow.
3. Choose the payment term (3 or 6 months) and preferred APR.
4. Review the displayed amortization schedule and deposit amount.
5. Confirm the selection and complete checkout by paying the deposit (20%).
6. Access "My Orders" → "Make Payment" to manage installment payments and pay future installments.

For convenience, the "Apply for Credit Now" action is available in the product detail payment modal and at checkout; it directs you to confirm terms and pay the deposit.

### Frequently Asked Questions (FAQs)

Q: Will Zaga Tech Credit perform a credit check?

A: For this demo the approval is simulated. In a real deployment Zaga Tech Credit would perform a soft credit check (or other identity checks) during the credit application process. Customers would be informed and asked for consent prior to any check.

Q: What happens if I miss a payment?

A: This demo marks payments as unpaid in the schedule. In production, late payments might incur late fees or collection steps according to local law and the store's terms of service. Always review the store's full terms before selecting credit.

Q: Can I pay off my balance early?

A: Yes. The payment portal supports paying the full remaining balance at any time—select "Full Balance" or enter a custom amount to settle early. Early payoff may reduce total interest paid.

Q: Are refunds possible for credit purchases?

A: Refunds follow the store's refund policy. If an item purchased on credit is refunded, the schedule and payments will be adjusted accordingly. In this demo, refund handling is manual and illustrative only.

Q: Is my payment information secure?

A: This demo simulates card entry for UI testing only and does not transmit card details to any payment processor. For production, integrate with a PCI-compliant payment gateway (e.g., Stripe, PayPal) to securely handle card payments.

Q: Who do I contact for billing support?

A: Use the contact info in the site footer (support@zagatechcredit.com) or the admin panel for merchant-side support.
