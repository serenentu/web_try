# BeautyVibe Project - Visual Structure Guide

## 📐 Project Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER'S BROWSER                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │  index.html  │  │   Home.html  │  │  about.html  │         │
│  │  (Products)  │  │   (Landing)  │  │    (Info)    │         │
│  └──────┬───────┘  └──────────────┘  └──────────────┘         │
│         │                                                        │
│         ├─► style.css (styling)                                 │
│         ├─► productfilters.js (filters/sort/display)            │
│         └─► validation.js (form validation)                     │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ AJAX Requests
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                       PHP BACKEND                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Shopping Cart:          Products:           Checkout:          │
│  ┌─────────────────┐   ┌──────────────┐   ┌─────────────────┐ │
│  │ add_to_cart.php │   │ products.php │   │  checkout.php   │ │
│  │  cart.php       │   │  product.php │   │ process_        │ │
│  │ cart_update.php │   └──────────────┘   │  checkout.php   │ │
│  │ cart_remove.php │                      │ order_          │ │
│  └─────────────────┘                      │  confirmation   │ │
│                                            └─────────────────┘ │
│                                                                  │
│  Order Management:         Database Config:                     │
│  ┌─────────────────┐      ┌────────────────┐                  │
│  │ order_history   │      │    db.php      │◄─────────────────┤
│  │  .php           │      │   (PDO conn)   │                  │
│  │ view_orders.php │      └────────────────┘                  │
│  └─────────────────┘                                            │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ SQL Queries
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      MySQL DATABASE                              │
├─────────────────────────────────────────────────────────────────┤
│  Database: shop_db                                              │
│  User: shop_user                                                │
│                                                                  │
│  Tables:                                                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   products   │  │  cart_items  │  │    orders    │         │
│  ├──────────────┤  ├──────────────┤  ├──────────────┤         │
│  │ id           │  │ id           │  │ id           │         │
│  │ name         │  │ session_id   │  │ session_token│         │
│  │ price        │  │ product_id   │  │ total        │         │
│  │ stock        │  │ quantity     │  │ customer_name│         │
│  │ image_path   │  └──────────────┘  │ created_at   │         │
│  │ category     │                    └──────────────┘         │
│  └──────────────┘                                              │
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐                           │
│  │ order_items  │  │  customers   │                           │
│  ├──────────────┤  ├──────────────┤                           │
│  │ order_id     │  │ id           │                           │
│  │ product_id   │  │ email        │                           │
│  │ quantity     │  │ name         │                           │
│  │ price        │  │ phone        │                           │
│  └──────────────┘  └──────────────┘                           │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## 🔄 User Journey Flow Diagrams

### 1️⃣ Browse Products Flow

```
User Opens Browser
       │
       ▼
┌─────────────────┐
│  index.html     │  Loads product page
└────────┬────────┘
         │
         ├──► style.css          (applies styling)
         │
         ├──► productfilters.js  (fetches products)
         │           │
         │           ▼
         │    ┌─────────────┐
         │    │ products.php│ (optional: fetch from DB)
         │    └─────────────┘
         │           │
         │           ▼
         │    ┌─────────────┐
         │    │   db.php    │ (connect to MySQL)
         │    └─────────────┘
         │           │
         │           ▼
         │    ┌─────────────┐
         │    │  MySQL DB   │ (query products table)
         │    └─────────────┘
         │
         ▼
┌──────────────────────────────┐
│  Products displayed          │
│  - Filter by category        │
│  - Sort by price/name        │
│  - Search products           │
└──────────────────────────────┘
```

### 2️⃣ Add to Cart Flow

```
User clicks "Add to Cart" on index.html
       │
       ▼
┌────────────────────┐
│ productfilters.js  │ Sends AJAX request
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│ add_to_cart.php    │ Receives request
└──────────┬─────────┘
           │
           ├─► Start PHP session (get session_id)
           │
           ▼
┌────────────────────┐
│     db.php         │ Connect to database
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│   MySQL DB         │ 
│ INSERT INTO        │ Add item to cart_items table
│  cart_items        │ (session_id, product_id, quantity)
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│   JSON Response    │ {success: true}
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│ productfilters.js  │ Show success message
└────────────────────┘
```

### 3️⃣ View & Manage Cart Flow

```
User clicks Cart Icon
       │
       ▼
┌────────────────────┐
│    cart.php        │ Cart page loads
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│     db.php         │ Connect to database
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│   MySQL DB         │ 
│ SELECT from        │ Get cart items for this session
│  cart_items        │ JOIN with products table
│  JOIN products     │
└──────────┬─────────┘
           │
           ▼
┌────────────────────────────────┐
│  Cart Page Displays:           │
│  - Product images              │
│  - Quantities                  │
│  - Prices                      │
│  - Subtotals                   │
│  - Total                       │
└────────────────────────────────┘
           │
           ├─► User updates quantity ──► cart_update.php ──► UPDATE cart_items
           │
           └─► User removes item ──────► cart_remove.php ──► DELETE from cart_items
```

