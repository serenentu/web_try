# BeautyVibe Project - Quick File Reference

## 📋 Essential Files (Do Not Delete)

### Core Application
| File | Purpose | Critical? |
|------|---------|-----------|
| `index.html` | Main products page with filters/search | ✅ Yes |
| `style.css` | All styling for the site | ✅ Yes |
| `productfilters.js` | Product filtering, sorting, display | ✅ Yes |
| `db.php` | Database connection | ✅ Yes |
| `schema.sql` | Database structure definition | ✅ Yes |
| `quick_setup.sql` | One-command database setup | ✅ Yes |

### Shopping Cart
| File | Purpose | Critical? |
|------|---------|-----------|
| `cart.php` | Shopping cart page | ✅ Yes |
| `cart_update.php` | Update cart quantities | ✅ Yes |
| `cart_remove.php` | Remove items from cart | ✅ Yes |
| `add_to_cart.php` | Add items to cart | ✅ Yes |

### Checkout & Orders
| File | Purpose | Critical? |
|------|---------|-----------|
| `checkout.php` | Checkout form | ✅ Yes |
| `process_checkout.php` | Process checkout | ✅ Yes |
| `order_confirmation.php` | Order confirmation page | ✅ Yes |
| `order_history.php` | View past orders | ⚠️ Important |

### Products
| File | Purpose | Critical? |
|------|---------|-----------|
| `products.php` | Product list page | ✅ Yes |
| `product.php` | Single product detail page | ✅ Yes |

### Assets
| File/Folder | Purpose | Critical? |
|------|---------|-----------|
| `BeautyVibeLogo.png` | Company logo | ✅ Yes |
| `product pictures/` | Product images | ✅ Yes |

### Documentation
| File | Purpose | Critical? |
|------|---------|-----------|
| `README_SETUP.md` | Setup instructions | ✅ Yes |
| `VERIFY_CREDENTIALS.md` | DB troubleshooting | ⚠️ Helpful |
| `INSTALL_PHP_MAC.md` | PHP install guide (macOS) | ⚠️ Helpful |

---

## 🗑️ Files You Can Delete

| File | Reason |
|------|--------|
| `order_history_demo.html` | Static demo - replaced by PHP version |
| `order_comfirmation_demo.html` | Static demo - replaced by PHP version |
| `order_confirmation (1).php` | Duplicate file |
| `view_orders.sql` | Just a reference query |
| `add_checkout_fields.sql` (root) | Duplicate - already in migrations/ |
| `bgmusic.mp3.mp3` | Background music (unusual for e-commerce) |
| `Archangel...mp3` | Background music (likely unused) |

---

## 📂 File Categories

### HTML Pages (8 files)
- **Live Pages**: index.html, Home.html, about.html, login.html, account.html
- **Demo Pages (can delete)**: order_history_demo.html, order_comfirmation_demo.html

### PHP Scripts (18 files)
- **Essential**: db.php, cart.php, products.php, product.php, checkout.php, process_checkout.php, order_confirmation.php, add_to_cart.php, cart_update.php, cart_remove.php
- **Important**: order_history.php, view_orders.php, order_review.php
- **Tools**: test_db_connection.php, run_migration.php
- **Optional**: review_submit.php
- **Duplicate (delete)**: order_confirmation (1).php

### JavaScript (3 files)
- **Critical**: productfilters.js
- **Important**: validation.js, script.js

### CSS (1 file)
- **Critical**: style.css

### Database (6 files + migrations folder)
- **Critical**: schema.sql, quick_setup.sql
- **Important**: migrations/2025-11-02_add_checkout_fields.sql
- **Tools**: setup_database.sh
- **Delete**: view_orders.sql, add_checkout_fields.sql (duplicate)

### Assets
- **Critical**: BeautyVibeLogo.png, product pictures/ (11 images)
- **Optional**: BeautyVibeAdv1.png
- **Delete**: bgmusic.mp3.mp3, Archangel...mp3

### Documentation (4 files)
- **Critical**: README_SETUP.md
- **Helpful**: VERIFY_CREDENTIALS.md, INSTALL_PHP_MAC.md
- **Minimal**: README.md (root - should be expanded)

---

## 🔄 How Files Work Together

```
User visits index.html
    ↓
Loads style.css (styling)
    ↓
Loads productfilters.js (display products)
    ↓
User clicks "Add to Cart"
    ↓
AJAX call to add_to_cart.php
    ↓
add_to_cart.php uses db.php to save to database
    ↓
User visits cart.php
    ↓
cart.php uses db.php to load cart items
    ↓
User updates quantity → cart_update.php
User removes item → cart_remove.php
    ↓
User proceeds to checkout.php
    ↓
Submits form to process_checkout.php
    ↓
process_checkout.php creates order in database
    ↓
Redirects to order_confirmation.php
    ↓
User can view order_history.php anytime
```

---

## 🎯 File Count Summary

| Category | Total | Critical | Important | Optional/Delete |
|----------|-------|----------|-----------|-----------------|
| HTML | 8 | 3 | 3 | 2 |
| PHP | 18 | 10 | 5 | 3 |
| JavaScript | 3 | 1 | 2 | 0 |
| CSS | 1 | 1 | 0 | 0 |
| Database | 6 | 2 | 2 | 2 |
| Images | 13 | 12 | 1 | 0 |
| Audio | 2 | 0 | 0 | 2 |
| Documentation | 4 | 1 | 2 | 1 |
| **TOTAL** | **55** | **30** | **15** | **10** |

---

## 🏆 Top 10 Most Important Files

1. **db.php** - Without this, nothing works
2. **index.html** - Main entry point
3. **style.css** - All visual styling
4. **productfilters.js** - Product display logic
5. **schema.sql** - Database structure
6. **cart.php** - Shopping cart
7. **checkout.php + process_checkout.php** - Complete purchases
8. **add_to_cart.php** - Core cart functionality
9. **order_confirmation.php** - Purchase confirmation
10. **README_SETUP.md** - Setup instructions

---

## ⚡ Quick Actions

### Clean Up Project
```bash
# Delete demo files
rm order_history_demo.html order_comfirmation_demo.html

# Delete duplicate files
rm "order_confirmation (1).php" view_orders.sql add_checkout_fields.sql

# Delete unused audio (if confirmed unused)
rm bgmusic.mp3.mp3 "Archangel (Slowed) - Dj Anemia, Crier, Sixnite [Edit Audio].mp3"
```

### Setup Project
```bash
# 1. Create database
mysql -u root -p < quick_setup.sql

# 2. Test connection
php test_db_connection.php

# 3. Start server
php -S localhost:8000

# 4. Open browser
open http://localhost:8000/index.html
```

---

## 💡 Key Insights

1. **55 total files** in the project
2. **30 files are critical** to core functionality
3. **10 files can be deleted** without impact
4. **No frameworks used** - vanilla PHP and JavaScript
5. **Session-based cart** - no login required
6. **Database-driven** - MySQL required for most features
7. **Well-documented** - 3 detailed documentation files

---

## 📞 Need More Details?

See `FILE_GUIDE.md` for comprehensive documentation including:
- Detailed description of every file
- Feature explanations
- Security considerations
- Data flow diagrams
- Technology stack details
- Common questions and answers
