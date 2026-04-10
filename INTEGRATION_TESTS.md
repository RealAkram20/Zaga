# Integration Test Guide - Zaga Tech Credit Advanced Features

## Pre-Test Setup

### 1. Clear Browser Storage (Fresh Start)
```javascript
// Open browser console (F12) and run:
localStorage.clear();
location.reload();
```

### 2. Verify Initial State
- Products should auto-generate (120 items)
- Cart should be empty
- No orders should exist
- User should not be signed in

---

## Test Suite: Complete Credit Purchase Flow

### Test 1: Product Selection with Interest Rate Selection ✅

**Objective:** Verify payment choice modal with interest rates

**Steps:**
1. Open `index.html` or `shop.html`
2. Search for "Laptop" or click any product
3. Click product to open details
4. Scroll to "Add to Cart" section
5. Click "Buy on Credit" button
6. Verify modal shows:
   - [ ] "Pay in full" option
   - [ ] "Buy on credit" option
   - [ ] 3/6 month dropdown (initially hidden)
   - [ ] Interest rate radio buttons (initially hidden)

**Actions:**
1. Select "Buy on credit" radio button
2. Verify modal reveals:
   - [ ] Payment Period dropdown (3/6 months)
   - [ ] Interest Rate options:
     - 0% (No Interest) - default
     - 5% APR
     - 9.99% APR
     - 14.99% APR
   - [ ] Credit summary box

**Interest Rate Testing:**
1. Keep 3 months, 0% - Note deposit and monthly
2. Change to 5% APR - Verify monthly increases
3. Change to 9.99% APR - Verify monthly increases further
4. Verify formula shows total interest in summary
5. Change to 6 months, 14.99% - Verify all calculations update

**Expected Results:**
- $1000 product, 3 months, 0%:
  - Deposit: $200
  - Monthly: $266.67
  - No interest

- $1000 product, 3 months, 9.99%:
  - Deposit: $200
  - Monthly: ~$268.15
  - Total Interest: ~$4.45

**Result:** ✅ PASS / ❌ FAIL

---

### Test 2: Order Placement and Schedule Generation ✅

**Objective:** Verify order is placed with full amortization schedule

**Steps:**
1. From previous test, confirm payment choice
2. Proceed to checkout
3. Complete shipping form
4. Verify checkout summary shows:
   - [ ] Deposit amount as "Amount to Pay Now"
   - [ ] Remaining balance displayed
   - [ ] Credit terms displayed

**Checkout Details Check:**
- For $1000 laptop, 3 months, 9.99%:
  - [ ] Total Now: $200 (deposit)
  - [ ] Remaining Balance: $804.45
  - [ ] Shows "Credit: 3 months at 9.99% APR"

**Complete Order:**
1. Click "Place Order"
2. Verify success message with order number
3. Note order number (e.g., "ORD-1706000000000")

**Verify Order Storage:**
```javascript
// In console, check order was stored:
const orders = JSON.parse(localStorage.getItem('orders'));
console.log(orders[0]);

// Verify order has:
// - orderNumber: "ORD-..."
// - schedule: [array of payment objects]
// - items with paymentPlan.interestRate
// - totalNow: 200 (deposit)
// - totalFull: 1000 (full price)
```

**Expected Order Schedule:**
```
Payment 1: $268.15 (Principal: $266.82, Interest: $1.33, Balance: $533.18)
Payment 2: $268.15 (Principal: $267.07, Interest: $1.08, Balance: $266.11)
Payment 3: $268.15 (Principal: $267.32, Interest: $0.83, Balance: -$1.21)
```

**Result:** ✅ PASS / ❌ FAIL

---

### Test 3: Order History Display ✅

**Objective:** Verify order appears with correct information

**Steps:**
1. Open `order-history.html`
2. Verify order card displays:
   - [ ] Order number (ORD-...)
   - [ ] Order date
   - [ ] Credit status indicator
   - [ ] Items purchased with quantities
   - [ ] Amount Paid Now: $200
   - [ ] Outstanding Balance: $804.45
   - [ ] "View Payment Schedule" button
   - [ ] "Make Payment" button

**Verify Balance Calculation:**
- Outstanding balance = $1000 - $200 (deposit) = $800
- Plus interest = $804.45
- Verify it matches order details

**Next Payment Due:**
- Should show date 1 month from order date

**Result:** ✅ PASS / ❌ FAIL

---

### Test 4: Payment Schedule Display ✅

