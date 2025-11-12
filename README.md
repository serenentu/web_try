# BeautyVibe E-Commerce Web Application

A full-featured e-commerce shopping cart application built with PHP, MySQL, and vanilla JavaScript.

## 🎯 Project Overview

BeautyVibe is a beauty products online store with complete shopping cart functionality, product filtering, checkout process, and order management.

## ✨ Features

- 🛍️ **Product Catalog**: Browse products with filtering by category (Lips, Face, Eyes)
- 🔍 **Search & Sort**: Search products and sort by price or name
- 🛒 **Shopping Cart**: Add, update, remove items with database persistence
- 💳 **Checkout**: Complete checkout process with customer information
- 📦 **Order History**: View past orders
- 📱 **Responsive Design**: Works on desktop and mobile devices
- 🎨 **Modern UI**: Clean, professional interface with Font Awesome icons

## 🏗️ Technology Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **No Frameworks**: Pure PHP and JavaScript (no Laravel, React, Vue, etc.)

## 📁 Project Structure

```
web_try/
├── README.md                    # This file
├── FILE_GUIDE.md               # Comprehensive file documentation
├── FILE_SUMMARY.md             # Quick reference guide
└── makeup_webpage_template/     # Main application
    ├── *.html                  # Frontend pages
    ├── *.php                   # Backend scripts
    ├── *.js                    # Client-side logic
    ├── *.css                   # Styling
    ├── *.sql                   # Database files
    ├── product pictures/       # Product images
    └── migrations/             # Database migrations
```

## 🚀 Quick Start

### Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB
- Web browser

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/serenentu/web_try.git
   cd web_try/makeup_webpage_template
   ```

2. **Setup Database**
   ```bash
   mysql -u root -p < quick_setup.sql
   ```
   This creates the database, user, tables, and sample data.

3. **Verify Connection**
   ```bash
   php test_db_connection.php
   ```

4. **Start PHP Server**
   ```bash
   php -S localhost:8000
   ```

5. **Open Browser**
   ```
   http://localhost:8000/index.html
   ```

## 📚 Documentation

- **[FILE_GUIDE.md](FILE_GUIDE.md)** - Comprehensive documentation explaining every file
- **[FILE_SUMMARY.md](FILE_SUMMARY.md)** - Quick reference and file categories
- **[makeup_webpage_template/README_SETUP.md](makeup_webpage_template/README_SETUP.md)** - Detailed setup instructions
- **[makeup_webpage_template/VERIFY_CREDENTIALS.md](makeup_webpage_template/VERIFY_CREDENTIALS.md)** - Database troubleshooting

## 💻 Core Files

| File | Purpose |
|------|---------|
| `index.html` | Main products page with filtering |
| `cart.php` | Shopping cart |
| `checkout.php` | Checkout process |
| `db.php` | Database configuration |
| `style.css` | Application styling |
| `productfilters.js` | Product filtering logic |

## 🗄️ Database

### Default Credentials
- **Host**: 127.0.0.1:3306
- **Username**: shop_user
- **Password**: ShopPass123!
- **Database**: shop_db

### Tables
- `products` - Product catalog
- `cart_items` - Shopping cart items
- `orders` - Customer orders
- `order_items` - Items in each order
- `customers` - Customer information

## 🛠️ Development

### File Importance
- **Critical (30 files)**: Cannot be removed without breaking functionality
- **Important (15 files)**: Enhances features and development
- **Optional (10 files)**: Can be removed (demos, duplicates, unused assets)

See [FILE_SUMMARY.md](FILE_SUMMARY.md) for the complete breakdown.

### Testing
```bash
# Test database connection
php test_db_connection.php

# View cart items
mysql -u shop_user -pShopPass123! shop_db -e "SELECT * FROM cart_items;"

# View orders
mysql -u shop_user -pShopPass123! shop_db -e "SELECT * FROM orders ORDER BY created_at DESC LIMIT 5;"
```

## 🔒 Security Notes

⚠️ **This is a learning/development project**

For production use, implement:
- Environment variables for credentials (not hardcoded in `db.php`)
- HTTPS/SSL encryption
- CSRF protection
- Input sanitization improvements
- Rate limiting
- Password hashing for user accounts
- Payment gateway integration

## 🤝 Contributing

This appears to be a school/learning project. If contributing:
1. Follow existing code style
2. Test thoroughly
3. Update documentation
4. Don't break existing functionality

## 📝 License

Educational/personal project - check with repository owner for licensing details.

## 🙋 Need Help?

1. Check [FILE_GUIDE.md](FILE_GUIDE.md) for file explanations
2. Read [README_SETUP.md](makeup_webpage_template/README_SETUP.md) for setup help
3. Review [VERIFY_CREDENTIALS.md](makeup_webpage_template/VERIFY_CREDENTIALS.md) for database issues
4. Check troubleshooting sections in documentation

## 📊 Project Stats

- **55 total files**
- **8 HTML pages**
- **18 PHP scripts**
- **3 JavaScript files**
- **11 product images**
- **5 database tables**
- **Well-documented** with 4 documentation files

---

**Built with ❤️ for BeautyVibe**