### 4️⃣ Checkout Flow

```
User clicks "Proceed to Checkout"
       │
       ▼
┌────────────────────┐
│  checkout.php      │ Show checkout form
└──────────┬─────────┘
           │
           ├──► Load cart items from DB
           ├──► Show order summary
           └──► Display form fields:
                - Customer name, email, phone
                - Shipping address
                - Payment method
           │
           ▼
User fills form and submits
       │
       ▼
┌────────────────────┐
│  validation.js     │ Validate form data
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│ process_checkout   │ Process order
│     .php           │
└──────────┬─────────┘
           │
           ├─► Validate data
           ├─► Calculate total
           │
           ▼
┌────────────────────┐
│   MySQL DB         │
│ INSERT INTO orders │ Create order record
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│   MySQL DB         │
│ INSERT INTO        │ Create order items
│  order_items       │
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│   MySQL DB         │
│ DELETE FROM        │ Clear cart
│  cart_items        │
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│ Redirect to        │
│ order_confirmation │ Show success message
│     .php           │ Display order details
└────────────────────┘
```

### 5️⃣ View Order History Flow

```
User clicks "Order History"
       │
       ▼
┌────────────────────┐
│ order_history.php  │
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│     db.php         │ Connect to database
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│   MySQL DB         │
│ SELECT from orders │ Get orders for this session
│ JOIN order_items   │ Get items in each order
└──────────┬─────────┘
           │
           ▼
┌────────────────────────────────┐
│  Order History Displays:       │
│  - Order numbers               │
│  - Dates                       │
│  - Totals                      │
│  - Item details                │
│  - Order status                │
└────────────────────────────────┘
```

## 🗂️ File Dependency Map

```
Critical Dependencies:
┌─────────────┐
│   db.php    │◄───────────────┐
└──────┬──────┘                │
       │                       │
       │ Required by:          │
       ├─► cart.php            │
       ├─► products.php        │
       ├─► product.php         │
       ├─► add_to_cart.php     │
       ├─► cart_update.php     │
       ├─► cart_remove.php     │
       ├─► checkout.php        │
       ├─► process_checkout.php│
       ├─► order_confirmation.php
       ├─► order_history.php   │
       └─► view_orders.php     │

Frontend Dependencies:
┌─────────────┐
│  style.css  │◄───────────────┐
└──────┬──────┘                │
       │                       │
       │ Required by:          │
       ├─► index.html          │
       ├─► Home.html           │
       ├─► about.html          │
       ├─► login.html          │
       ├─► cart.php            │
       └─► All other pages     │

┌──────────────────┐
│productfilters.js │
└────────┬─────────┘
         │
         │ Required by:
         └─► index.html (products page)

┌──────────────────┐
│ validation.js    │
└────────┬─────────┘
         │
         │ Required by:
         ├─► login.html
         ├─► checkout.php
         └─► Forms with validation
```

## 📊 Database Schema Visualization

```
┌─────────────────────────────────────────────────────────────────┐
│                      DATABASE RELATIONSHIPS                      │
└─────────────────────────────────────────────────────────────────┘

products                    cart_items                 orders
┌─────────────┐            ┌─────────────┐           ┌──────────────┐
│ id (PK)     │◄───────────│ product_id  │           │ id (PK)      │
│ name        │            │   (FK)      │           │ session_token│
│ price       │            ├─────────────┤           │ total        │
│ stock       │            │ session_id  │           │ customer_name│
│ image_path  │            │ quantity    │           │ created_at   │
│ category    │            │ created_at  │           │ status       │
│ description │            └─────────────┘           └──────┬───────┘
└─────────────┘                                             │
                                                            │
                                                            │
                              order_items                   │
                              ┌─────────────┐              │
                              │ id (PK)     │              │
              ┌───────────────│ order_id(FK)│◄─────────────┘
              │               │ product_id  │
              │               │   (FK)      │
              │               │ quantity    │
              │               │ price       │
              │               │ subtotal    │
              │               └─────────────┘
              │
              ▼
products ─────┘
(product details)


Legend:
PK = Primary Key
FK = Foreign Key
──► = References/Foreign Key relationship
```

## 🎯 File Organization by Function