**Objective:** Verify detailed payment schedule modal

**Steps:**
1. From order history, click "View Payment Schedule"
2. Verify modal shows table with columns:
   - [ ] Payment # (1, 2, 3)
   - [ ] Due Date (3 dates, 1 month apart)
   - [ ] Payment Amount ($268.15 each)
   - [ ] Principal ($266.82, $267.07, $267.32)
   - [ ] Interest ($1.33, $1.08, $0.83)
   - [ ] Balance ($533.18, $266.11, -$1.21)

**Validate Calculations:**
1. Principal + Interest = Payment Amount ✓
2. Each payment reduces balance ✓
3. Total principal = $801.21 (≈ $800 after rounding) ✓
4. Total interest = $4.45 ✓
5. Interest decreases each month ✓

**Result:** ✅ PASS / ❌ FAIL

---

### Test 5: Payment Portal Access ✅

**Objective:** Verify payment portal loads correctly

**Steps:**
1. From order history, click "Make Payment"
2. Verify portal shows:
   - [ ] Order number in title
   - [ ] Total Outstanding Balance: $804.45
   - [ ] Next Payment Due: $268.15
   - [ ] Payment schedule table
   - [ ] Payment amount selector buttons
   - [ ] Credit card form

**Payment Options Visible:**
- [ ] Next Payment: $268.15 (pre-selected)
- [ ] Double Payment: $536.30
- [ ] Full Balance: $804.45
- [ ] Custom Amount input field

**Form Fields Present:**
- [ ] Card Number input
- [ ] Expiry Date input (MM/YY format)
- [ ] CVV input
- [ ] Process Payment button

**Result:** ✅ PASS / ❌ FAIL

---

### Test 6: Payment Processing ✅

**Objective:** Verify payment can be processed and recorded

**Steps:**
1. In payment portal, select "Next Payment" ($268.15)
2. Enter test card details:
   - Card: 4532 1111 1111 1111
   - Expiry: 12/25
   - CVV: 123
3. Click "Process Payment"

**Validation:**
- [ ] Form validates card number format
- [ ] Form validates expiry date format
- [ ] Form validates CVV
- [ ] Success message appears
- [ ] Message shows:
  - Amount: $268.15
  - Transaction Date: Today's date
  - Link to "View all orders"

**Backend Verification:**
```javascript
// Verify payment recorded in order:
const orders = JSON.parse(localStorage.getItem('orders'));
const order = orders[0];
console.log(order.paymentsMade);
// Should contain: [{date: "...", amount: 268.15}]
```

**Result:** ✅ PASS / ❌ FAIL

---

### Test 7: Balance Update After Payment ✅

**Objective:** Verify remaining balance updates after payment

**Steps:**
1. Return to order history
2. Verify the same order now shows:
   - [ ] Outstanding Balance updated to: $536.30
   - [ ] Next Payment Due: $268.15
   - [ ] Payment status changed to reflect partial payment

**View Updated Schedule:**
1. Click "View Payment Schedule" again
2. Verify first payment now shows status (if implemented)

**Result:** ✅ PASS / ❌ FAIL

---

### Test 8: Multiple Payments ✅

**Objective:** Verify multiple payments can be processed

**Steps:**
1. Make second payment (Next Payment: $268.15)
2. Outstanding balance should now be: $268.15
3. Make third payment (Full Balance: $268.15)
4. Outstanding balance should now be: $0 or very small

**Final Verification:**
- [ ] All 3 payments recorded in paymentsMade
- [ ] Balance shows $0 or fully paid
- [ ] Order marked as complete

**Result:** ✅ PASS / ❌ FAIL

---

### Test 9: Multiple Products in One Order ✅

**Objective:** Verify order with multiple credit items

**Steps:**
1. Clear cart and start fresh
2. Add 2 different products to cart
3. For each product, select different terms:
   - Product 1: 3 months, 5% APR
   - Product 2: 6 months, 9.99% APR
4. Complete checkout
5. Verify order shows both items
6. Verify payment schedule includes payments for both products

**Expected Result:**
- Schedule should have 6 payments (3 from product 1, 3 from product 2 if products expire at different times, or combined)
- Total outstanding = sum of both products' remaining amounts
- Interest rates applied correctly to each

**Result:** ✅ PASS / ❌ FAIL

---

### Test 10: Different Interest Rate Scenarios ✅

**Objective:** Verify interest calculations are correct

