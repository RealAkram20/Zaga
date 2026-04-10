# Quick Start Guide - Zaga Tech Credit

## Getting Started

### 1. **Open the Website**
- Open `index.html` in your web browser
- You can also use any local server (Python: `python -m http.server 8000`)

### 2. **Browse Products**
- Home page shows featured products
- Click "Shop" to see all 120+ products
- Use the search bar or filters to find specific products

### 3. **Make Your First Purchase**

#### Option A: Pay in Full
1. Click on any product
2. Enter quantity and click "Add to Cart"
3. Go to Cart and checkout
4. Complete the form and purchase

#### Option B: Buy on Credit
1. Click on any product
2. Click "Buy on Credit" button
3. In the payment choice modal:
   - Select "Buy on credit"
   - Choose 3 or 6 month payment term
   - Select interest rate (0%, 5%, 9.99%, or 14.99%)
4. Review payment breakdown showing:
   - Deposit due now (20%)
   - Monthly payment amount
   - Total interest (if applicable)
5. Complete checkout with deposit amount

### 4. **Test Data**
- **120 Pre-loaded Products** in categories:
  - Laptops ($499-$3000)
  - Desktops ($399-$3400)
  - Tablets ($199-$1400)
  - Accessories ($9-$200)
  - Peripherals ($19-$500)
  - Storage ($29-$600)

### 5. **Test Sign-In**
- Use any email address (e.g., `test@example.com`)
- Optional display name
- Sign in to track orders and access payment portal

### 6. **View Orders & Make Payments**
1. Sign in with your email
2. Click "My Orders" in navigation
3. View your orders:
   - Order number and date
   - Items purchased
   - Amount paid vs. remaining balance
   - Payment status

4. For credit orders, click:
   - **"View Payment Schedule"** - See all payment dates and amounts
   - **"Make Payment"** - Process an installment payment

### 7. **Payment Portal Features**
- View full amortization schedule with interest breakdown
- Quick payment options:
  - Next payment only
  - Double payment
  - Full remaining balance
  - Custom amount
- Enter credit card details (test: use any valid format)
- See updated balance after payment

### Credit Policy & Eligibility
Before applying for credit, please review the Zaga Tech Credit credit policy (simulated for this demo):

- Deposit: 20% of the total purchase amount is due at checkout.
- Financing: Remaining 80% is financed over the chosen term (3 or 6 months) at the selected APR.
- Eligibility: Customers must be 18+, provide a valid billing address and contact information, and meet the minimum purchase threshold (e.g., $50). Credit checks are simulated in this demo; a production system would perform real checks with user consent.
- How to apply: During checkout select "Buy on Credit", choose term and APR, review the amortization schedule, and confirm by paying the deposit.

If you meet the eligibility requirements, select "Buy on Credit" during checkout to apply and proceed with the deposit payment.

### 8. **Admin Panel**
- Click "Admin" in navigation
- View dashboard statistics
- Manage products (add, edit, delete)
- View all orders
- Access "Order History & Payments" for comprehensive order tracking

---

## Example Credit Purchase Scenario

### Scenario: Buying a $1000 Laptop on 3-Month Credit at 9.99% APR

**Step 1: Payment Choice Modal**
- Select "Buy on credit"
- Choose "3 Months"
- Select "9.99% APR"
- See breakdown:
  - Deposit: $200 (due now)
  - Monthly: $268.15
  - Total Interest: $4.45

**Step 2: Checkout**
- Pay only $200 deposit
- Complete order

**Step 3: Order History**
- View order details
- Outstanding balance: $804.45 (includes interest)
- Next payment due: 1 month from order date

**Step 4: Payment Schedule**
- Payment 1: $268.15 (Principal: $266.82, Interest: $1.33)
- Payment 2: $268.15 (Principal: $267.07, Interest: $1.08)
- Payment 3: $268.15 (Principal: $267.32, Interest: $0.83)

**Step 5: Payment Portal**
- Make Payment 1: Enter $268.15
- Balance updated: $536.30
- Progress toward fully paid status

---

## Key Features to Try

### 1. **Search & Filter**
- Try searching "Laptop" or "Monitor"
- Filter by price: $0-$500, $500-$1000, $1000+
- Filter by rating: 4+ stars

### 2. **Cart Management**
- Add multiple items
- Adjust quantities
- Remove items
- See real-time subtotal and tax

### 3. **Multiple Payment Options**
- Same product with different terms
- See how interest affects total cost
- Compare 3-month vs 6-month

### 4. **Payment Tracking**
- Make multiple partial payments
- Track payment history
- See remaining balance update in real-time

### 5. **Admin Functions**
- Add new product: Go to Admin → Add New Product
- Edit product: Edit any existing product
- View all orders and their statuses

---

## Test Credit Card Details
(For testing payment form validation)

- **Card Number:** 4532 1111 1111 1111
- **Expiry:** 12/25
- **CVV:** 123

---

## Keyboard Shortcuts & Tips

- **Cart Counter:** Always visible in top-right corner
- **Responsive Layout:** Resize browser to see mobile view
- **Data Persistence:** All data saved to browser localStorage
- **Reset Data:** Open browser console and run:
  ```javascript
  localStorage.clear();
  location.reload();
  ```

---

## Troubleshooting

**Q: Orders not showing?**
- A: Make sure you're signed in. Orders are filtered by user email.

**Q: Payment schedule not displaying?**
- A: Click "View Payment Schedule" button on credit orders.

**Q: Interest calculations seem off?**
- A: Interest uses standard amortization formula. 0% APR shows equal payments.

**Q: Images not loading?**
- A: Check if `images/` folder contains SVG files. They're created automatically.

**Q: Want to clear data?**
- A: Open browser console (F12) and run: `localStorage.clear(); location.reload();`

---

## Browser Console Debug Commands

```javascript
// View all products
console.log(JSON.parse(localStorage.getItem('products')));

// View all orders
console.log(JSON.parse(localStorage.getItem('orders')));

// View current cart
console.log(JSON.parse(localStorage.getItem('cart')));

// View current user
console.log(JSON.parse(localStorage.getItem('zagatech_current_user')));

// Clear everything
localStorage.clear();

// Regenerate products
__regenerateDefaultProducts(120);
```

---

## Next Steps

1. **Explore the interface** - Try different products and features
2. **Test credit purchases** - Try different terms and interest rates
3. **Make test payments** - Process installment payments in portal
4. **Try admin features** - Manage products and view orders
5. **Check payment schedules** - Understand amortization details

---

For detailed feature documentation, see `FEATURES.md`

**Enjoy Zaga Tech Credit!** 🛒