```
USER INTERFACE (What users see)
├── index.html          → Products page (main)
├── Home.html           → Landing page
├── about.html          → About page
├── login.html          → Login page
├── account.html        → Account page
└── style.css           → All styling

SHOPPING CART (Cart management)
├── cart.php            → View cart
├── add_to_cart.php     → Add items
├── cart_update.php     → Update quantities
└── cart_remove.php     → Remove items

PRODUCTS (Product display)
├── products.php        → Product list
├── product.php         → Product details
└── productfilters.js   → Filter/sort logic

CHECKOUT (Purchase process)
├── checkout.php        → Checkout form
├── process_checkout.php→ Process order
└── order_confirmation  → Confirmation page
    .php

ORDERS (Order management)
├── order_history.php   → Customer orders
└── view_orders.php     → Admin view

DATABASE (Data layer)
├── db.php              → Connection
├── schema.sql          → Table structure
├── quick_setup.sql     → Setup script
└── migrations/         → Schema updates

UTILITIES (Helpers)
├── validation.js       → Form validation
├── script.js           → General JS
├── test_db_connection  → DB testing
│   .php
└── run_migration.php   → Migration runner

ASSETS (Images & files)
├── BeautyVibeLogo.png  → Logo
├── product pictures/   → Product images
└── (audio files)       → Background music

DOCUMENTATION (Help files)
├── README.md           → Project overview
├── FILE_GUIDE.md       → File documentation
├── FILE_SUMMARY.md     → Quick reference
├── README_SETUP.md     → Setup guide
├── VERIFY_CREDENTIALS  → DB troubleshooting
│   .md
└── INSTALL_PHP_MAC.md  → PHP install guide
```

## 💾 Session & Data Flow

```
┌─────────────────────────────────────────────────────────────┐
│                     SESSION MANAGEMENT                       │
└─────────────────────────────────────────────────────────────┘

User Opens Site
       │
       ▼
┌────────────────────┐
│ PHP session_start()│ Creates unique session ID
└──────────┬─────────┘
           │
           ▼
┌─────────────────────────────────────────┐
│ Session ID stored in:                   │
│ 1. Browser cookie (PHPSESSID)           │
│ 2. Server memory                        │
└──────────┬──────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────┐
│ Session ID used to:                     │
│ 1. Track cart items (cart_items table) │
│ 2. Track orders (orders table)          │
│ 3. Maintain user state                  │
└─────────────────────────────────────────┘

Cart Items Storage:
cart_items table
┌──────────────┬────────────┬──────────┐
│ session_id   │ product_id │ quantity │
├──────────────┼────────────┼──────────┤
│ abc123xyz... │ 1          │ 2        │
│ abc123xyz... │ 5          │ 1        │
│ def456uvw... │ 3          │ 3        │
└──────────────┴────────────┴──────────┘
   └─ Unique per browser session

Order History:
orders table
┌──────────────┬────────┬────────────┐
│ session_token│ total  │ created_at │
├──────────────┼────────┼────────────┤
│ abc123xyz... │ 125.40 │ 2025-11-12 │
│ abc123xyz... │ 89.90  │ 2025-11-10 │
└──────────────┴────────┴────────────┘
   └─ Tracks orders by session
```

## 🔐 Security Layer

```
┌─────────────────────────────────────────────────────────────┐
│                    SECURITY MEASURES                         │
└─────────────────────────────────────────────────────────────┘

Input Layer (Client)
┌────────────────────┐
│  validation.js     │ ─► Basic client-side validation
└────────────────────┘    (Not secure, just UX)

Processing Layer (Server)
┌────────────────────┐
│  PHP Scripts       │ ─► Server-side validation
│  - Sanitization    │ ─► htmlspecialchars()
│  - Type checking   │ ─► intval(), floatval()
└────────────────────┘

Database Layer
┌────────────────────┐
│  PDO Prepared      │ ─► Prevents SQL injection
│  Statements        │ ─► Parameterized queries
└────────────────────┘

⚠️  Missing (needed for production):
├─ HTTPS/SSL
├─ CSRF tokens
├─ Password hashing
├─ Rate limiting
├─ Input sanitization improvements
└─ Environment variables for credentials
```

## 📈 Performance Considerations

```
┌─────────────────────────────────────────────────────────────┐
│                    PERFORMANCE NOTES                         │
└─────────────────────────────────────────────────────────────┘

Images:
├─ Mix of PNG and WebP formats
├─ WebP = smaller file size
├─ Loaded on-demand (not preloaded)
└─ Could benefit from lazy loading

Database:
├─ Indexed foreign keys (good)
├─ Session-based queries (efficient)
├─ Could add caching layer
└─ Consider connection pooling for high traffic

JavaScript:
├─ Vanilla JS (no framework overhead)
├─ Inline functions (could be minified)
└─ No bundling/compilation needed

CSS:
├─ Single stylesheet (simple)
├─ Could be minified for production
└─ No preprocessor (SASS/LESS)

Caching:
├─ No caching implemented
├─ Could add browser caching headers
└─ Could add Redis/Memcached for sessions
```

---

## 🎓 Learning Path for This Project

If you're new to this project, study in this order:

1. **Start Here**: `README.md` (project overview)
2. **Understand Files**: `FILE_SUMMARY.md` (quick reference)
3. **Deep Dive**: `FILE_GUIDE.md` (detailed explanations)
4. **Setup**: `README_SETUP.md` (get it running)
5. **Database**: `schema.sql` (understand data structure)
6. **Frontend**: `index.html` → `style.css` → `productfilters.js`
7. **Backend**: `db.php` → `cart.php` → `checkout.php`
8. **Flow**: Follow a user journey (browse → cart → checkout)

---

This visual guide complements the detailed documentation in FILE_GUIDE.md and FILE_SUMMARY.md.