**Scenario A: No Interest (0%)**
- $1000, 3 months
- Expected monthly: $333.33
- Expected interest: $0
- Total paid: $1000

**Scenario B: Low Interest (5%)**
- $1000, 3 months
- Expected monthly: ~$335.87
- Expected interest: ~$7.61
- Total paid: ~$1007.61

**Scenario C: Standard Interest (9.99%)**
- $1000, 3 months
- Expected monthly: ~$268.15 (after 20% deposit)
- Expected interest: ~$4.45
- Total paid: ~$1004.45

**Scenario D: High Interest (14.99%)**
- $1000, 3 months
- Expected monthly: higher than 9.99%
- Expected interest: ~$6.67
- Total paid: ~$1006.67

**Result:** ✅ PASS / ❌ FAIL

---

## Stress Tests

### Test 11: Large Purchase (High Interest Impact) ✅

**Objective:** Verify system handles large amounts correctly

**Steps:**
1. Find expensive product ($3000 desktop)
2. Purchase on credit: 6 months, 14.99% APR
3. Deposit: $600
4. Verify schedule generated correctly
5. Make several payments
6. Verify all calculations remain accurate

**Expected:**
- Schedule with 6 detailed payments
- Clear interest breakdown
- Accurate principal reduction

**Result:** ✅ PASS / ❌ FAIL

---

### Test 12: Data Persistence ✅

**Objective:** Verify data survives page refresh

**Steps:**
1. Create order with multiple payments recorded
2. Refresh browser (F5)
3. Navigate back to order history
4. Verify:
   - [ ] Order still exists
   - [ ] Payment history still recorded
   - [ ] Balance calculations still correct
   - [ ] Schedule still intact

**Result:** ✅ PASS / ❌ FAIL

---

### Test 13: Admin Panel Access ✅

**Objective:** Verify admin can see and manage orders

**Steps:**
1. Go to Admin panel (`admin.html`)
2. Click "Order History & Payments" link
3. Verify orders display with correct information
4. Verify can view payment schedules
5. Verify access to payment portal

**Result:** ✅ PASS / ❌ FAIL

---

## Edge Cases

### Test 14: Rounding and Precision ✅

**Objective:** Verify no rounding errors in calculations

**Steps:**
1. Purchase odd amount like $999.99
2. Generate schedule
3. Verify:
   - [ ] All amounts to 2 decimal places
   - [ ] Principal + Interest = Payment
   - [ ] Sum of all payments = total amount

**Result:** ✅ PASS / ❌ FAIL

---

### Test 15: Payment Exceeding Balance ✅

**Objective:** Verify system handles overpayment

**Steps:**
1. With balance of $200, try custom payment of $300
2. System should:
   - [ ] Allow payment
   - [ ] Reduce balance to $0 (not negative)
   - [ ] Show paid status

**Result:** ✅ PASS / ❌ FAIL

---

## Summary Report

**Total Tests:** 15  
**Passed:** ___/15  
**Failed:** ___/15  
**Success Rate:** ___%

### Critical Tests (Must Pass)
- [ ] Test 1: Interest Rate Selection
- [ ] Test 2: Schedule Generation
- [ ] Test 3: Order History Display
- [ ] Test 4: Payment Schedule Modal
- [ ] Test 6: Payment Processing

### Important Tests
- [ ] Test 5: Payment Portal Access
- [ ] Test 7: Balance Update
- [ ] Test 8: Multiple Payments

### Nice to Have
- [ ] Test 9-15: Edge cases and stress tests

---

## Debugging Tips

If a test fails:

1. **Check Browser Console:**
   ```javascript
   // View all orders
   JSON.parse(localStorage.getItem('orders'))
   
   // View specific order schedule
   JSON.parse(localStorage.getItem('orders'))[0].schedule
   
   // View payment history
   JSON.parse(localStorage.getItem('orders'))[0].paymentsMade
   ```

2. **Verify Calculations:**
   ```javascript
   // Test amortization calculation
   computeCredit(1000, 3, 9.99)
   ```

3. **Check File Presence:**
   - Ensure all .html files exist in `Zagatech.html/`
   - Ensure `script.js` exists in parent directory
   - Ensure relative paths are correct

4. **Clear and Reset:**
   ```javascript
   localStorage.clear();
   __regenerateDefaultProducts(120);
   location.reload();
   ```

---

**Test Date:** ___________  
**Tester Name:** ___________  
**Platform:** ___________  
**Browser:** ___________  

---

*Integration Test Guide v1.0 - 2025*
